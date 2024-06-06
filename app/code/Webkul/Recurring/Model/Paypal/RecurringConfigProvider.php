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
namespace Webkul\Recurring\Model\Paypal;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Payment\Helper\Data as PaymentHelper;
use Webkul\Recurring\Model\Paypal\PaymentMethod;

class RecurringConfigProvider implements ConfigProviderInterface
{
    /**
     * @var string[]
     */
    protected $_methodCodes = [
        PaymentMethod::CODE
    ];

    /**
     * @var \Magento\Payment\Model\Method\AbstractMethod[]
     */
    protected $_methods = [];

    /**
     * @param PaymentHelper $paymentHelper
     */
    public function __construct(
        PaymentHelper $paymentHelper
    ) {
        foreach ($this->_methodCodes as $code) {
            $this->_methods[$code] = $paymentHelper->getMethodInstance($code);
        }
    }

    /**
     *  Get config
     */
    public function getConfig()
    {
        $config = [
            'payment' => [
                'recurringpaypal' => []
            ]
        ];
        foreach ($this->_methodCodes as $code) {
            if ($this->_methods[$code]->isAvailable()) {
                $config['payment']['recurringpaypal']['redirectUrl'][$code] =
                $this->getMethodRedirectUrl($code);
            }
        }
        return $config;
    }

    /**
     * Return redirect URL for method
     *
     * @param  string $code
     * @return mixed
     */
    protected function getMethodRedirectUrl($code)
    {
        return $this->_methods[$code]->getCheckoutRedirectUrl();
    }
}
