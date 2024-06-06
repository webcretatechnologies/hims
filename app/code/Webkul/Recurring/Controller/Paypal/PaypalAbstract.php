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
namespace Webkul\Recurring\Controller\Paypal;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Sales\Model\Order as OrderModel;
use Webkul\Recurring\Model\RecurringTermsFactory  as Term;
use Webkul\Recurring\Model\RecurringSubscriptions  as Subscriptions;
use Magento\Framework\Stdlib\DateTime\DateTime  as Date;
use Magento\Quote\Model\QuoteRepository;
use Magento\Checkout\Model\Cart as CheckoutCart;
use Magento\Framework\App\RequestInterface;
use Webkul\Recurring\Model\RecurringProductPlans;
use Magento\Sales\Model\Order\Payment\Transaction;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Framework\App\Request\InvalidRequestException;

/**
 * Webkul Recurring Landing page Index Controller.
 */
abstract class PaypalAbstract extends Action implements \Magento\Framework\App\CsrfAwareActionInterface
{
    public const  SANDBOX          = "payment/recurringpaypal/sandbox";
    public const  USERNAME         = "payment/recurringpaypal/api_username";
    public const  PASSWORD         = "payment/recurringpaypal/api_password";
    public const  SIGNATURE        = "payment/recurringpaypal/api_signature";
    public const  CLIENT_ID        = "payment/recurringpaypal/client_id";
    public const  SECRET_KEY       = "payment/recurringpaypal/secret_key";
    public const  URL              = "https://api-m.";
    public const  URL_COMPLETE     = "paypal.com/v1/";
    public const  CANCEL_URL       = 'recurring/paypal/cancel';
    public const  RETURN_URL       = 'recurring/paypal/returnAction';
    public const  NOTIFICATION_URL = 'recurring/paypal/notify';
    /**
     * @var PageFactory
     */
    protected $helper;

    /**
     * @var subscriptions
     */
    protected $subscriptions;

    /**
     * @var Date
     */
    protected $date;

    /**
     * @var \Webkul\Recurring\Logger\Logger
     */
    protected $logger;

    /**
     * @var \Magento\Framework\HTTP\Client\Curl
     */
    protected $curl;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var CheckoutCart
     */
    protected $checkoutCart;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
    protected $coreSession;
    
    /**
     * @var \Magento\Framework\Controller\ResultFactory
     */
    protected $resultRedirect;

    /**
     * @var QuoteRepository
     */
    protected $quoteRepository;

    /**
     * @var \Magento\Framework\DB\Transaction
     */
    protected $transaction;

    /**
     * @var \Webkul\Recurring\Model\Cron
     */
    protected $cron;

    /**
     * @var \Webkul\Recurring\Helper\Order
     */
    protected $orderHelper;
    
    /**
     * @var \Magento\Sales\Model\Service\InvoiceService
     */
    protected $invoiceService;
    
    /**
     * @var RecurringProductPlans
     */
    protected $plans;

    /**
     * @var RecurringTermsFactory
     */
    protected $term;
    
    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * @var \Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface
     */
    protected $transactionBuilder;

    /**
     * @var \Magento\Sales\Model\Order\Email\Sender\InvoiceSender
     */
    protected $invoiceSender;
    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;
    /**
     * @var OrderModel
     */
    protected $orderModel;

    /**
     * @var \Webkul\Recurring\Helper\Email
     */
    protected $emailHelper;

    /**
     * @var \Webkul\Recurring\Helper\Data
     */
    protected $dataHelper;

    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    protected $encryptor;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     *
     * @param \Webkul\Recurring\Logger\Logger $logger
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param OrderModel $orderModel
     * @param Date $date
     * @param Subscriptions $subscriptions
     * @param RecurringProductPlans $plans
     * @param RecurringTermsFactory $term
     * @param \Magento\Framework\HTTP\Client\Curl $curl
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Framework\Controller\ResultFactory $result
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Framework\Session\SessionManagerInterface $coreSession
     * @param \Magento\Sales\Model\Service\InvoiceService $invoiceService
     * @param \Magento\Framework\DB\Transaction $transaction
     * @param \Webkul\Recurring\Helper\Paypal $helper
     * @param \Webkul\Recurring\Helper\Order $orderHelper
     * @param \Webkul\Recurring\Model\Cron $cron
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param CheckoutCart $checkoutCart
     * @param QuoteRepository $quoteRepository
     * @param Transaction\BuilderInterface $transactionBuilder
     * @param InvoiceSender $invoiceSender
     * @param \Webkul\Recurring\Helper\Email $emailHelper
     * @param \Magento\Framework\Encryption\EncryptorInterface $encryptor
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Webkul\Recurring\Helper\Data $dataHelper = null
     */
    public function __construct(
        \Webkul\Recurring\Logger\Logger $logger,
        Context $context,
        PageFactory $resultPageFactory,
        OrderModel $orderModel,
        Date $date,
        Subscriptions $subscriptions,
        RecurringProductPlans $plans,
        Term $term,
        \Magento\Framework\HTTP\Client\Curl $curl,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\Controller\ResultFactory $result,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Session\SessionManagerInterface $coreSession,
        \Magento\Sales\Model\Service\InvoiceService $invoiceService,
        \Magento\Framework\DB\Transaction $transaction,
        \Webkul\Recurring\Helper\Paypal $helper,
        \Webkul\Recurring\Helper\Order $orderHelper,
        \Webkul\Recurring\Model\Cron $cron,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        CheckoutCart $checkoutCart,
        QuoteRepository $quoteRepository,
        Transaction\BuilderInterface $transactionBuilder,
        InvoiceSender $invoiceSender,
        \Webkul\Recurring\Helper\Email $emailHelper,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Webkul\Recurring\Helper\Data $dataHelper = null
    ) {
        $this->term                     = $term;
        $this->plans                    = $plans;
        $this->coreSession              = $coreSession;
        $this->cron                     = $cron;
        $this->orderHelper              = $orderHelper;
        $this->checkoutSession          = $checkoutSession;
        $this->curl                     = $curl;
        $this->urlBuilder               = $urlBuilder;
        $this->logger                   = $logger;
        $this->resultPageFactory        = $resultPageFactory;
        $this->date                     = $date;
        $this->subscriptions            = $subscriptions;
        $this->orderModel               = $orderModel;
        $this->transaction              = $transaction;
        $this->invoiceService           = $invoiceService;
        $this->resultRedirect           = $result;
        $this->helper                   = $helper;
        $this->checkoutCart             = $checkoutCart;
        $this->quoteRepository          = $quoteRepository;
        $this->jsonHelper               = $jsonHelper;
        $this->transactionBuilder       = $transactionBuilder;
        $this->invoiceSender            = $invoiceSender;
        $this->emailHelper              = $emailHelper;
        $this->encryptor                = $encryptor;
        $this->storeManager             = $storeManager;
        $this->dataHelper = $dataHelper ?: \Magento\Framework\App\ObjectManager::getInstance()
        ->create(\Webkul\Recurring\Helper\Data::class);
        parent::__construct($context);
    }

    /**
     * Express paypal url
     *
     * @param boolean $isSandBox
     * @return string
     */
    protected function getExpressUrl($isSandBox)
    {
        return "https://www.".(($isSandBox) ? "sandbox." : "")."paypal.com/cgi-bin/webscr?cmd=_express-checkout&token=";
    }

    /**
     * Express paypal Action
     *
     * @param boolean $isSandBox
     * @return string
     */
    protected function getActionUrl($isSandBox)
    {
        return "https://www.".(($isSandBox) ? "sandbox." : "")."paypal.com/cgi-bin/webscr";
    }

    /**
     * @inheritDoc
     */
    public function createCsrfValidationException(
        RequestInterface $request
    ): ?InvalidRequestException {
            return null;
    }

    /**
     * @inheritDoc
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }
}
