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
 namespace Webkul\Recurring\Block;

 use Magento\Framework\View\Element\Template;
 use Magento\Framework\View\Element\Template\Context;
 use Magento\Framework\UrlInterface;
 use \Magento\Framework\Pricing\Helper\Data as FormatPrice;
 use Magento\Sales\Model\Order\Address\Renderer as AddressRenderer;
 use Magento\Sales\Model\Order\Address;
 use \Webkul\Recurring\Model\RecurringTerms;
 
class Manage extends Template
{
    public const REQUEST_KEY = "id";
    /**
     * @var \Webkul\Recurring\Model\RecurringSubscriptions
     */
    private $subscription;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $session;

    /**
     * @var \Magento\Sales\Model\Order
     */
    private $orderModel;

    /**
     * @var \Webkul\Recurring\Model\RecurringProductPlansFactory
     */
    private $planFactory;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    private $customerFactory;

    /**
     * @var \Webkul\Recurring\Model\RecurringSubscriptionsMapping
     */
    private $mapping;
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    private $productFactory;

    /**
     * @var FormatPrice
     */
    private $priceHelper;

     /**
      * @var AddressRenderer
      */
    protected $addressRenderer;

     /**
      * @var \Magento\Framework\App\ResourceConnection
      */
    public $_resource;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $json;

    /**
     * @var RecurringTerms
     */
    protected $recurringTerms;
    
    /**
     * Constructor function
     *
     * @param Context $context
     * @param \Magento\Customer\Model\Session $session
     * @param \Webkul\Recurring\Model\RecurringSubscriptions $subscription
     * @param UrlInterface $urlBuilder
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Sales\Model\Order $orderModel
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Webkul\Recurring\Model\RecurringSubscriptionsMapping $mapping
     * @param \Webkul\Recurring\Model\RecurringProductPlansFactory $planFactory
     * @param FormatPrice $priceHelper
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Framework\Serialize\Serializer\Json $json
     * @param AddressRenderer $addressRenderer
     * @param RecurringTerms $recurringTerms
     * @param array $data
     */
    public function __construct(
        Context $context,
        \Magento\Customer\Model\Session $session,
        \Webkul\Recurring\Model\RecurringSubscriptions $subscription,
        UrlInterface $urlBuilder,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Sales\Model\Order $orderModel,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Webkul\Recurring\Model\RecurringSubscriptionsMapping $mapping,
        \Webkul\Recurring\Model\RecurringProductPlansFactory $planFactory,
        FormatPrice $priceHelper,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Framework\Serialize\Serializer\Json $json,
        AddressRenderer $addressRenderer,
        RecurringTerms $recurringTerms,
        array $data = []
    ) {
        $this->mapping            = $mapping;
        $this->customerFactory    = $customerFactory;
        $this->planFactory        =  $planFactory;
        $this->productFactory     = $productFactory;
        $this->subscription       = $subscription;
        $this->urlBuilder         = $urlBuilder;
        $this->session            = $session;
        $this->orderModel         = $orderModel;
        $this->priceHelper     = $priceHelper;
        $this->_resource = $resource;
        $this->json               = $json;
        $this->addressRenderer = $addressRenderer;
        $this->recurringTerms        =  $recurringTerms;
        parent::__construct($context, $data);
        $this->setCollection($this->getGridCollection());
    }

    /**
     * Get Customer
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
     * Subscription Grid Collection
     *
     * @return \Webkul\Recurring\Model\RecurringSubscriptions
     */
    public function getGridCollection()
    {
        $collection = $this->subscription->getCollection();
        $collection->addFieldToFilter(
            "customer_id",
            ['eq' => $this->session->getCustomer()->getId()]
        );
        $collection->setOrder("entity_id", "DESC");
        return $collection;
    }
    
    /**
     * Subscription Mapping Grid Collection
     *
     * @return \Webkul\Recurring\Model\RecurringSubscriptionsMapping
     */
    public function getGridChildCollection()
    {
        $subscriptionTable = $this->_resource->getTableName("recurring_subscriptions");

        $collection = $this->mapping->getCollection()->addFieldToFilter(
            'subscription_id',
            $this->getRequest()->getParam(self::REQUEST_KEY)
        );
        $collection
        ->getSelect()
        ->joinLeft(
            ['sv'=>$subscriptionTable],
            "main_table.subscription_id = sv.entity_id",
            [
                'ref_profile_id' => 'sv.ref_profile_id',
                'plan_id' => 'sv.plan_id',
                'status' => 'sv.status'
            ]
        );
        return $collection;
    }

    /**
     * Get row url
     *
     * @param integer $id
     * @return string
     */
    public function getRowUrl($id)
    {
        return $this->getUrl(
            "recurring/subscription/view",
            [self::REQUEST_KEY => $id]
        );
    }
    
    /**
     * Back url
     *
     * @return string
     */
    public function getBackUrl()
    {
         return $this->getUrl("recurring/subscription/manage");
    }

    /**
     * Unsubscribe url
     *
     * @return string
     */
    public function getUnsubscribeUrl()
    {
        $id = $this->getRequest()->getParam(self::REQUEST_KEY);
        return $this->getUrl(
            "recurring/subscription/unsubscribe",
            [self::REQUEST_KEY => $id]
        );
    }

    /**
     * Hold subscribe url
     *
     * @return string
     */
    public function getHoldSubscribeUrl()
    {
        $id = $this->getRequest()->getParam(self::REQUEST_KEY);
        return $this->getUrl(
            "recurring/subscription/unsubscribe",
            [self::REQUEST_KEY => $id, 'type' => 'hold']
        );
    }

    /**
     * Resume subscribe url
     *
     * @return string
     */
    public function getResumeSubscribeUrl()
    {
        $id = $this->getRequest()->getParam(self::REQUEST_KEY);
        return $this->getUrl(
            "recurring/subscription/unsubscribe",
            [self::REQUEST_KEY => $id, 'type' => 'resume']
        );
    }

    /**
     * Plan type name
     *
     * @param integer $planId
     * @return string
     */
    public function getTypeName($planId)
    {
        $model = $this->recurringTerms->load($planId);
        return $model->getTitle();
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
     * Product name
     *
     * @param integer $productId
     * @return string
     */
    public function getProductName($productId)
    {
        return $this->productFactory->create()->load($productId)->getName();
    }

    /**
     * Get Order
     *
     * @param integer $orderId
     * @return array
     */
    public function getOrder($orderId)
    {
        return $this->orderModel->load($orderId);
    }

    /**
     * Returns string with formatted address.
     *
     * @param Address $address
     *
     * @return null|string
     */
    public function getFormattedAddress(Address $address)
    {
        return $this->addressRenderer->format($address, 'html');
    }

    /**
     * Order Id
     *
     * @param integer $orderId
     * @return string
     */
    public function getOrderId($orderId)
    {
        return $this->orderModel->load($orderId)->getIncrementId();
    }

    /**
     * Subscription Order increment id
     *
     * @return string
     */
    public function getWKOrderId()
    {
        return '#'.$this->getOrderId($this->getSubscriptions()->getOrderId());
    }

    /**
     * Prepare pager for rate list
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        return $this;
    }
    
    /**
     * Get edit page status
     *
     * @return boolean
     */
    public function isEditPage()
    {
        return $this->getRequest()->getParam(self::REQUEST_KEY) ? true : false;
    }

    /**
     * Form data
     */
    public function getFormData()
    {
        $rows = [
        [
            "value" => "",
            "input" => "text",
            "options" => [],
            "name" => "name",
            "index" => "name",
            "class" => "required-entry input-text",
            'label' => __('Rule Name'),
            "isRequired" => 'required'
        ],
        [
            "value" => "",
            "input" => "text",
            "options" => [],
            "name" => "description",
            "index" => "description",
            "class" => "required-entry input-text",
            "label" => __('Description'),
            "isRequired" => 'required',
            "notice" => __('Description ..')
        ]
        ];
    }

    /**
     * Get pager html
     *
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * Subscription row
     *
     * @return Webkul\Recurring\Model\RecurringSubscriptions
     */
    public function getSubscriptions()
    {
        $model = $this->subscription->load(
            $this->getRequest()->getParam(self::REQUEST_KEY)
        );
        return $model;
    }

    /**
     * Subscription type
     *
     * @return string
     */
    public function getRecurringProductPlans()
    {
        return $this->recurringTerms->load(
            $this->getSubscriptions()->getPlanId()
        )->getTitle();
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
     * Order Url
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
     * Customer Url
     *
     * @return string
     */
    public function getCustomerUrl()
    {
        return $this->urlBuilder->getUrl(
            'customer/account/*'
        );
    }

    /**
     * Get Product
     *
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct()
    {
        return $this->productFactory->create()
        ->load($this->getSubscriptions()->getProductId());
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
    /**
     * Get Subscription charge
     *
     * @return \Magento\Framework\Pricing\Helper\Data
     */
    public function getSubscriptionCharge()
    {
        $orderId = $this->getSubscriptions()->getOrderId();
        $order = $this->getOrder($orderId);
        $subscriptionCharge = $order->getBaseSubTotal();
        return $this->priceHelper->currency($subscriptionCharge);
    }

    /**
     * Get Subscription charge
     *
     * @return \Magento\Framework\Pricing\Helper\Data
     */
    public function getInitialFee()
    {
        $extra = $this->json->unserialize($this->getSubscriptions()->getExtra());
        $initialFee =  $extra['initial_fee'];
        return $this->priceHelper->currency($initialFee);
    }

    /**
     * Get formatted date
     *
     * @param string $date
     * @return string
     */
    public function getFormattedDate($date)
    {
        return $this->formatDate(
            $date,
            \IntlDateFormatter::FULL,
            false
        );
    }
}
