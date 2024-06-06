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
namespace Webkul\Recurring\Helper;

use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Sales\Model\Order\Payment\Transaction;
use Magento\Sales\Model\Order;
use Webkul\Recurring\Model\RecurringTermsFactory;
use Webkul\Recurring\Model\Config\Source\DurationType;

/**
 * Stripe data helper.
 */
class Stripe extends \Magento\Framework\App\Helper\AbstractHelper
{
    public const METHOD_CODE = \Webkul\Recurring\Model\Stripe\PaymentMethod::CODE;
    public const MAX_SAVED_CARDS = 30;
    public const CARD_IS_ACTIVE = 1;
    public const CARD_NOT_ACTIVE = 0;
    public const API_SECRET_KEY = 'api_secret_key';
    public const API_PUBLISH_KEY = 'api_publish_key';
    public const STRIPE_API_VERSION = '2022-11-15';

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var \Magento\Framework\Locale\Resolver
     */
    protected $resolver;
    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    protected $encryptor;
    /**
     * @var \Magento\Framework\HTTP\Client\Curl
     */
    protected $curl;
    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderModel;
    /**
     * @var \Webkul\Recurring\Model\RecurringSubscriptionsFactory
     */
    protected $subscription;
    /**
     * @var \Webkul\Recurring\Model\Cron
     */
    protected $cron;
    /**
     * @var \Webkul\Recurring\Helper\Order
     */
    protected $orderHelper;
    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;
    /**
     * @var \Magento\Sales\Model\Service\InvoiceService
     */
    protected $invoiceService;
    /**
     * @var \Magento\Framework\DB\Transaction
     */
    protected $transaction;
    /**
     * @var InvoiceSender
     */
    protected $invoiceSender;
    /**
     * @var Transaction\BuilderInterface
     */
    protected $transactionBuilder;
    /**
     * @var \Webkul\Recurring\Logger\Logger
     */
    protected $logger;

    /**
     * @var string
     */
    protected $stripeClient;

    /**
     * @var \Webkul\Recurring\Helper\Email
     */
    protected $emailHelper;

    /**
     * @var RecurringTermsFactory
     */
    private $termFactory;

     /**
      * @var \Webkul\Recurring\Helper\Data
      */
    private $helper;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Locale\Resolver $resolver
     * @param \Magento\Framework\Encryption\EncryptorInterface $encryptor
     * @param \Magento\Framework\HTTP\Client\Curl $curl
     * @param \Magento\Sales\Model\OrderFactory $orderModel
     * @param \Webkul\Recurring\Model\RecurringSubscriptionsFactory $subscription
     * @param \Webkul\Recurring\Model\Cron $cron
     * @param \Webkul\Recurring\Helper\Order $orderHelper
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Sales\Model\Service\InvoiceService $invoiceService
     * @param \Magento\Framework\DB\Transaction $transaction
     * @param InvoiceSender $invoiceSender
     * @param Transaction\BuilderInterface $transactionBuilder
     * @param \Webkul\Recurring\Logger\Logger $logger
     * @param \Webkul\Recurring\Helper\Email $emailHelper
     * @param RecurringTermsFactory $termFactory
     * @param \Webkul\Recurring\Helper\Data $helper = null
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Locale\Resolver $resolver,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magento\Framework\HTTP\Client\Curl $curl,
        \Magento\Sales\Model\OrderFactory $orderModel,
        \Webkul\Recurring\Model\RecurringSubscriptionsFactory $subscription,
        \Webkul\Recurring\Model\Cron $cron,
        \Webkul\Recurring\Helper\Order $orderHelper,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Sales\Model\Service\InvoiceService $invoiceService,
        \Magento\Framework\DB\Transaction $transaction,
        InvoiceSender $invoiceSender,
        Transaction\BuilderInterface $transactionBuilder,
        \Webkul\Recurring\Logger\Logger $logger,
        \Webkul\Recurring\Helper\Email $emailHelper,
        RecurringTermsFactory $termFactory,
        \Webkul\Recurring\Helper\Data $helper = null
    ) {
        $this->storeManager = $storeManager;
        $this->resolver =  $resolver;
        $this->encryptor = $encryptor;
        $this->curl = $curl;
        $this->orderModel = $orderModel;
        $this->subscription = $subscription;
        $this->cron = $cron;
        $this->orderHelper = $orderHelper;
        $this->jsonHelper = $jsonHelper;
        $this->invoiceService = $invoiceService;
        $this->transaction = $transaction;
        $this->invoiceSender = $invoiceSender;
        $this->transactionBuilder = $transactionBuilder;
        $this->logger = $logger;
        $this->emailHelper = $emailHelper;
        $this->termFactory   = $termFactory;
        $this->helper = $helper ?: \Magento\Framework\App\ObjectManager::getInstance()
        ->create(\Webkul\Recurring\Helper\Data::class);
        parent::__construct($context);
    }
    
    /**
     * Function to get Config Data.
     *
     * @param bool $field
     * @return string
     */
    public function getConfigValue($field = false)
    {
        if ($field) {
            if ($field == self::API_SECRET_KEY || $field == self::API_PUBLISH_KEY) {
                return $this->encryptor->decrypt(
                    $this->scopeConfig->getValue(
                        'payment/'.self::METHOD_CODE.'/'.$field,
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                    )
                );
            } else {
                return $this->scopeConfig->getValue(
                    'payment/'.self::METHOD_CODE.'/'.$field,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                );
            }
        } else {
            return;
        }
    }

    /**
     * Check if payment method active.
     *
     * @return bool
     */
    public function getIsActive()
    {
        return $this->getConfigValue('active');
    }

    /**
     * This model is used to cancel the paypal recurring payment
     *
     * @param \Webkul\Recurring\Model\RecurringSubscriptions $model
     * @return bool
     */
    public function cancelSubscriptions($model)
    {
        /*
         * set api key for payment  >> sandbox api key or live api key
         */
        try {
            $this->stripeClient = $this->setStripeInstance();
            $subscriptionId = $model->getRefProfileId();
            $subscriptionItemId = $model->getSubscriptionItemId();
            $subscription = $this->stripeClient->subscriptions->retrieve($subscriptionId);
            $subscriptionItem = $this->stripeClient->subscriptionItems->retrieve($subscriptionItemId);
            $subscriptionItemsList = $this->stripeClient->subscriptionItems->all([
                'subscription' => $subscriptionId
            ]);
            if (count($subscriptionItemsList) == 1) {
                $subscription->delete();
                return true;
            } elseif (isset($subscription["id"]) && $subscriptionItem['id']) {
                $subscriptionItem->delete();
                return true;
            }
            return false;
        } catch (\Exception $e) {
            $this->logger->info('cancelSubscriptions'.$e->getMessage());
        }
    }

    /**
     * This model is used to hold the paypal recurring payment
     *
     * @param \Webkul\Recurring\Model\RecurringSubscriptions $model
     * @return bool
     */
    public function holdSubscriptions($model)
    {
        /*
         * set api key for payment  >> sandbox api key or live api key
         */
        try {
            $this->stripeClient = $this->setStripeInstance();
            $subscriptionId = $model->getRefProfileId();
            $subscriptionItemsList = $this->stripeClient->subscriptionItems->all([
                'subscription' => $subscriptionId
            ]);
                    
            if (count($subscriptionItemsList) == 1) {
                $this->stripeClient->subscriptions->update(
                    $subscriptionId,
                    ['pause_collection' => ['behavior' => 'void']]
                );
                return true;
            }
            return false;
        } catch (\Exception $e) {
            $this->logger->info('holdSubscriptions'.$e->getMessage());
        }
    }

    /**
     * This model is used to resume the paypal recurring payment
     *
     * @param \Webkul\Recurring\Model\RecurringSubscriptions $model
     * @return bool
     */
    public function resumeSubscriptions($model)
    {
        /*
         * set api key for payment  >> sandbox api key or live api key
         */
        try {
            $this->stripeClient = $this->setStripeInstance();
            $subscriptionId = $model->getRefProfileId();
            $subscriptionItemsList = $this->stripeClient->subscriptionItems->all([
                'subscription' => $subscriptionId
            ]);
                    
            if (count($subscriptionItemsList) == 1) {
                $this->stripeClient->subscriptions->update(
                    $subscriptionId,
                    ['pause_collection' => '']
                );
                return true;
            }
            return false;
        } catch (\Exception $e) {
            $this->logger->info('resumeSubscriptions'.$e->getMessage());
        }
    }

    /**
     * Get media url
     *
     * @return string
     */
    public function getMediaUrl()
    {
        /** @var Store $store */
        $store = $this->storeManager->getStore();
        return $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
    }

    /**
     * Get the current set locale code from configuration.
     *
     * @return string
     */
    public function getLocaleFromConfiguration()
    {
        return $this->resolver->getLocale();
    }

    /**
     * Returns the locale value exist in stripe api
     *
     * Other wise return "auto"
     *
     * @return string
     */
    public function getLocaleForStripe()
    {
        $configLocale = $this->getLocaleFromConfiguration();
        if ($configLocale) {
            $temp = explode('_', $configLocale);
            if (isset($temp['0'])) {
                $configLocale = $temp['0'];
            }
        }
        $stripeLocale = $this->matchCodeSupportedByStripeApi($configLocale);
        return $stripeLocale;
    }

    /**
     * Matches the configuration locale to the locale exixt in strip api
     *
     * @param string $configLocale
     * @return string
     */
    public function matchCodeSupportedByStripeApi($configLocale)
    {
        if (in_array($configLocale, ["zh","da","nl","en","fi","fr","de","it","ja","no","es","sv"])) {
            return $configLocale;
        }
        return "auto";
    }

    /**
     * Get whether customer exists or not on stripe
     *
     * @param string $custID
     * @return bool|null
     */
    public function customerExist($custID = null)
    {
        try {
            $secretKey = $this->getConfigValue(self::API_SECRET_KEY);
            $url = "https://api.stripe.com/v1/customers/".$custID;
            $headers =['Authorization: Bearer '.$secretKey,];
            $arr = [
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTPHEADER =>$headers,
                CURLOPT_HEADER => true,
                CURLOPT_RETURNTRANSFER => true,
            ];
            $this->curl->addHeader('Authorization: Bearer ', $secretKey);
            $this->curl->setOptions($arr);
            $this->curl->get($url);
            if ($this->curl->getStatus() == 200) {
                return 1;
            } else {
                return 0;
            }
        } catch (\Exception $e) {
            $this->logger->info($e->getMessage());
        }
    }

    /**
     * SaveSubscriptionData
     *
     * @param array $response
     */
    public function saveSubscriptionData($response)
    {
        $incrementId = 0;
        $this->stripeClient = $this->setStripeInstance();
        $subscriptionItems = $this->stripeClient->subscriptionItems->all([
            'subscription' => $response["data"]["object"]["subscription"]
        ]);
        $incrementId = $subscriptionItems["data"][0]["plan"]["nickname"];
        $order = $this->loadOrder($incrementId);
        $subscriptionsCollection = $this->subscription->create()
            ->getCollection()
            ->addFieldToFilter(
                "order_id",
                $order->getId()
            );
        foreach ($subscriptionItems['data'] as $item) {
            $subscriptionItemId = $item['id'];
            $productId = $item['plan']['metadata']['product_id'];
            foreach ($subscriptionsCollection as $subscription) {
                if ($subscription['product_id'] == $productId && $subscriptionItemId != null) {
                    $this->saveSubscription(
                        $subscription,
                        $subscriptionItemId,
                        $response["data"]["object"]["subscription"],
                        $response["data"]["object"]["customer"]
                    );
                }
                
            }
        }
    }

    /**
     * This function is used to save the stripe subscription id
     *
     * @param \Webkul\Recurring\Model\RecurringSubscriptions $model
     * @param int $subscriptionItemId
     * @param integer $profileId
     * @param int $stripeCustomerId
     */
    private function saveSubscription($model, $subscriptionItemId, $profileId, $stripeCustomerId)
    {
        $paymentCode = \Webkul\Recurring\Model\Stripe\PaymentMethod::CODE;
        $model->setData('ref_profile_id', $profileId);
        $model->setData('stripe_customer_id', $stripeCustomerId);
        $model->setData('subscription_item_id', $subscriptionItemId);
        $model->setData('payment_code', $paymentCode);
        $model->setData('status', 1);
        $model->setId($model->getId());
        $model->save();
    }

    /**
     * ProcessSubscription
     *
     * @param array $response
     */
    public function processSubscription($response)
    {
        try {
            $subscriptionsCollection = $this->subscription->create()
            ->getCollection()
            ->addFieldToFilter(
                "ref_profile_id",
                $response["data"]["object"]["subscription"]
            )
            ->addFieldToFilter(
                "stripe_customer_id",
                $response["data"]["object"]["customer"]
            );
            $planId = $subscriptionId = 0;
            $chargeId = $response["data"]["object"]["charge"];
            $this->stripeClient = $this->setStripeInstance();
            $charge = $this->stripeClient->charges->retrieve($chargeId);
            if (($charge["status"] == "paid" ) || ( $charge["status"] ==  "succeeded")) {
                foreach ($subscriptionsCollection as $subscription) {
                    $subscriptionId = $subscription->getId();
                    $planId         = $subscription->getPlanId();
                    $createdAt      = $subscription->getCreatedAt();
                }
                $todayDate = date('Y-m-d');
                $txnId = $response["data"]["object"]["charge"];
                if ($planId && strpos($createdAt, $todayDate) === false) {
                    $subscriptionItems = $this->stripeClient->subscriptionItems->all([
                        'subscription' => $response["data"]["object"]["subscription"]
                    ]);
                
                    $incrementId = $subscriptionItems["data"][0]["plan"]["nickname"];
                    $order = $this->loadOrder($incrementId);
                    $this->createOrder($planId, $order, $subscriptionId, $txnId, $response);
                } elseif ($planId) {
                    $subscriptionItems = $this->stripeClient->subscriptionItems->all([
                        'subscription' => $response["data"]["object"]["subscription"]
                    ]);
                    $incrementId = $subscriptionItems["data"][0]["plan"]["nickname"];
                    $order = $this->loadOrder($incrementId);
                    $order->setTotalPaid($order->getGrandTotal())
                    ->setBaseTotalPaid($order->getBaseGrandTotal())
                    ->save();
                    $orderInfo = '';
                    $receiverInfo = [];
                    $receiverInfo = [
                        'name' => $order->getCustomerName(),
                        'email' => $order->getCustomerEmail(),
                    ];
                    $orderItems = $order->getAllVisibleItems();
                    $orderInfo = $this->helper->getEmailTemplateVar($order, $orderItems);
                    
                    $emailTempVariables['orderItems'] = $orderInfo;
                    $emailTempVariables['refProfileId'] = $response["data"]["object"]["subscription"];
                    $emailTempVariables['customerName'] = $order->getCustomerName();
                
                    $this->emailHelper->sendNewSubscriptionEmail(
                        $emailTempVariables,
                        $receiverInfo
                    );
                    $this->createInvoice($order, $txnId, $response);
                    $this->createFirstTransaction($order, $txnId, $response);
                }
            }
        } catch (\Exception $e) {
            $this->logger->info('processSubscription : '.$e->getMessage());
        }
    }
    
    /**
     * Create order in magento for stripe recurring subscription
     *
     * @param integer $planId
     * @param integer $order
     * @param integer $subscriptionId
     * @param string $txnId
     * @param array $response
     */
    private function createOrder($planId, $order, $subscriptionId, $txnId, $response)
    {
        try {
            $plan = $this->cron->getRecurringProductPlans($planId);
            $result = $this->orderHelper->createMageOrder($order, $plan['title']);
            
            if (isset($result['error']) && $result['error'] == 0) {
                $this->cron->saveMapping($result['id'], $subscriptionId);
                $this->updateValidTill($planId, $subscriptionId);
                $this->createTransaction($result['id'], $txnId, $response);
            }
        } catch (\Exception $e) {
            $this->logger->info('Controller_Subscription_Webhook1 : '.$e->getMessage());
        }
    }

    /**
     * Update Valid Till
     *
     * @param int $planId
     * @param int $subscriptionId
     */
    public function updateValidTill($planId, $subscriptionId)
    {
        $subscription = $this->subscription->create()->load($subscriptionId);
        $tillDate = $subscription->getValidTill();
        $validTillDate =
        empty($tillDate)?date_format(date_create(date('m/d/Y')), "Y-m-d H:i:s"):$tillDate;
        $validTill = $this->getValidTill($planId, $validTillDate);
        $subscription->setValidTill($validTill);
        $subscription->save();
    }

    /**
     * To get Valid Till Date of subscription
     *
     * @param string $typeId
     * @param string $startDate
     * @return string
     */
    public function getValidTill($typeId, $startDate)
    {
        $validTill = $startDate;
        $term  = $this->termFactory->create()->load($typeId);
        $termType = $term->getDurationType();
        switch ($termType) {
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
     * Create transaction
     *
     * @param integer $id
     * @param integer $txnId
     * @param array $responseData
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function createTransaction($id, $txnId, $responseData)
    {
        $order = $this->orderModel->create()->load($id);
        $payment = $order->getPayment();
        $payment->setTransactionId($txnId);
        $txn = $payment->addTransaction(
            \Magento\Sales\Model\Order\Payment\Transaction::TYPE_CAPTURE
        );
        $payment->setIsTransactionClosed(1);
         
        $payment->setTransactionAdditionalInfo(
            \Magento\Sales\Model\Order\Payment\Transaction::RAW_DETAILS,
            ['description' => $this->jsonHelper->jsonEncode($responseData)]
        );
        $txn->setIsTransactionClosed(1);
        $txn->save();
        $comment = "event id: ".$responseData['id'];
        $history = $order->addStatusHistoryComment($comment, false);
        $history->setIsCustomerNotified(true);
        if ($order->getState() != null) {
            $order->setStatus($order->getState())
            ->save();
        } else {
            $order->setStatus(\Magento\Sales\Model\Order::STATE_PROCESSING)
            ->save();
        }
        try {
            if (!$order->canInvoice()) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Cannot create an invoice.')
                );
            }
            $invoice = $this->invoiceService->prepareInvoice($order);
            if (!$invoice->getTotalQty()) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Cannot create an invoice without products.')
                );
            }
            $invoice->setRequestedCaptureCase(
                \Magento\Sales\Model\Order\Invoice::CAPTURE_ONLINE
            );
            $invoice->register();
            $txnSave = $this->transaction
                ->addObject($invoice)
                ->addObject($invoice->getOrder());
            $txnSave->save();
            $this->invoiceSender->send($invoice);
            $order->setState(\Magento\Sales\Model\Order::STATE_PROCESSING)
            ->setStatus(\Magento\Sales\Model\Order::STATE_PROCESSING)
            ->save();
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->logger->info('Controller_Subscription_Webhook2 : '.$e->getMessage());
        } catch (\Exception $e) {
            $this->logger->info('Controller_Subscription_Webhook3 : '.$e->getMessage());
        }
    }

    /**
     * Create First transaction
     *
     * @param \Magento\Sales\Model\OrderFactory $order
     * @param integer $txnId
     * @param array $responseData
     */
    private function createFirstTransaction($order, $txnId, $responseData)
    {
        try {
            $payment = $order->getPayment();
            $payment->setTransactionId($txnId);
            $transaction = $payment->addTransaction(
                \Magento\Sales\Model\Order\Payment\Transaction::TYPE_CAPTURE
            );
            $payment->setIsTransactionClosed(1);
            
            $payment->setTransactionAdditionalInfo(
                \Magento\Sales\Model\Order\Payment\Transaction::RAW_DETAILS,
                ['description' => $this->jsonHelper->jsonEncode($responseData)]
            );
            $transaction->setIsTransactionClosed(1);
            $transaction->save();
            $comment = "event id: ".$responseData['id'];
            $history = $order->addStatusHistoryComment($comment, false);
            $history->setIsCustomerNotified(true);
            if ($order->getState() != null) {
                $order->setStatus($order->getState())
                ->save();
            } else {
                $order->setStatus(\Magento\Sales\Model\Order::STATE_PROCESSING)
                ->save();
            }
            
        } catch (\Exception $e) {
            $this->logger->info($e->getMessage());
            
        }
    }

    /**
     * Create invoice
     *
     * @param \Magento\Sales\Model\OrderFactory $order
     * @param integer $txnId
     * @param array $responseData
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function createInvoice($order, $txnId, $responseData)
    {
        $resultData = $this->convertValuesToJson(
            $responseData
        );
        $payment = $order->getPayment();
        $payment->setTransactionId($txnId);
        $payment->setAdditionalInformation(
            [\Magento\Sales\Model\Order\Payment\Transaction::RAW_DETAILS => (array) $resultData]
        );
        $trans = $this->transactionBuilder;
        $transaction = $trans->setPayment($payment)
            ->setOrder($order)
            ->setTransactionId($txnId)
            ->setAdditionalInformation(
                [\Magento\Sales\Model\Order\Payment\Transaction::RAW_DETAILS => (array) $resultData]
            )
            ->setFailSafe(true)
            ->build(\Magento\Sales\Model\Order\Payment\Transaction::TYPE_CAPTURE);
        $payment->setParentTransactionId(null);
        $payment->save();
        $transaction->save();
        $comment = "invoice id: ".$responseData['data']['object']['id'];
        $history = $order->addStatusHistoryComment($comment);
        $history->setIsCustomerNotified(true);
        try {
            if (!$order->canInvoice()) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Cannot create an invoice.')
                );
            }
            $orderInvoice = $this->invoiceService->prepareInvoice($order);
            if (!$orderInvoice->getTotalQty()) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Cannot create an invoice without products.')
                );
            }
            $orderInvoice->setTransactionId($txnId);
            $orderInvoice->setRequestedCaptureCase(
                \Magento\Sales\Model\Order\Invoice::CAPTURE_ONLINE
            );
            $orderInvoice->register();
            $orderInvoice->save();
            $transactionSave = $this->transaction
                ->addObject($orderInvoice)
                ->addObject($orderInvoice->getOrder());
            $transactionSave->save();
            $this->invoiceSender->send($orderInvoice);
            if ($order->getState() != null) {
                $order->setState($order->getState())
                ->setStatus($order->getState())
                ->save();
            } else {
                $order->setState(\Magento\Sales\Model\Order::STATE_PROCESSING)
                ->setStatus(\Magento\Sales\Model\Order::STATE_PROCESSING)
                ->save();
            }
            
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->logger->info('Controller_Subscription_Webhook4 : '.$e->getMessage());
        } catch (\Exception $e) {
            $this->logger->info('Controller_Subscription_Webhook5 : '.$e->getMessage());
        }
    }

    /**
     * Convert values of array to json
     *
     * @param array $responseDataArray
     * @return array
     */
    public function convertValuesToJson($responseDataArray)
    {
        foreach ($responseDataArray as $key => $value) {
            $responseDataArray[$key] = $this->jsonHelper->jsonEncode($value);
        }
        return $responseDataArray;
    }

    /**
     * Load order by id
     *
     * @param int $id
     * @return \Magento\Sales\Model\Order
     */
    public function loadOrder($id)
    {
        return $this->orderModel->create()->loadByIncrementId($id);
    }

    /**
     * Decline Order
     *
     * @param \Magento\Sales\Model\OrderFactory $order
     */
    public function declineOrder($order = null)
    {
        if ($order != null) {
            $message = __('Order cancelled because stripe payment failed');
            try {
                $order->cancel();
                $order->registerCancellation($message);
                $history = $order->addStatusHistoryComment($message, false);
                $history->setIsCustomerNotified(false);
                $order->save();
            } catch (\Exception $e) {
                $this->logger->info("declineOrder : ".$e->getMessage());
            }
        }
    }

    /**
     * Charge closed
     *
     * @param array $response
     */
    public function chargeClosed($response)
    {
        $this->stripeClient = $this->setStripeInstance();
        $paymentIntent = $response['data']['object']['payment_intent'];
        $paymentIntentResponse = $this->stripeClient->paymentIntents->retrieve($paymentIntent);
        $orderId = $paymentIntentResponse['metadata']['magento_order_id'];
        $trackingcoll = $this->orderModel->create()->getCollection()
            ->addFieldToFilter('order_id', $orderId);
        foreach ($trackingcoll as $tracking) {
            $tranferId = $tracking->getStripePaymentIntentTransferId();
            if ($tranferId && $response['data']['object']['status'] == 'lost') {
                $transferResponse = $this->stripeClient->transfers->createReversal($tranferId);
            }
        }
        $order = $this->orderModel->create()->load($orderId);
        $order->setState(Order::STATE_CLOSED, true);
        $order->setStatus(Order::STATE_CLOSED);
        $order
        ->addStatusToHistory($order
        ->getStatus(), 'Order closed after dispute and transfer reversed ' . $response['data']['object']['id']);
        $order->save();
    }

    /**
     * Set Stripe Instance
     *
     * @return Stripe\StripeClient
     */
    public function setStripeInstance()
    {
        $stripeInstance = '';
        $stripeKey = $this->getConfigValue(self::API_SECRET_KEY);
        try {
            if (!empty($stripeKey)) {
                $stripeInstance = new \Stripe\StripeClient([
                    'api_key' => $stripeKey,
                    'stripe_version' => self::STRIPE_API_VERSION,
                ]);
            }
        } catch (\Exception $e) {
            $this->logger->info($e->getMessage());
        }
        return $stripeInstance;
    }
}
