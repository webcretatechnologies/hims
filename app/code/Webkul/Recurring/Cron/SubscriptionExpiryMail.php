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
namespace Webkul\Recurring\Cron;

class SubscriptionExpiryMail
{
     /**
      * @var \Webkul\Recurring\Helper\Data
      */
    protected $helper;

     /**
      * @var \Webkul\Recurring\Model\RecurringSubscriptionsFactory
      */
    protected $subscriptions;

    /**
     * @var \Webkul\Recurring\Logger\Logger
     */
    protected $logger;

     /**
      * @var \Magento\Sales\Model\Order
      */
    private $orderModel;

    /**
     * @var \Webkul\Recurring\Helper\Email
     */
    private $emailHelper;
    
    /**
     * Construct function
     *
     * @param \Webkul\Recurring\Helper\Data $helper
     * @param \Webkul\Recurring\Model\RecurringSubscriptionsFactory $subscriptions
     * @param \Webkul\Recurring\Logger\Logger $logger
     * @param \Magento\Sales\Model\Order $orderModel
     * @param \Webkul\Recurring\Helper\Email $emailHelper
     */
    public function __construct(
        \Webkul\Recurring\Helper\Data $helper,
        \Webkul\Recurring\Model\RecurringSubscriptionsFactory $subscriptions,
        \Webkul\Recurring\Logger\Logger $logger,
        \Magento\Sales\Model\Order $orderModel,
        \Webkul\Recurring\Helper\Email $emailHelper
    ) {
        $this->helper = $helper;
        $this->subscriptions = $subscriptions;
        $this->logger = $logger;
        $this->orderModel = $orderModel;
        $this->emailHelper = $emailHelper;
    }
    
    /**
     * Execute
     */
    public function execute()
    {
        try {
            $dayBefore = $this->helper->getReminderDay();
            // get active subscription
            $day = '-'.$dayBefore.'day';
            $collection = $this->subscriptions->create()->getCollection();
            $collection
                ->addFieldToSelect('valid_till')
                ->addFieldToFilter('status', ['eq' =>1]);
            foreach ($collection as $subscription) {
                $validTill = $subscription->getValidTill();
                $expiryDate = date('d M, y', strtotime($validTill));
                $todayDate = date("d M, y");
                $mailDate = date('d M, y', strtotime($validTill . $day));
                if ($mailDate == $todayDate) {
                    $orderInfo = '';
                    $orderId = $subscription->getOrderId();
                    $order = $this->orderModel->load($orderId);
                    $receiverInfo = [];
                    $receiverInfo = [
                        'name' => 'sapna',
                        'email' => 'sapna.bhatt289@webkul.in',
                    ];
                    $orderItems = $order->getAllVisibleItems();
                    $orderInfo = $this->helper->getEmailTemplateVar($order, $orderItems);
                    $emailTempVariables['refProfileId'] = $subscription->getRefProfileId();
                    $emailTempVariables['productName'] = $subscription->getProductName();
                    $emailTempVariables['customerName'] = $order->getCustomerName();
                    $emailTempVariables['validTill'] = $expiryDate;
                    $emailTempVariables['orderItems'] = $orderInfo;
                    
                    $this->emailHelper->sendExpiryEmail(
                        $emailTempVariables,
                        $receiverInfo
                    );
                }
            }
            
        } catch (\Exception $e) {
            $this->logger->info("IsCouponExpired executed ".$e->getMessage());
        }
    }
}
