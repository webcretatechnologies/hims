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
namespace Webkul\Recurring\Model\Total\Invoice;

use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\Invoice\Total\AbstractTotal;

/**
 * Webkul_Recurring Model Initialfee
 */
class Initialfee extends AbstractTotal
{
    /**
     * Function collect
     *
     * @param Invoice $invoice
     * @return $this
     */
    public function collect(Invoice $invoice)
    {
        $invoice->setInitialFee(0);
        $invoice->setBaseInitialFee(0);
        $invoice->setGrandTotal((float)$invoice->getGrandTotal() + (float)$invoice->getInitialFee());
        $invoice->setBaseGrandTotal((float)$invoice->getBaseGrandTotal() + (float)$invoice->getInitialFee());
        return $this;
    }
}
