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
namespace Webkul\Recurring\Block\Adminhtml\Subscriptions\Tab\View;

use Magento\Framework\UrlInterface;
use Magento\Sales\Model\Order;
use \Magento\Framework\Pricing\Helper\Data as FormatPrice;
use \Webkul\Recurring\Model\RecurringTerms;

/**
 * Adminhtml customer view personal information sales block.
 */
class SubscriptionsInfo extends \Magento\Backend\Block\Template
{
    /**
     * @var Magento\Sales\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var Order
     */
    protected $order;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var \Webkul\Recurring\Model\RecurringProductPlansFactory
     */
    protected $planFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;
    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var FormatPrice
     */
    private $priceHelper;

    /**
     * @var \Magento\Sales\Model\Order\Address\Renderer
     */
    private $addressRenderer;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $json;

    /**
     * @var RecurringTerms
     */
    protected $recurringTerms;

    /**
     * @var RecurringTerms
     */
    protected $session;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param UrlInterface $urlBuilder
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Webkul\Recurring\Model\RecurringProductPlansFactory $planFactory
     * @param Order $order
     * @param FormatPrice $priceHelper
     * @param \Magento\Sales\Model\Order\Address\Renderer $addressRenderer
     * @param \Magento\Framework\Serialize\Serializer\Json $json
     * @param \Magento\Customer\Model\Session $session
     * @param \Magento\Framework\App\Request\Http $request
     * @param RecurringTerms $recurringTerms
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        UrlInterface $urlBuilder,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Webkul\Recurring\Model\RecurringProductPlansFactory $planFactory,
        Order $order,
        FormatPrice $priceHelper,
        \Magento\Sales\Model\Order\Address\Renderer $addressRenderer,
        \Magento\Framework\Serialize\Serializer\Json $json,
        \Magento\Customer\Model\Session $session,
        \Magento\Framework\App\Request\Http $request,
        RecurringTerms $recurringTerms,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->order =  $order;
        $this->planFactory =  $planFactory;
        $this->productFactory = $productFactory;
        $this->customerFactory = $customerFactory;
        $this->coreRegistry = $registry;
        $this->urlBuilder = $urlBuilder;
        $this->priceHelper     = $priceHelper;
        $this->addressRenderer  = $addressRenderer;
        $this->json = $json;
        $this->session = $session;
        $this->request = $request;
        $this->recurringTerms =  $recurringTerms;
    }

    /**
     * Get subscription started date
     *
     * @return string
     */
    public function getStartDate()
    {
        return $this->formatDate(
            $this->getSubscriptions()->getStartDate(),
            \IntlDateFormatter::FULL,
            false
        );
    }

    /**
     * Get subscription data
     *
     * @return \Magento\Framework\Registry
     */
    public function getSubscriptions()
    {
        $subscriptionId = $this->request->getParam('id');
        $this->session->setSubscriptionId($subscriptionId);
        return $this->coreRegistry->registry('subscriptions_data');
    }
    
    /**
     * Get order
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->order->load(
            $this->getSubscriptions()->getOrderId()
        );
    }

    /**
     * Get Formatted price
     *
     * @param int $price
     * @return string
     */
    public function getFormattedPrice($price)
    {
        return $this->priceHelper->currency($price);
    }

    /**
     * Get Formatted address
     *
     * @param array $address
     * @return string
     */
    public function getFormattedAddress($address)
    {
        return $this->addressRenderer->format($address, 'html');
    }

    /**
     * Get order url
     *
     * @return string
     */
    public function getOrderUrl()
    {
        return $this->urlBuilder->getUrl(
            'sales/order/view',
            ['order_id' => $this->getSubscriptions()->getOrderId()]
        );
    }

    /**
     * Get order id
     *
     * @return string
     */
    public function getOrderId()
    {
        return '#'.$this->order->load(
            $this->getSubscriptions()->getOrderId()
        )->getIncrementId();
    }

    /**
     * Get Product
     *
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct()
    {
        return $this->productFactory->create()->load(
            $this->getSubscriptions()->getProductId()
        );
    }

    /**
     * Get customer
     *
     * @return \Magento\Customer\Model\Customer
     */
    public function getCustomer()
    {
        return $this->customerFactory->create()->load(
            $this->getSubscriptions()->getCustomerId()
        );
    }

    /**
     * Get customer edit url
     *
     * @param int $id
     * @return string
     */
    public function getCustomerUrl($id)
    {
        return $this->urlBuilder->getUrl(
            'customer/index/edit',
            ['id' => $id]
        );
    }

    /**
     * Get intial fee of Subscription
     *
     * @return \Webkul\Recurring\Model\RecurringProductPlans
     */
    public function getRecurringProductPlanInitialFee()
    {
        $extra = $this->json->unserialize($this->getSubscriptions()->getExtra());
        return $extra['initial_fee'];
    }

    /**
     * Get name of Subscription
     *
     * @return \Webkul\Recurring\Model\RecurringProductPlans
     */
    public function getRecurringProductPlans()
    {
        return $this->recurringTerms->load(
            $this->getSubscriptions()->getPlanId()
        )->getTitle();
    }

    /**
     * Get Subscription charge
     *
     * @return \Magento\Framework\Pricing\Helper\Data
     */
    public function getSubscriptionCharge()
    {
        $order = $this->getOrder();
        $subscriptionCharge = $order->getBaseSubTotal();
        return $this->priceHelper->currency($subscriptionCharge);
    }

    /**
     * Get subscription creation date
     *
     * @return string
     */
    public function getCreateDate()
    {
        return $this->formatDate(
            $this->getSubscriptions()->getCreatedAt(),
            \IntlDateFormatter::FULL,
            false
        );
    }

    /**
     * Get Status
     *
     * @param boolean $status
     * @return string
     */
    public function getStatus($status)
    {
        if ($status == 1) {
            return __("Subscribed");
        }
        return __("UnSubscribed");
    }

    /**
     * Get subscription creation date
     *
     * @return string
     */
    public function getValidTill()
    {
        return $this->formatDate(
            $this->getSubscriptions()->getValidTill(),
            \IntlDateFormatter::FULL,
            false
        );
    }
}
