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
namespace Webkul\Recurring\Controller\Subscription;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\App\RequestInterface;
use Magento\Sales\Model\Order;

/**
 * Webkul Recurring Abstract Controller.
 */
abstract class SubscriptionAbstract extends Action
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;
    /**
     * @var \Magento\Customer\Model\Url
     */
    protected $customerUrl;
    /**
     * @var \Magento\Framework\Session\SessionManager
     */
    protected $sessionManager;
    /**
     * @var \Webkul\Recurring\Helper\Stripe
     */
    protected $stripeHelper;
    /**
     * @var \Webkul\Recurring\Model\RecurringSubscriptions
     */
    protected $subscription;
    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;
    /**
     * @var \Webkul\Recurring\Helper\Paypal
     */
    protected $paypalHelper;
    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var Order
     */
    protected $order;

     /**
      * @var \Webkul\Recurring\Helper\Email
      */
    protected $emailHelper;

    /**
     * @var \Webkul\Recurring\Logger\Logger
     */
    protected $logger;

    /**
     * @var \Webkul\Recurring\Helper\Data
     */
    protected $dataHelper;
        
    /**
     * Construct function
     *
     * @param Context $context
     * @param Session $customerSession
     * @param \Magento\Customer\Model\Url $customerUrl
     * @param PageFactory $resultPageFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Webkul\Recurring\Model\RecurringSubscriptions $subscription
     * @param \Webkul\Recurring\Helper\Paypal $paypalHelper
     * @param \Webkul\Recurring\Helper\Stripe $stripeHelper
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Framework\Session\SessionManager $sessionManager
     * @param \Webkul\Recurring\Helper\Email $emailHelper
     * @param Order $order
     * @param \Webkul\Recurring\Logger\Logger $logger
     * @param \Webkul\Recurring\Helper\Data $dataHelper = null
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        \Magento\Customer\Model\Url $customerUrl,
        PageFactory $resultPageFactory,
        \Magento\Framework\Registry $coreRegistry,
        \Webkul\Recurring\Model\RecurringSubscriptions $subscription,
        \Webkul\Recurring\Helper\Paypal $paypalHelper,
        \Webkul\Recurring\Helper\Stripe $stripeHelper,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\Session\SessionManager $sessionManager,
        \Webkul\Recurring\Helper\Email $emailHelper,
        Order $order,
        \Webkul\Recurring\Logger\Logger $logger,
        \Webkul\Recurring\Helper\Data $dataHelper = null
    ) {
        $this->customerSession      = $customerSession;
        $this->resultPageFactory    = $resultPageFactory;
        $this->customerUrl          = $customerUrl;
        $this->paypalHelper         = $paypalHelper;
        $this->stripeHelper         = $stripeHelper;
        $this->coreRegistry         = $coreRegistry;
        $this->subscription         = $subscription;
        $this->jsonHelper           = $jsonHelper;
        $this->sessionManager       = $sessionManager;
        $this->emailHelper          = $emailHelper;
        $this->order                = $order;
        $this->logger               = $logger;
        $this->dataHelper = $dataHelper ?: \Magento\Framework\App\ObjectManager::getInstance()
        ->create(\Webkul\Recurring\Helper\Data::class);
        parent::__construct($context);
    }

     /**
      * Check customer authentication.
      *
      * @param RequestInterface $request
      * @return \Magento\Framework\App\ResponseInterface
      */
    public function dispatch(RequestInterface $request)
    {
        $loginUrl = $this->customerUrl->getLoginUrl();

        if (!$this->customerSession->authenticate($loginUrl)) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
        }
        return parent::dispatch($request);
    }
}
