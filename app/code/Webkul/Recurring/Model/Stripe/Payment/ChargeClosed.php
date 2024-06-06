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

namespace Webkul\Recurring\Model\Stripe\Payment;

class ChargeClosed
{
    /**
     * @var \Webkul\Recurring\Helper\Stripe
     */
    protected $stripeHelper;
    
    /**
     * Constructor
     *
     * @param \Webkul\Recurring\Helper\Stripe $stripeHelper
     */
    public function __construct(
        \Webkul\Recurring\Helper\Stripe $stripeHelper
    ) {
        $this->stripeHelper = $stripeHelper;
    }

    /**
     * Manage charge closes
     *
     * @param array $res
     */
    public function process($res)
    {
        $this->stripeHelper->chargeClosed($res);
    }
}
