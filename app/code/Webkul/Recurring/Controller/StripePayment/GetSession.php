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
namespace Webkul\Recurring\Controller\StripePayment;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Webkul\Recurring\Model\RecurringProductPlansFactory as RecurringProductPlans;
use Webkul\Recurring\Model\RecurringTermsFactory as Term;
use Magento\Quote\Model\QuoteRepository;

class GetSession extends Action
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $_jsonResultFactory;

    /**
     * @var RecurringProductPlans
     */
    protected $plans;

    /**
     * @var Term
     */
    protected $term;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    private $jsonHelper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;
    /**
     * @var \Webkul\Recurring\Helper\Data
     */
    protected $helper;
    /**
     * @var \Webkul\Recurring\Helper\Stripe
     */
    protected $stripeHelper;
    /**
     * @var \Magento\Checkout\Model\Type\Onepage
     */
    protected $onePage;
    /**
     * @var QuoteRepository
     */
    protected $quoteRepository;
    /**
     * @var string
     */
    protected $stripeClient;
    /**
     * Construct
     *
     * @param Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonResultFactory
     * @param \Magento\Checkout\Model\Type\Onepage $onePage
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Webkul\Recurring\Helper\Data $helper
     * @param \Webkul\Recurring\Helper\Stripe $stripeHelper
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param QuoteRepository $quoteRepository
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param RecurringProductPlans $plans
     * @param Term $term
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $jsonResultFactory,
        \Magento\Checkout\Model\Type\Onepage $onePage,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Webkul\Recurring\Helper\Data $helper,
        \Webkul\Recurring\Helper\Stripe $stripeHelper,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        QuoteRepository $quoteRepository,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        RecurringProductPlans $plans,
        Term $term
    ) {
        $this->_jsonResultFactory = $jsonResultFactory;
        $this->storeManager = $storeManager;
        $this->orderFactory = $orderFactory;
        $this->helper = $helper;
        $this->stripeHelper = $stripeHelper;
        $this->onePage = $onePage;
        $this->term = $term;
        $this->plans = $plans;
        $this->quoteRepository = $quoteRepository;
        $this->jsonHelper = $jsonHelper;
        parent::__construct($context);
    }

    /**
     * Execute
     */
    public function execute()
    {
        try {
            $this->helper->logDataInLogger("GetSession");
            $orderId = $this->onePage->getCheckout()->getLastOrderId();
            $resultJson = $this->_jsonResultFactory->create();
            $resultJson->setHeader('Cache-Control', 'max-age=0, must-revalidate, no-cache, no-store', true);
            $resultJson->setHeader('Pragma', 'no-cache', true);

            $this->stripeClient = $this->stripeHelper->setStripeInstance();
            
            $order = $this->orderFactory->create()->load($orderId);
            $smallCurrencyArray = [
                "bif", "clp", "djf", "gnf", "jpy", "kmf", "krw", "mga", "pyg", "rwf","vnd", "vuv", "xaf", "xof", "xpf"
            ];

            /** @var \Magento\Quote\Model\Quote  */
            $quote                   = $this->quoteRepository->get($order->getQuoteId());
            $cartData                = $quote->getAllVisibleItems();
            $planId                  = $interval = $intervalCount = "";
            $baseInitialFee          = 0.0;
            $endDate                 = '';
            $subscriptionProductName = '';
            $lineItems = [];
            $currencyStripe = strtolower($order->getStore()->getCurrentCurrencyCode());
            foreach ($cartData as $item) {
                if ($additionalOptionsQuote = $item->getOptionByCode('custom_additional_options')) {
                    $subscriptionProductName = $item->getName();
                    $allOptions = $this->jsonHelper->jsonDecode(
                        $additionalOptionsQuote->getValue()
                    );
                    foreach ($allOptions as $key => $option) {
                        if ($option['label'] == 'Plan Id') {
                            $planId = $option['value'];
                        }
                        if ($option['label'] == 'Base Initial Fee') {
                            $baseInitialFee =  $option['value'];
                        }
                    }

                    $currentCurrencyCode = $this->storeManager->getStore()->getCurrentCurrency()->getCode();
                    $initialFee = round($this->storeManager->getStore()->getBaseCurrency()
                    ->convert($baseInitialFee, $currentCurrencyCode), 2);
                    $itemPrice = $this->calculateItemPrice($item, $order);
                    $initialFee   = (in_array($currencyStripe, $smallCurrencyArray)) ?
                    round($initialFee) : $initialFee * 100;
                    $itemPrice   = (in_array($currencyStripe, $smallCurrencyArray)) ?
                    round($itemPrice) : $itemPrice * 100;
                    if ($planId) {
                        $result         = $this->getFrequency($planId);
                        if ($result['interval_count'] != 0) {
                            $intervalCount  = $result['interval_count'];
                            $interval       = strtolower($result['interval']);
                        }
                    }
                    
                    $product = $this->stripeClient->products->create(
                        [
                            "name" => $subscriptionProductName,
                            "type" => "service"
                        ]
                    );
                    $initialProduct = $this->stripeClient->products->create(
                        [
                            "name" => "InitialFee-".$subscriptionProductName
                        ]
                    );
                    $price = $this->stripeClient->prices->create(
                        [
                            'product'       => $product["id"],
                            'unit_amount'   => $itemPrice,
                            'currency'      => $currencyStripe,
                            'recurring'     => [
                                'interval'    => $interval,
                                'interval_count' => $intervalCount
                            ],
                            'nickname'      => $order->getIncrementId(),
                            'metadata'      => ['product_id' => $item->getProductId()]
                        ]
                    );
                    $initialPrice = $this->stripeClient->prices->create(
                        [
                            'product'       => $initialProduct["id"],
                            'unit_amount'   => $initialFee,
                            'currency'      => $currencyStripe,
                            'nickname'      => $order->getIncrementId(),
                        ]
                    );
                  
                    $lineItems[] = [
                        'price' => $price["id"],
                        'quantity' => 1,
                    ];
                    $lineItems[] = [
                        'price' => $initialPrice["id"],
                        'quantity' => 1,
                    ];
                }
            }
            
            $response = $this->stripeClient->checkout->sessions->create([
                "payment_method_types" => ["card"],
                "line_items" => $lineItems,
                'mode' => 'subscription',
                "success_url" => $this->storeManager->getStore()->getUrl('recurring/stripepayment/success'),
                "cancel_url" => $this->storeManager->getStore()->getUrl('recurring/stripepayment/failure'),
                "client_reference_id" => $order->getIncrementId(),
                "customer_email" => $order->getCustomerEmail(),
                'metadata' => ['order_id' => $order->getIncrementId()]
            ]);

            return $resultJson->setData($response);

        } catch (\Exception $e) {
            $this->helper->logDataInLogger("StripePayment_GetSession execute : ".$e->getMessage());
            return false;
        }
    }

    /**
     * This function return the duration of the plan
     *
     * @param integer $planId
     * @return integer
     */
    private function getFrequency($planId)
    {
        $typeId = $this->plans->create()->load($planId)->getType();
        $terms  = $this->term->create()->load($typeId);
        $result = ['interval' => $terms->getDurationType(), 'interval_count' => $terms->getDuration()];
        return $result;
    }

    /**
     * Calculate item subscription price
     *
     * @param \Magento\Quote\Model\Quote $item
     * @param \Magento\Sales\Model\OrderFactory $order
     * @return float
     */
    private function calculateItemPrice($item, $order)
    {
        $amount = 0;
        $amount = $item->getPriceInclTax() - $item->getDiscountAmount();
        $shippingAmount = $order->getShippingAmount() + $order->getShippingTaxAmount();
        $qty = $this->getOrderQty($order);
        $shippingAmount = $shippingAmount/$qty;
        $productType = $item->getProductType();
        if ($productType != \Magento\Catalog\Model\Product\Type::TYPE_VIRTUAL ||
        $productType != \Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE) {
            $amount += $shippingAmount;
        }
        return $amount;
    }

    /**
     * GetOrderQty
     *
     * @param \Magento\Sales\Model\OrderFactory $order
     */
    private function getOrderQty($order)
    {
        $qty = 0;
        foreach ($order->getAllVisibleItems() as $item) {
            $productType = $item->getProductType();
            if ($productType != \Magento\Catalog\Model\Product\Type::TYPE_VIRTUAL ||
            $productType != \Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE) {
                $qty += $item->getQtyOrdered();
            }
        }
        
        return $qty;
    }
}
