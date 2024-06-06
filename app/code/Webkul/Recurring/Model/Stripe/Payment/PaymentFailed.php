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

class PaymentFailed
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
     * Manage payment charge succeeded
     *
     * @param array $response
     */
    public function process($response)
    {
        if (isset($response["data"]["object"]["lines"]["data"][0]["price"]["nickname"])) {
            $incrementId = $response["data"]["object"]["lines"]["data"][0]["price"]["nickname"];
            $order = $this->stripeHelper->loadOrder($incrementId);
            if ($response["data"]["object"]['status'] == "paid") {
                $this->stripeHelper->declineOrder($order);
            }
        }
    }
}
