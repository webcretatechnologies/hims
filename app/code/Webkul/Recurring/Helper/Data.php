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

use Magento\Catalog\Api\ProductRepositoryInterface;

/**
 * Webkul Recurring Helper Data
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Customer\Model\SessionFactory
     */
    protected $customerSession;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $product;

    /**
     * @var \Webkul\Recurring\Logger\Logger
     */
    private $logger;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    private $json;
    /**
     * @var \Webkul\Recurring\Model\Plans\DataProvider
     */
    private $dataProvider;
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Locale\CurrencyInterface $localeCurrency
     */
    protected $localeCurrency;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $cart;
    
    /**
     * @var ItemFactory
     */
    protected $itemFactory;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $serializer;

    /**
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Customer\Model\SessionFactory $customerSession
     * @param \Magento\Framework\Serialize\Serializer\Json $json
     * @param \Webkul\Recurring\Model\Plans\DataProvider $dataProvider
     * @param \Webkul\Recurring\Logger\Logger $logger
     * @param ProductRepositoryInterface $productRepository
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Locale\CurrencyInterface $localeCurrency
     * @param \Magento\Checkout\Model\Cart $cart
     * @param \Magento\Sales\Model\Order\ItemFactory $itemFactory
     * @param \Magento\Framework\Serialize\Serializer\Json $serializer
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Customer\Model\SessionFactory $customerSession,
        \Magento\Framework\Serialize\Serializer\Json $json,
        \Webkul\Recurring\Model\Plans\DataProvider $dataProvider,
        \Webkul\Recurring\Logger\Logger $logger,
        ProductRepositoryInterface $productRepository,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Locale\CurrencyInterface $localeCurrency,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Sales\Model\Order\ItemFactory $itemFactory,
        \Magento\Framework\Serialize\Serializer\Json $serializer
    ) {
        $this->dataProvider     = $dataProvider;
        $this->json       = $json;
        $this->customerSession  = $customerSession;
        $this->logger           = $logger;
        $this->productRepository = $productRepository;
        $this->storeManager = $storeManager;
        $this->localeCurrency = $localeCurrency;
        $this->cart = $cart;
        $this->itemFactory = $itemFactory;
        $this->serializer = $serializer;
        parent::__construct($context);
    }

    /**
     * Get cart data
     *
     * @return array
     */
    public function getCartData()
    {
        $additionalOptions = [];
        $quote = $this->cart->getQuote();
        if ($this->customerSession->create()->isLoggedIn()) {
            $cartData = $quote->getAllVisibleItems();
            foreach ($cartData as $item) {
                if ($customAdditionalOptionsQuote = $item->getOptionByCode('custom_additional_options')) {
                    $options = $this->json->unserialize(
                        $customAdditionalOptionsQuote->getValue()
                    );
                    $additionalOptions["itemQty"] =  $item->getQty();
                    foreach ($options as $option) {
                        $additionalOptions["termId"] =  0;
                        if ($option['label'] == 'Plan Id') {
                            $additionalOptions["planId"] =  $option['value'];
                        }
                        if ($option['label'] == 'Start Date') {
                            $additionalOptions["startDate"] = $option['value'];
                        }
                        if ($option['label'] == 'Base Initial Fee') {
                            $baseInitialFee =  $option['value'];
                        }
                        if ($option['label'] == 'Subscription Charge') {
                            $additionalOptions["subscriptionsCharge"] = $option['value'];
                        }
                    }
                }
            }
            $currentCurrencyCode = $this->storeManager->getStore()->getCurrentCurrency()->getCode();
            $additionalOptions["initialFee"] = round($this->storeManager->getStore()->getBaseCurrency()
            ->convert($baseInitialFee, $currentCurrencyCode), 2);
        }
        return $additionalOptions;
    }
    /**
     * This function will let us know the product supports subscription or not.
     *
     * @param integer $productId
     * @return array
     */
    public function getRecurring($productId)
    {
        $model = $this->productRepository->getById($productId);
        $returnArray = [];
        $returnArray ['subscription'] = $model->getData('subscription');
        return $returnArray;
    }

    /**
     * This function returns the configuration setting data array
     *
     * @return array
     */
    public function getConfigData()
    {
        $returnData = [];
        $returnData['enable'] = $this->getConfig('general_settings/enable');
        $returnData['description'] = $this->getConfig('subscription_labels/subscription_msg');
        return $returnData;
    }

    /**
     * Get Configuration setting values for allowed payment methods to buy subscription
     *
     * @return string
     */
    public function getAllowedPaymentMethods()
    {
        return $this->scopeConfig->getValue(
            'recurring/general_settings/allowedpaymentmethods',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * This function will return the every configuration field value.
     *
     * @param string $field
     * @return string
     */
    public function getConfig($field)
    {
        return  $this->scopeConfig->getValue(
            'recurring/'.$field,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * This function will return the all subscription and terms content from model
     *
     * @return array
     */
    public function getSubscriptionContent()
    {
        return $this->dataProvider->toArray();
    }

    /**
     * This function will return customer id if the customer is loggedin
     *
     * @return integer
     */
    public function getIsCustomerLoggedIn()
    {
        $customerData =  $this->customerSession->create();
        $groupId = $customerData->getcustomer_group_id();
        $customerId = $customerData->getcustomer_id();
        return ($customerId || $groupId);
    }

    /**
     * This function will write the data into the log file
     *
     * @param array|mixed $data
     */
    public function logDataInLogger($data)
    {
        $this->logger->info($data);
    }

    /**
     * Get current currency code
     *
     * @return string
     */
    public function getCurrencySymbol()
    {
        $currencyCode = $this->storeManager->getStore()->getCurrentCurrencyCode();
        return $this->localeCurrency->getCurrency($currencyCode)->getSymbol();
    }

    /**
     * Get cancellation reason
     *
     * @return array
     */
    public function getCancellationReason()
    {
        $reasons = $this->getConfig('cancellation_reasons/reasons');
        if (is_string($reasons) == 'string') {
            return $this->json->unserialize($reasons);
        }
        return $reasons;
    }

    /**
     * Get admin email
     *
     * @return string
     */
    public function getAdminEmail()
    {
        return $this->getConfig('email/email_copy_to');
    }

    /**
     * Allow customer to hold subscription
     *
     * @return string
     */
    public function canHoldTheSubscription()
    {
        return $this->getConfig('customer_control_settings/hold_subscription');
    }

    /**
     * Allow customer to cancel subscription
     *
     * @return string
     */
    public function canCancelTheSubscription()
    {
        return $this->getConfig('customer_control_settings/cancel_subscription');
    }

    /**
     * Allow customer to cancel subscription
     *
     * @return string
     */
    public function getReminderDay()
    {
        return $this->getConfig('email/reminder_day');
    }
    /**
     * Get Order Product Option Data Method.
     *
     * @param \Magento\Sales\Model\Order\Item $item
     * @param array $result
     *
     * @return array
     */
    public function getProductOptionData($item, $result = [])
    {
        try {
            $productOptionsData = $this->getProductOptions(
                $item->getProductOptions()
            );
            if ($options = $productOptionsData) {
                if (isset($options['options'])) {
                    $result = array_merge($result, $options['options']);
                }
                if (isset($options['additional_options'])) {
                    $result = array_merge($result, $options['additional_options']);
                }
                if (isset($options['attributes_info'])) {
                    $result = array_merge($result, $options['attributes_info']);
                }
            }
            return $result;
        } catch (\Exception $e) {
            $this->logDataInLogger('getProductOptionData '.$e->getMessage());
        }
    }

    /**
     * Get Order Product Name Html Data Method.
     *
     * @param array  $result
     *
     * @return string
     */
    public function getProductNameHtml($result)
    {
        $proOptionData = '';
        try {
            if ($_options = $result) {
                $proOptionData = '<div class="item-options">';
                foreach ($_options as $_option) {
                    $proOptionData .= '<p><span>'.$_option['label'].' : </span>';
    
                    $proOptionData .= '<span>'.$_option['value'];
                    $proOptionData .= '</span></p>';
                }
                $proOptionData .= '</div>';
            }
            return $proOptionData;
        } catch (\Exception $e) {
            $this->logDataInLogger('getProductNameHtml '.$e->getMessage());
        }
    }

    /**
     * Get config product item id
     *
     * @param int $orderId
     * @param int $itemId
     * @return \Webkul\Recurring\Helper\Item
     */
    public function getConfigProductItemId($orderId, $itemId)
    {
        $configurableSalesItem = $this->itemFactory->create()->getCollection()
                                ->addFieldToFilter('order_id', $orderId)
                                ->addFieldToFilter('parent_item_id', $itemId)
                                ->addFieldToSelect('product_id');
        foreach ($configurableSalesItem as $item) {
            return $item;
        }
    }

     /**
      * Get product options
      *
      * @param json|array $optionData
      * @return array
      */
    public function getProductOptions($optionData)
    {
        try {
            if ($optionData) {
                if (!is_array($optionData)) {
                    return $this->json->unserialize(
                        $optionData
                    );
                } else {
                    return $optionData;
                }
            } else {
                return $optionData;
            }
        } catch (\Exception $e) {
            return $this->json->unserialize(
                $optionData
            );
        }
    }

    /**
     * Get email template variables function
     *
     * @param array $order
     * @param array $orderItems
     * @return string
     */
    public function getEmailTemplateVar($order, $orderItems)
    {
        $orderInfo = '';
        try {
            foreach ($orderItems as $item) {
                $productName = $item->getName();
                $result = [];
                $result = $this->getProductOptionData($item, $result);
                $productOptionData = $this->getProductNameHtml($result);
                if ($item->getProductType() == 'configurable') {
                    $configurableSalesItem = $this->getConfigProductItemId($order->getId(), $item->getItemId());
                    $configurableItemArr = $configurableSalesItem->getProductId();
                    $configurableItemId = $item['product_id'];
                    if ($configurableItemArr) {
                        $configurableItemId = $configurableItemArr;
                    }
                    $product = $this->productRepository->getById($configurableItemId);
                } else {
                    $product = $this->productRepository->getById($item['product_id']);
                }
                $sku = $product->getSku();
                $orderInfo = $orderInfo."<tbody><tr>
                <td class='item-info' style='padding: 10px'>".$productName."</td>
                <td class='item-info' style='padding: 10px'>".$sku."</td>
                <td class='item-qty' style='padding: 10px'>".($item['qty_ordered'] * 1)."</td>
                </tr>
                <tr>
                <td class='item-info'>".$productOptionData."</td>
                </tr></tbody>";
            }
        } catch (\Exception $e) {
            $this->logDataInLogger('getEmailTemplateVar '.$e->getMessage());
        }
        return $orderInfo;
    }

    /**
     * This function will return json encoded data
     *
     * @param  array $data
     * @return string
     */
    public function jsonEncodeData($data)
    {
        return $this->serializer->serialize($data);
    }

    /**
     * This function will return json decode data
     *
     * @param  string $data
     * @return array
     */
    public function jsonDecodeData($data)
    {
        return $this->serializer->unserialize($data);
    }
}
