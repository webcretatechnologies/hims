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
namespace Webkul\Recurring\Model\Stripe;

use Magento\Payment\Model\Method\AbstractMethod;
use Magento\Framework\Exception\LocalizedException;

class PaymentMethod extends AbstractMethod
{
    public const CODE = 'recurringstripe';

    /**
     * @var bool
     */
    protected $_isGateway = true;
    /**
     * Availability option.
     *
     * @var bool
     */
    protected $_isInitializeNeeded = false;
    /**
     * @var bool
     */
    protected $_canAuthorize = true;
    /**
     * @var bool
     */
    protected $_canCapture = true;
    /**
     * @var string
     */
    protected $_code = self::CODE;
    /**
     * Availability option.
     *
     * @var bool
     */
    protected $_canRefund = true;
    /**
     * Availability option.
     *
     * @var bool
     */
    protected $_canRefundInvoicePartial = false;
    /**
     * Availability option.
     *
     * @var bool
     */
    protected $_canUseInternal = false;
    /**
     * @var \Magento\Framework\Session\SessionManager
     */
    protected $coreSession;
    
    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory
     * @param \Magento\Payment\Helper\Data $paymentData
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Payment\Model\Method\Logger $logger
     * @param \Webkul\Recurring\Helper\Stripe $stripeHelper
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        \Webkul\Recurring\Helper\Stripe $stripeHelper,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * Authorizes specified amount.
     *
     * @param InfoInterface $payment
     * @param float         $amount
     *
     * @return $this
     *
     * @throws LocalizedException
     */
    public function authorize(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        return $this;
    }

    /**
     * Captures specified amount.
     *
     * @param InfoInterface $payment
     * @param float $amount
     *
     * @return $this
     */
    public function capture(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        $order = $payment->getOrder();
        $amount  = $order->getGrandTotal();
        parent::capture($payment, $amount);
        return $this;
    }

    /**
     * Do not validate payment form using server methods.
     *
     * @return bool
     */
    public function validate()
    {
        return true;
    }

    /**
     * Assign corresponding data.
     *
     * @param \Magento\Framework\DataObject|mixed $data
     *
     * @return $this
     *
     * @throws LocalizedException
     */
    public function assignData(\Magento\Framework\DataObject $data)
    {
        parent::assignData($data);
        return $this;
    }

    /**
     * Define if debugging is enabled.
     *
     * @return bool
     *
     * @api
     */
    public function getDebugFlag()
    {
        if ($this->getConfigData('sandbox')) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * GetCardData get the unique fingureprint from the card object
     *
     * @param String $token
     * @return String
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCardData($token = null)
    {
        try {
            $tokenData = \Stripe\Token::retrieve($token);
            return $tokenData;
        } catch (\Exception $e) {
            throw new LocalizedException(
                __(
                    'There was an error capturing the transaction: %1',
                    $e->getMessage()
                )
            );
        }
    }

    /**
     * Get config payment action url
     *
     * @return string
     * @api
     */
    public function getConfigPaymentAction()
    {
        $sType = $this->getInfoInstance()->getAdditionalInformation('stype');
        if ($sType == 'bitcoin' && $this->getConfigData('payment_action') == 'authorize') {
            return self::ACTION_AUTHORIZE_CAPTURE;
        } else {
            return $this->getConfigData('payment_action');
        }
    }
}
