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

use Magento\Payment\Model\Method\AbstractMethod;
use Magento\Sales\Model\Order\Payment;
use Magento\Framework\App\RequestInterface;

class PaymentMethod extends AbstractMethod
{
    public const CODE = 'recurringpaypal';

    /**
     * @var \Webkul\Recurring\Helper\Data
     */
    private $helper;

    /**
     * @var string
     */
    protected $_code = self::CODE;

    /**
     * @var bool
     */
    protected $_isGateway = true;

    /**
     * Availability option.
     *
     * @var bool
     */
    protected $_isInitializeNeeded = true;

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
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlBuilder;

    /**
     * @var Transaction\BuilderInterface
     */
    protected $_transactionBuilder;

    /**
     * @var RequestInterface
     */
    protected $_request;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory
     * @param \Magento\Payment\Helper\Data $paymentData
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Payment\Model\Method\Logger $logger
     * @param \Webkul\Recurring\Helper\Data $helper
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param Transaction\BuilderInterface $transactionBuilder
     * @param RequestInterface $request
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
        \Webkul\Recurring\Helper\Data $helper,
        \Magento\Framework\UrlInterface $urlBuilder,
        Payment\Transaction\BuilderInterface $transactionBuilder,
        RequestInterface $request,
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
        $this->_urlBuilder = $urlBuilder;
        $this->_transactionBuilder = $transactionBuilder;
        $this->_request = $request;
        $this->helper = $helper;
    }

    /**
     * Checkout redirect URL getter for onepage checkout (hardcode).
     *
     * @see \Magento\Checkout\Controller\Onepage::savePaymentAction()
     * @see Magento\Quote\Model\Quote\Payment::getCheckoutRedirectUrl()
     *
     * @return string
     */
    public function getCheckoutRedirectUrl()
    {
        try {
            return $this->_urlBuilder->getUrl(
                'recurring/paypal/index'
            );
        } catch (\Exception $e) {
            $this->helper->logDataInLogger("Model_PaymentMethod getCheckoutRedirectUrl : ".$e->getMessage());
            return false;
        }
    }
}
