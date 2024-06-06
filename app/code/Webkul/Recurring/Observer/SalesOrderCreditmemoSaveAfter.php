<?php
/**
 * Webkul Software.
 *
 * @category   Webkul
 * @package    Webkul_Recurring
 * @author     Webkul Software Private Limited
 * @copyright  Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */

namespace Webkul\Recurring\Observer;

use Magento\Framework\Event\ObserverInterface;

class SalesOrderCreditmemoSaveAfter implements ObserverInterface
{

    /**
     * @var \Webkul\Recurring\Model\RecurringSubscriptionsFactory
     */
    protected $subscriptions;

    /**
     * @var \Webkul\Recurring\Helper\Paypal
     */
    protected $paypalHelper;

    /**
     * @var \Webkul\Recurring\Helper\Stripe
     */
    protected $stripeHelper;

    /**
     * @var \Webkul\Recurring\Logger\Logger
     */
    protected $logger;

    /**
     * @var \Webkul\Recurring\Helper\Email
     */
    protected $emailHelper;
    
    /**
     * Construct function
     *
     * @param \Webkul\Recurring\Model\RecurringSubscriptionsFactory $subscriptions
     * @param \Webkul\Recurring\Helper\Paypal $paypalHelper
     * @param \Webkul\Recurring\Helper\Stripe $stripeHelper
     * @param \Webkul\Recurring\Logger\Logger $logger
     * @param \Webkul\Recurring\Helper\Email $emailHelper
     */
    public function __construct(
        \Webkul\Recurring\Model\RecurringSubscriptionsFactory $subscriptions,
        \Webkul\Recurring\Helper\Paypal $paypalHelper,
        \Webkul\Recurring\Helper\Stripe $stripeHelper,
        \Webkul\Recurring\Logger\Logger $logger,
        \Webkul\Recurring\Helper\Email $emailHelper
    ) {
        $this->subscriptions = $subscriptions;
        $this->paypalHelper = $paypalHelper;
        $this->stripeHelper = $stripeHelper;
        $this->logger = $logger;
        $this->emailHelper = $emailHelper;
    }

    /**
     * Observer action for Sales order place after.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            $creditmemo = $observer->getCreditmemo();
            $orderId = $creditmemo->getOrderId();
            $order = $creditmemo->getOrder();
            $collection = $this->subscriptions->create()->getCollection();
            $collection
                ->addFieldToFilter('order_id', ['eq' =>$orderId])
                ->addFieldToFilter('status', ['eq' =>1]);
            $cancellationReason = "refund";
            foreach ($collection as $subscription) {
                if ($subscription->getRefProfileId() != "" && $subscription->getStripeCustomerId() != "") {
                    if ($this->stripeHelper->cancelSubscriptions($subscription)) {
                        $status = false;
                        $this->saveModelData($subscription, $cancellationReason, $status);
                        $this->sendEmailForCancellation($subscription, $order);
                    }
                } else {
                    if ($this->paypalHelper->cancelSubscriptions($subscription)) {
                        $status = false;
                        $this->saveModelData($subscription, $cancellationReason, $status);
                        $this->sendEmailForCancellation($subscription, $order);
                    }
                }
            }
        } catch (\Exception $e) {
            $this->logger->info(
                "Observer_creditmemo execute : ".$e->getMessage()
            );
        }
    }

    /**
     * Save model data function
     *
     * @param \Webkul\Recurring\Model\RecurringSubscriptionsFactory $model
     * @param string $cancellationReason
     * @param bool $status
     */
    protected function saveModelData($model, $cancellationReason, $status)
    {
        try {
            $model->setStatus($status);
            $model->setCancellationReason($cancellationReason);
            $model->setId($model->getId())->save();
        } catch (\Exception $e) {
            $this->logger->info('saveModelData'. $e->getMessage());
        }
    }

    /**
     * Send mail for cancelled subscription function
     *
     * @param \Webkul\Recurring\Model\RecurringSubscriptionsFactory $model
     * @param \Magento\Sales\Model\Order $order
     */
    protected function sendEmailForCancellation($model, $order)
    {
        $receiverInfo = [];
        $receiverInfo = [
            'name' => $order->getCustomerName(),
            'email' => $order->getCustomerEmail(),
        ];
        $emailTempVariables['productId'] = $model->getProductId();
        $emailTempVariables['productName'] = $model->getProductName();
        $emailTempVariables['refProfileId'] = $model->getRefProfileId();
        $emailTempVariables['customerName'] = $order->getCustomerName();

        $this->emailHelper->sendSubscriptionCancelEmail(
            $emailTempVariables,
            $receiverInfo
        );
    }
}
