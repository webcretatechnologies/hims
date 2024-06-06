<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_Recurring
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Recurring\Model;

use Magento\Sales\Model\Order as OrderModel;
use Webkul\Recurring\Model\RecurringTerms  as Term;
use Webkul\Recurring\Model\RecurringSubscriptionsMappingFactory  as MappingFactory;
use Webkul\Recurring\Model\RecurringSubscriptionsFactory  as SubscriptionsFactory;
use Magento\Framework\Stdlib\DateTime\DateTime  as Date;
use Webkul\Recurring\Model\Config\Source\DurationType;

class Cron
{
    /**
     * for logging.
     * @var \Webkul\Recurring\Logger\Logger
     */
    protected $logger;
    
    /**
     * @var Term
     */
    private $term;
    
    /**
     * @var MappingFactory
     */
    private $mappingFactory;
    
    /**
     * @var Date
     */
    private $date;
    
    /**
     * @var SubscriptionsFactory
     */
    private $subscriptionsFactory;
    
    /**
     * @var OrderModel
     */
    private $orderModel;

    /**
     * @var \Webkul\Recurring\Helper\Order
     */
    protected $orderHelper;

    /**
     *
     * @param \Webkul\Recurring\Logger\Logger $logger
     * @param Term $term
     * @param MappingFactory $mappingFactory
     * @param OrderModel $orderModel
     * @param Date $date
     * @param SubscriptionsFactory $subscriptionsFactory
     * @param \Webkul\Recurring\Helper\Order $orderHelper
     */
    public function __construct(
        \Webkul\Recurring\Logger\Logger $logger,
        Term $term,
        MappingFactory $mappingFactory,
        OrderModel $orderModel,
        Date $date,
        SubscriptionsFactory $subscriptionsFactory,
        \Webkul\Recurring\Helper\Order $orderHelper
    ) {
        $this->orderHelper              = $orderHelper;
        $this->logger                   = $logger;
        $this->term                     = $term;
        $this->mappingFactory           = $mappingFactory;
        $this->date                     = $date;
        $this->subscriptionsFactory     = $subscriptionsFactory;
        $this->orderModel               = $orderModel;
    }
    
    /**
     * Cron job executed 1 time per five minutes to check the offline recurring orders creation
     */
    public function recurringOrder()
    {
        try {
            $orderIds = [];
            $subscriptionsCollection = $this->subscriptionsFactory->create()
                ->getCollection()
                ->addFieldToFilter("status", true);
            foreach ($subscriptionsCollection as $subscriptionsModel) {
                $subscriptionId    = $subscriptionsModel->getId();
                $planId            = $subscriptionsModel->getPlanId();
                $orderId           = $subscriptionsModel->getOrderId();
                $startDate         = $subscriptionsModel->getStartDate();
                if (!in_array($orderId, $orderIds)) {
                    $this->reProcessSubscription($planId, $orderId, $startDate, $subscriptionId);
                    $orderIds[] = $orderId;
                }
            }
        } catch (\Exception $e) {
            $this->logger->debug($e->getMessage());
            $this->logger->info($e->getMessage());
        }
    }

    /**
     * Re processing subscription
     *
     * @param integer $planId
     * @param integer $orderId
     * @param string $startDate
     * @param integer $subscriptionId
     */
    private function reProcessSubscription($planId, $orderId, $startDate, $subscriptionId)
    {
        $startDateArray = explode(" ", $startDate);
        $duration = 0;
        $date = $this->date->gmtDate('m/d/Y');
        if (isset($planId)) {
            $durationDetails = $this->getDurationDetails($planId);
            $duration = $this->totalDuration($durationDetails);
            $dateFrom =  date_create($startDateArray[0]);
            $dateTo   =  date_create($date);
            $diff     =  date_diff($dateFrom, $dateTo);
            $reNew    =  $this->canRenewed($diff->format('%a'), $duration, $subscriptionId);
            $paymentMethods = [
                \Webkul\Recurring\Model\Stripe\PaymentMethod::CODE,
                \Webkul\Recurring\Model\Paypal\PaymentMethod::CODE
            ];
            $this->logger->info('reNew '.$reNew);
            $order      = $this->orderModel->load($orderId);
            if ($reNew && !in_array($order->getPayment()->getMethodInstance()->getCode(), $paymentMethods)) {
                $this->createOrder($planId, $order, $subscriptionId);
            }
        }
    }

    /**
     * Total Duration period
     *
     * @param array $durationDetail
     * @return int
     */
    private function totalDuration($durationDetail)
    {
        $durationType = $durationDetail['duration_type'];
        switch ($durationType) {
            case DurationType::DAY:
                $duration = $durationDetail['duration'];
                break;
            case DurationType::WEEK:
                $duration = 7;
                break;
            case DurationType::MONTH:
                $duration = 30;
                break;
            case DurationType::YEAR:
                $duration = 365;
                break;
            default:
                $duration = 0;
                break;
        }

        return $duration;
    }

    /**
     * Create order
     *
     * @param integer $planId
     * @param \Webkul\Recurring\Model\Order $order
     * @param integer $subscriptionId
     */
    private function createOrder($planId, $order, $subscriptionId)
    {
        try {
            $plan = $this->getRecurringProductPlans($planId);
            $result = $this->orderHelper->createMageOrder($order, $plan['title']);
            if (isset($result['error']) && $result['error'] == 0) {
                $this->saveMapping($result['id'], $subscriptionId);
            } else {
                $this->logger->info($result['msg']);
            }
        } catch (\Exception $e) {
            $this->logger->info($e->getMessage());
        }
    }

    /**
     * This function is used for mapping the child order with the subscription
     *
     * @param integer $orderId
     * @param integer $subscriptionId
     */
    public function saveMapping($orderId, $subscriptionId)
    {
        $time = date('Y-m-d H:i:s');
        $model = $this->mappingFactory->create();
        $model->setSubscriptionId($subscriptionId);
        $model->setOrderId($orderId);
        $model->setCreatedAt($time);
        $model->save();
    }

    /**
     * This will decide plan should renew or not.
     *
     * @param integer $serveredDays
     * @param integer $duration
     * @param integer $subscriptionId
     * @return array
     */
    private function canRenewed($serveredDays, $duration, $subscriptionId)
    {
        $return = false;
        $todayDate = date('Y-m-d');
        $mappingCollection = $this->mappingFactory->create()->getCollection()
            ->addFieldToFilter('subscription_id', $subscriptionId)
            ->addFieldToFilter('created_at', ['like' => $todayDate.'%']);
        if ($mappingCollection->getSize()) {
            return $return;
        }
        if ($duration == 0) {
            $return = false;
        } elseif ($serveredDays == 0) {
            $return = false;
        } elseif ($duration <= $serveredDays && ($serveredDays % $duration == 0)) {
            $return = true;
        }
        return $return;
    }

    /**
     * This function returns the subscription type details in array form
     *
     * @param integer $planId
     * @return array
     */
    public function getRecurringProductPlans($planId)
    {
        return $this->term->load($planId)->getData();
    }

    /**
     * This function returns the particular plans duration details
     *
     * @param integer $durationId
     * @return array
     */
    private function getDurationDetails($durationId)
    {
        return $this->term->load($durationId)->getData();
    }
}
