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
namespace Webkul\Recurring\Model\Total;

class Initialfee extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal
{

    public const INITIAL_FEE = 'initial_fee';
    public const BASE_INITIAL_FEE = 'base_initial_fee';

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    private $jsonHelper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Webkul\Recurring\Helper\Data
     */
    private $helper;
    
    /**
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Webkul\Recurring\Helper\Data $helper
     */
    public function __construct(
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Webkul\Recurring\Helper\Data $helper
    ) {
        $this->jsonHelper = $jsonHelper;
        $this->storeManager = $storeManager;
        $this->helper = $helper;
    }

    /**
     * Collect
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return $this
     */
    public function collect(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        parent::collect($quote, $shippingAssignment, $total);
        $initialFee = '';
        $baseInitialFee = '';
        $cartData = $quote->getAllVisibleItems();
        $currentCurrencyCode = $this->storeManager->getStore()->getCurrentCurrency()->getCode();
        $currencySymbol = $this->helper->getCurrencySymbol();
        foreach ($cartData as $item) {
            if ($item->getOptionByCode('additional_options')) {
                $additionalOptions = $this->helper->
                jsonDecodeData($item->getOptionByCode('additional_options')->getValue());
            }
            if ($additionalOptionsQuote = $item->getOptionByCode('custom_additional_options')) {
                $allOptions = $this->helper->jsonDecodeData(
                    $additionalOptionsQuote->getValue()
                );
                foreach ($allOptions as $key => $option) {
                    if ($option['label'] == 'Base Initial Fee') {
                        $initialFee = $this->storeManager->getStore()->getBaseCurrency()
                        ->convert($option['value'], $currentCurrencyCode);
                        $additionalOptions['2']['value'] = $currencySymbol.$initialFee;
                        $item->getOptionByCode('additional_options')
                        ->setValue($this->helper->jsonEncodeData($additionalOptions));
                        $baseInitialFee = ((float)$baseInitialFee) + $option['value'];
                    }
                }
            }
        }

        if ($baseInitialFee != "") {
            $initialFee = $this->storeManager->getStore()->getBaseCurrency()
            ->convert($baseInitialFee, $currentCurrencyCode);
            $total->setData(static::INITIAL_FEE, $initialFee);
            $total->setData(static::BASE_INITIAL_FEE, $baseInitialFee);
            $total->setTotalAmount(static::INITIAL_FEE, $initialFee);
            $total->setBaseTotalAmount(static::BASE_INITIAL_FEE, $baseInitialFee);
        }
        return $this;
    }
    
    /**
     * Assign subtotal amount and label to address object
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param Address\Total $total
     * @return array|void
     */
    public function fetch(\Magento\Quote\Model\Quote $quote, \Magento\Quote\Model\Quote\Address\Total $total)
    {
        $initialFee = '';
        $baseInitialFee = '';
        $cartData = $quote->getAllVisibleItems();
        foreach ($cartData as $item) {
            if ($additionalOptionsQuote = $item->getOptionByCode('custom_additional_options')) {
                $allOptions = $this->jsonHelper->jsonDecode(
                    $additionalOptionsQuote->getValue()
                );
                foreach ($allOptions as $key => $option) {
                    if ($option['label'] == 'Base Initial Fee') {
                        $baseInitialFee = ((float)$baseInitialFee) + $option['value'];
                    }
                }
            }
        }

        $currentCurrencyCode = $this->storeManager->getStore()->getCurrentCurrency()->getCode();
        $initialFee = round($this->storeManager->getStore()->getBaseCurrency()
        ->convert($baseInitialFee, $currentCurrencyCode), 2);
        if (!empty($initialFee)) {
            return [
                'code' => self::INITIAL_FEE,
                'title' => $this->getLabel(),
                'label' => $this->getLabel(),
                'value' =>  $initialFee
            ];
        }
    }
 
    /**
     * Get Subtotal label
     *
     * @return \Magento\Framework\Phrase
     */
    public function getLabel()
    {
        return __('Initial Fee');
    }
}
