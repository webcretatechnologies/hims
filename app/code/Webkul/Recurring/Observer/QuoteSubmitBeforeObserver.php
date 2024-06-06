<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Recurring
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Recurring\Observer;
 
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Webkul\Recurring\Model\RecurringProductPlans;
use Webkul\Recurring\Model\RecurringSubscriptions;
use Webkul\Recurring\Model\RecurringTermsFactory;
use Webkul\Recurring\Model\Config\Source\DurationType;
 
class QuoteSubmitBeforeObserver implements ObserverInterface
{
    /**
     * @var array
     */
    private $quoteItems = [];

    /**
     * @var \Magento\Quote\Model\ResourceModel\Quote
     */
    private $quote = null;

    /**
     * @var \Magento\Sales\Model\Order
     */
    private $order = null;

    /**
     * @var RecurringProductPlans
     */
    private $plans;

    /**
     * @var RecurringTermsFactory
     */
    private $term;

    /**
     * @var RecurringSubscriptions
     */
    private $subscriptions;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    private $jsonHelper;
    
    /**
     *
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    private $timeZone;

    /**
     * @var \Webkul\Recurring\Helper\Data
     */
    private $helper;
     
    /**
     * @param RecurringProductPlans $plans
     * @param RecurringTermsFactory $term
     * @param RecurringSubscriptions $subscriptions
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timeZone
     * @param \Webkul\Recurring\Helper\Data $helper
     */
    public function __construct(
        RecurringProductPlans $plans,
        RecurringTermsFactory $term,
        RecurringSubscriptions $subscriptions,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timeZone,
        \Webkul\Recurring\Helper\Data $helper
    ) {
        $this->timeZone         = $timeZone;
        $this->jsonHelper       = $jsonHelper;
        $this->plans            = $plans;
        $this->term             = $term;
        $this->subscriptions    = $subscriptions;
        $this->helper           = $helper;
    }
    
    /**
     * This function is used to save subscription
     *
     * @param EventObserver $observer
     */
    public function execute(EventObserver $observer)
    {
        try {
            $this->quote = $observer->getQuote();
            $this->order = $observer->getOrder();
            $refProfileId = '';
            foreach ($this->order->getItems() as $orderItem) {
                if ($quoteItem = $this->getQuoteItemById($orderItem->getQuoteItemId())) {
                    if ($additionalOptionsQuote = $quoteItem->getOptionByCode('additional_options')) {
                        $additionalOptionsQuote = $this->jsonHelper->jsonDecode(
                            $additionalOptionsQuote->getValue()
                        );
                        if ($additionalOptionsOrder = $orderItem->getProductOptionByCode('additional_options')) {
                            $additionalOptions = $this->getMergedArray(
                                $additionalOptionsQuote,
                                $additionalOptionsOrder
                            );
                        } else {
                            $additionalOptions = $additionalOptionsQuote;
                        }
                        $options = $orderItem->getProductOptions();
                        $options['additional_options'] = $additionalOptions;
                        $orderItem->setProductOptions($options);
                    }
                    if ($customAdditionalOptionsQuote = $quoteItem->getOptionByCode('custom_additional_options')) {
                        $allOptions = $this->jsonHelper->jsonDecode(
                            $customAdditionalOptionsQuote->getValue()
                        );
                        $recurringProductPlansId =  '';
                        $startDate =  '';
                        $endDate =  '';
                        list(
                            $termId, $recurringProductPlansId, $startDate, $endDate
                        ) = $this->getSubscriptionData($allOptions);
                        $subscriptionStatus = 1;
                        if ($this->order
                        ->getPayment()
                        ->getMethodInstance()
                        ->getCode() == \Webkul\Recurring\Model\Paypal\PaymentMethod::CODE) {
                            $subscriptionStatus = 0;
                        } elseif ($this->order
                        ->getPayment()
                        ->getMethodInstance()
                        ->getCode() == \Webkul\Recurring\Model\Stripe\PaymentMethod::CODE) {
                            $subscriptionStatus = 0;
                        }
                        if ($recurringProductPlansId) {
                            $firstName         = $this->order->getCustomerFirstname();
                            $lastName          = $this->order->getCustomerLastname();
                            $customerName      = $firstName." ".$lastName;
                            $planData          = $this->getPlanData($recurringProductPlansId);
                            $currentDateObject = $this->timeZone->date();
                            $currentDate       = $currentDateObject->format('Y-m-d H:i:s.u');
                            $currentDate       = str_replace(".000000", "", $currentDate);
                            $explodedValues    =  explode(" ", $currentDate);
                            $currentTime       =  $explodedValues[1] ?? "00:00:00";
                            $startDate         = date_format(date_create($startDate), "Y-m-d H:i:s");
                            $startDate         = str_replace("00:00:00", $currentTime, $startDate);
                            $endDate = date_format(date_create($endDate), "Y-m-d H:i:s");
                            $endDate         = str_replace("00:00:00", $currentTime, $endDate);
                            $data = [
                            'order_id'       =>  $this->order->getId(),
                            'product_id'     =>  $quoteItem->getProductId(),
                            'product_name'   =>  $quoteItem->getProduct()->getName(),
                            'customer_id'    =>  $this->order->getCustomerId(),
                            'customer_name'  =>  $customerName,
                            'plan_id'        =>  $termId,
                            'start_date'     =>  $startDate,
                            'end_date'       =>  $endDate,
                            'extra'          =>  $this->jsonHelper->jsonEncode($planData),
                            'status'         =>  $subscriptionStatus,
                            'ref_profile_id' =>  $refProfileId,
                            'created_at'     =>  $currentDate,
                            'valid_till'     =>  $this->getValidTill($planData['type'], $startDate)
                            ];
                            $this->saveSubscriptionData($data);
                        }
                    }
                }
            }
            $this->order->save();
        } catch (\Exception $e) {
            $this->helper->logDataInLogger(
                "Observer_QuoteSubmitBefore_execute: ".$e->getMessage()
            );
        }
    }

    /**
     * Get Valid Till Date of subscription
     *
     * @param string $planTypeId
     * @param string $startDate
     * @return string
     */
    public function getValidTill($planTypeId, $startDate)
    {
        $validTill = $startDate;
        $term  = $this->term->create()->load($planTypeId);
        $durationType = $term->getDurationType();
        switch ($durationType) {
            case DurationType::DAY:
                $validTill = date('Y-m-d', strtotime($startDate . ' + ' . 1 . DurationType::DAY));
                break;
            case DurationType::WEEK:
                $validTill = date('Y-m-d', strtotime($startDate . ' + ' . 1 . DurationType::WEEK));
                break;
            case DurationType::MONTH:
                $validTill = date('Y-m-d', strtotime($startDate . ' + ' . 1 . DurationType::MONTH));
                break;
            case DurationType::YEAR:
                $validTill = date('Y-m-d', strtotime($startDate . ' + ' . 1 . DurationType::YEAR));
                break;
        }
        return $validTill;
    }

    /**
     * Get Plan Data
     *
     * @param integer $recurringProductPlansId
     * @return array
     */
    private function getPlanData($recurringProductPlansId)
    {
        return $this->plans->load($recurringProductPlansId)->getData();
    }

    /**
     * Save Subscription Data
     *
     * @param array $data
     */
    private function saveSubscriptionData($data)
    {
        $subscriptionsModel = $this->subscriptions;
        $subscriptionsModel->setData($data);
        $subscriptionsModel->save();
    }

    /**
     * This function returns then valid quote item id
     *
     * @param integer $id
     * @return mixed
     */
    private function getQuoteItemById($id)
    {
        if (empty($this->quoteItems)) {
            foreach ($this->quote->getAllVisibleItems() as $item) {
                $this->quoteItems[$item->getId()] = $item;
            }
        }
        if (array_key_exists($id, $this->quoteItems)) {
            return $this->quoteItems[$id];
        }
        return null;
    }

    /**
     * Get start date and subscription typeId
     *
     * @param array $allOptions
     * @return array
     */
    private function getSubscriptionData($allOptions)
    {
        $recurringProductPlansId = '';
        $startDate = '';
        $endDate = '';
        foreach ($allOptions as $key => $option) {
            if ($option['label'] == 'Plan Id') {
                $recurringProductPlansId = $option['value'];
            }
            if ($option['label'] == 'Term Id') {
                $termId = $option['value'];
            }
            if ($option['label'] == 'Start Date') {
                $startDate = $option['value'];
            }
            if ($option['label'] == 'End Date') {
                $endDate = $option['value'];
            }
        }
        return [
            $termId, $recurringProductPlansId, $startDate, $endDate
        ];
    }

    /**
     * Get Merged Array from two Arrays
     *
     * @param array $additionalOptionsQuote
     * @param array $additionalOptionsOrder
     * @return array
     */
    private function getMergedArray($additionalOptionsQuote, $additionalOptionsOrder)
    {
        $additionalOptions = array_merge($additionalOptionsQuote, $additionalOptionsOrder);
        return array_unique($additionalOptions, SORT_REGULAR);
    }
}
