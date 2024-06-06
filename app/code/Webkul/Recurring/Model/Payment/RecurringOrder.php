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
namespace Webkul\Recurring\Model\Payment;

class RecurringOrder extends \Magento\Payment\Model\Method\AbstractMethod
{
    public const PAYMENT_METHOD_CASHONDELIVERY_CODE = "recurringorders";
    public const PAYMENT_METHOD_BILLING_AGGREMENT = "paypal_billing_agreement";
    public const PAYMENT_FREE_CODE = 'free';

    /**
     * Payment method code
     *
     * @var string
     */
    protected $_code = self::PAYMENT_METHOD_CASHONDELIVERY_CODE;

    /**
     * @var boolean
     */
    protected $_isOffline = true;

    /**
     * Returns config Instructions
     *
     * @return string
     */
    public function getInstructions()
    {
        return trim($this->getConfigData("instructions"));
    }
}
