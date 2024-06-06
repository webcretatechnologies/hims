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
namespace Webkul\Recurring\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\Model\Session as BackendSession;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Sales\Model\Order;

abstract class AbstractRecurring extends Action
{
    public const ENABLE = true;
    public const DISABLE = false;

    /**
     * Authorization level of a basic admin session
     */
    public const ADMIN_RESOURCE = 'Webkul_Recurring::recurring';
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;
    /**
     * @var \Webkul\Recurring\Helper\Data
     */
    protected $helper;
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;
    /**
     * @var \Webkul\Recurring\Model\Subscription
     */
    protected $plans;
    /**
     * @var BackendSession
     */
    protected $backendSession;
    /**
     * @var \Webkul\Recurring\Helper\Stripe
     */
    protected $stripeHelper;
    /**
     * @var \Magento\Ui\Component\MassAction\Filter
     */
    protected $massFilter;
    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $product;
    /**
     * @var \Webkul\Recurring\Helper\Paypal
     */
    protected $paypalHelper;
    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;
    /**
     * @var \Webkul\Recurring\Model\RecurringTerms
     */
    protected $terms;
    /**
     * @var \Webkul\Recurring\Model\RecurringSubscriptions
     */
    protected $subscriptions;
    /**
     * @var FormKeyValidator
     */
    protected $formKeyValidator;
    
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
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param \Webkul\Recurring\Helper\Data $helper
     * @param \Webkul\Recurring\Helper\Paypal $paypalHelper
     * @param \Webkul\Recurring\Helper\Stripe $stripeHelper
     * @param \Magento\Framework\Registry $registry
     * @param BackendSession $backendSession
     * @param \Webkul\Recurring\Model\RecurringProductPlans $plans
     * @param \Webkul\Recurring\Model\RecurringTerms $terms
     * @param \Webkul\Recurring\Model\RecurringSubscriptions $subscriptions
     * @param \Magento\Catalog\Model\Product $product
     * @param FormKeyValidator $formKeyValidator
     * @param \Magento\Ui\Component\MassAction\Filter $massFilter
     * @param DataPersistorInterface $dataPersistor
     * @param Order $order
     * @param \Webkul\Recurring\Helper\Email $emailHelper
     * @param \Webkul\Recurring\Logger\Logger $logger
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Webkul\Recurring\Helper\Data $helper,
        \Webkul\Recurring\Helper\Paypal $paypalHelper,
        \Webkul\Recurring\Helper\Stripe $stripeHelper,
        \Magento\Framework\Registry $registry,
        BackendSession $backendSession,
        \Webkul\Recurring\Model\RecurringProductPlans $plans,
        \Webkul\Recurring\Model\RecurringTerms $terms,
        \Webkul\Recurring\Model\RecurringSubscriptions $subscriptions,
        \Magento\Catalog\Model\Product $product,
        FormKeyValidator $formKeyValidator,
        \Magento\Ui\Component\MassAction\Filter $massFilter,
        DataPersistorInterface $dataPersistor,
        Order $order,
        \Webkul\Recurring\Helper\Email $emailHelper,
        \Webkul\Recurring\Logger\Logger $logger
    ) {
        $this->paypalHelper       = $paypalHelper;
        $this->stripeHelper       = $stripeHelper;
        $this->helper             = $helper;
        $this->registry           = $registry;
        $this->plans              = $plans;
        $this->terms              = $terms;
        $this->subscriptions      = $subscriptions;
        $this->product            = $product;
        $this->backendSession     = $backendSession;
        $this->formKeyValidator   = $formKeyValidator;
        $this->massFilter         = $massFilter;
        $this->resultPageFactory  = $resultPageFactory;
        $this->dataPersistor = $dataPersistor;
        $this->order                = $order;
        $this->emailHelper          = $emailHelper;
        $this->logger               = $logger;
        parent::__construct($context);
    }

    /**
     * Set status
     *
     * @param \Webkul\Recurring\Model\RecurringTerms $model
     * @param boolean $status
     */
    public function setStatus($model, $status)
    {
        $model->setStatus($status)->setId($model->getId())->save();
    }
}
