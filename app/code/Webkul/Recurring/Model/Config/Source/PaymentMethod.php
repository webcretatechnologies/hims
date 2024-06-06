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

namespace Webkul\Recurring\Model\Config\Source;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Payment\Model\Config;
use Magento\Framework\Option\ArrayInterface;

class PaymentMethod extends \Magento\Framework\DataObject implements ArrayInterface
{
    /**
     * @var ScopeConfigInterface
     */
    protected $_appConfigScopeConfigInterface;

    /**
     * @var Config
     */
    protected $_paymentModelConfig;

    /**
     * @param ScopeConfigInterface $appConfigScopeConfigInterface
     * @param Config               $paymentModelConfig
     */
    public function __construct(
        ScopeConfigInterface $appConfigScopeConfigInterface,
        Config $paymentModelConfig
    ) {

        $this->_appConfigScopeConfigInterface = $appConfigScopeConfigInterface;
        $this->_paymentModelConfig = $paymentModelConfig;
    }

    /**
     * Options getter.
     *
     * @return array
     */
    public function toOptionArray()
    {
        $payments = $this->_paymentModelConfig->getActiveMethods();
        $methods = [];
        foreach ($payments as $paymentCode => $paymentModel) {
            if ($paymentCode != \Webkul\Recurring\Model\Payment\RecurringOrder::PAYMENT_METHOD_BILLING_AGGREMENT &&
            $paymentCode != \Webkul\Recurring\Model\Payment\RecurringOrder::PAYMENT_METHOD_CASHONDELIVERY_CODE) {
                if ($paymentCode == \Webkul\Recurring\Model\Payment\RecurringOrder::PAYMENT_FREE_CODE) {
                    continue;
                }
                $paymentTitle = $this->_appConfigScopeConfigInterface
                    ->getValue('payment/'.$paymentCode.'/title');
                $methods[$paymentCode] = [
                    'label' => __($paymentTitle),
                    'value' => $paymentCode
                ];
            }
        }
        return $methods;
    }
}
