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
namespace Webkul\Recurring\Block\Adminhtml\Sales\Order\Invoice;

class Totals extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Webkul\Recurring\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Sales\Model\Order\Invoice
     */
    protected $invoice = null;

    /**
     * @var \Magento\Framework\DataObject
     */
    protected $source;
    
    /**
     * Source Object
     *
     * @return \Magento\Sales\Model\Order\Invoice
     */
    public function getSource()
    {
        return $this->getParentBlock()->getSource();
    }

    /**
     * Get invoice
     *
     * @return \Magento\Sales\Model\Order\Invoice
     */
    public function getInvoice()
    {
        return $this->getParentBlock()->getInvoice();
    }

    /**
     * Initialize totals
     *
     * @return $this
     */
    public function initTotals()
    {
        $this->getParentBlock();
        $this->getInvoice();
        $this->getSource();
        if (!$this->getSource()->getOrder()->getInitialFee()) {
            return $this;
        }
        $invoiceItems = $this->getSource()->getItems()->getData();
        if ($invoiceItems) {
            $total = new \Magento\Framework\DataObject(
                [
                    'code' => 'initialfee',
                    'value' => $this->getSource()->getOrder()->getInitialFee(),
                    'label' => __("Initial Fee"),
                ]
            );
            $this->getParentBlock()->addTotalBefore($total, 'grand_total');
        }
        return $this;
    }
}
