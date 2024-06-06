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
namespace Webkul\Recurring\Block\Adminhtml\Sales;

/**
 * Webkul_Recurring Block Totals
 */
class Totals extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Directory\Model\CurrencyFactory
     */
    protected $currencyFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->currencyFactory = $currencyFactory;
        $this->storeManager = $storeManager;
    }

    /**
     * Get order
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->getParentBlock()->getOrder();
    }
    
    /**
     * Source Object
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getSource()
    {
        return $this->getParentBlock()->getSource();
    }

    /**
     * Initialize total
     *
     * @return $this
     */
    public function initTotals()
    {
        $this->getParentBlock();
        $this->getOrder();
        $this->getSource();
        $currencyCodeTo = $this->getSource()->getOrderCurrencyCode();
        $currencyCodeFrom = $this->getSource()->getBaseCurrencyCode();
        $rate = $this->currencyFactory->create()->load($currencyCodeTo)->getAnyRate($currencyCodeFrom);
        $baseInitialFee = $rate * (float)$this->getSource()->getInitialFee();
        if (!$this->getSource()->getInitialFee()) {
            return $this;
        }
        $total = new \Magento\Framework\DataObject(
            [
                'code' => 'initail_fee',
                'value' => $this->getSource()->getInitialFee(),
                'base_value' => $baseInitialFee,
                'label' => __("Initial Fee"),
            ]
        );
        $this->getParentBlock()->addTotalBefore($total, 'grand_total');
        return $this;
    }
}
