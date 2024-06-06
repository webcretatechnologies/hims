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
namespace Webkul\Recurring\Helper;

use Magento\Store\Model\App\Emulation;

/**
 * Webkul Recurring Helper Order
 */
class Order extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * This variable is set the store scope for order
     *
     * @var Magento\Store\Model\App\Emulation;
     */
    private $emulate;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    private $jsonHelper;
    /**
     * @var Magento\Catalog\Model\ProductFactory
     */
    private $productFactory;
    /**
     * @var Magento\Quote\Api\CartRepositoryInterface
     */
    private $cartRepositoryInterface;
    /**
     * @var Magento\Quote\Api\CartManagementInterface
     */
    private $cartManagementInterface;
    /**
     * @var Magento\Customer\Api\CustomerRepositoryInterface
     */
    private $customerRepository;
    /**
     * @var Magento\Sales\Model\Order
     */
    private $order;
    /**
     * @var \Webkul\Recurring\Logger\Logger
     */
    protected $logger;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param Emulation $emulate
     * @param Magento\Catalog\Model\ProductFactory $productFactory
     * @param Magento\Quote\Api\CartRepositoryInterface $cartRepositoryInterface
     * @param Magento\Quote\Api\CartManagementInterface $cartManagementInterface
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param Magento\Sales\Model\Order $order
     * @param \Webkul\Recurring\Logger\Logger $logger
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        Emulation $emulate,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Quote\Api\CartRepositoryInterface $cartRepositoryInterface,
        \Magento\Quote\Api\CartManagementInterface $cartManagementInterface,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Sales\Model\Order $order,
        \Webkul\Recurring\Logger\Logger $logger
    ) {
        $this->emulate                  = $emulate;
        $this->jsonHelper               = $jsonHelper;
        $this->productFactory           = $productFactory;
        $this->cartRepositoryInterface  = $cartRepositoryInterface;
        $this->cartManagementInterface  = $cartManagementInterface;
        $this->customerRepository       = $customerRepository;
        $this->order                    = $order;
        $this->logger = $logger;
        parent::__construct($context);
    }
    
    /**
     * Get product
     *
     * @param integer $productId
     * @return \Magento\Catalog\Model\Product
     */
    private function getProduct($productId)
    {
        return $this->productFactory->create()->load($productId);
    }
    
    /**
     * Create Order On Your Store
     *
     * @param \Magento\Sales\Model\Order $order
     * @param string $planName
     * @return array
     */
    public function createMageOrder($order, $planName)
    {
        try {
            $storeId = $order->getStoreId();
            $cartId = $this->cartManagementInterface->createEmptyCart(); //Create empty cart
            $quote = $this->cartRepositoryInterface->get($cartId); // load empty cart quote
           
            $quote->setStoreId($storeId);
            $environment  = $this->emulate->startEnvironmentEmulation($storeId);
    
            $customerId = $order->getCustomerId();
            
            $shippingAddress = ($order->getShippingAddress() && count($order->getShippingAddress()->getData())) ?
                                $order->getShippingAddress() :
                                $order->getBillingAddress();
            $billingAddress = $order->getBillingAddress();
            // if you have allready buyer id then you can load customer directly
            $customer   = $this->customerRepository->getById($customerId);
            // if you have allready buyer id then you can load customer directly
            
            $quote->setCurrency();
            $quote->assignCustomer($customer); //Assign quote to customer
    
            $additionalOptions [] = [
                'label' => __("Subscription"),
                'value' => $planName
            ];
            //add items in quote
            foreach ($order->getAllVisibleItems() as $item) {
                $product = $this->getProduct($item->getProductId());
                $product->setPrice($item->getPrice());
                $quote->addProduct($product, (int)($item->getQty()));
            }
    
            $cartData = $quote->getAllVisibleItems();
            foreach ($cartData as $item) {
                $item->addOption(
                    [
                        'product_id' => $item->getProductId(),
                        'code' => 'custom_additional_options',
                        'value' => $this->jsonHelper->jsonEncode($additionalOptions)
                    ]
                );
            }
            
            //Set Address to quote
            $quote->getBillingAddress()->addData($billingAddress->getData());
            $quote->getShippingAddress()->addData($shippingAddress->getData());
     
            // Collect Rates and Set Shipping & Payment Method
     
            $paymentMethod = $order->getPayment()->getMethodInstance()->getCode();
            $shippingAddress=$quote->getShippingAddress();
            $shippingAddress->setCollectShippingRates(true)
                            ->collectShippingRates()
                            ->setShippingMethod($order->getShippingMethod()); //shipping method
            $quote->setPaymentMethod($paymentMethod); //payment method
            $quote->setInventoryProcessed(false); //not effetc inventory
            $paymentMethodArray = [
                \Webkul\Recurring\Model\Stripe\PaymentMethod::CODE,
                \Webkul\Recurring\Model\Paypal\PaymentMethod::CODE
            ];
            if (in_array($paymentMethod, $paymentMethodArray)) {
                $paymentMethod = \Webkul\Recurring\Model\Payment\RecurringOrder::PAYMENT_METHOD_CASHONDELIVERY_CODE;
            }
            
            $quote->setIsRecurring(1);
            $quote->setCustomerIsGuest(0);
            // Set Sales Order Payment
            $quote->getPayment()->importData(['method' => $paymentMethod]);
            $quote->save(); //Now Save quote and your quote is ready
            
            // Collect Totals
            $quote->collectTotals();
            
            // Create Order From Quote
            $orderId = $this->cartManagementInterface->placeOrder($quote->getId());
            $order = $this->order->load($orderId);
            
            $order->setEmailSent(0);
            
            if ($order->getEntityId()) {
                $result =   [
                    'error' => 0,
                    'order_id' => $order->getRealOrderId(),
                    'id' => $order->getId()
                ];
            } else {
                $result =   [
                    'error' => 1,
                    'msg' => __('Something happened while creating your order.')
                ];
            }
            $this->emulate->stopEnvironmentEmulation($environment);
        } catch (\Exception $e) {
            $this->logger->info('createMageOrder ==>'.$e->getMessage());
        }
        return $result;
    }
}
