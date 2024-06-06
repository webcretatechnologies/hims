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

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Payment\Helper\Data as PaymentHelper;
use Webkul\Recurring\Model\Stripe\PaymentMethod;

class RecurringConfigProvider implements ConfigProviderInterface
{
    public const MEDIA_URL = 'Webkul_Recurring::images/wkstripe';
    public const DEFAULT_IMAGE = self::MEDIA_URL.'/config/stripe-logo.png';
    /**
     * @var string[]
     */
    protected $_methodCodes = [
        PaymentMethod::CODE
    ];
    /**
     * @var string[]
     */
    protected $_methodCode = PaymentMethod::CODE;
    /**
     * @var \Magento\Payment\Model\Method\AbstractMethod[]
     */
    protected $_methods = [];
    /**
     * @var \Webkul\Recurring\Helper\Stripe
     */
    protected $stripeHelper;
    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var \Magento\Framework\View\Element\Template
     */
    protected $template;
    /**
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
    protected $session;
    /**
     * @var \Magento\Framework\Filesystem\Driver\File
     */
    protected $fileDriver;
    /**
     * @var \Magento\Framework\View\Asset\Repository
     */
    protected $assetRepo;
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;
    /**
     *
     * @param PaymentHelper $paymentHelper
     * @param \Webkul\Recurring\Helper\Stripe $stripeHelper
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\View\Element\Template $template
     * @param \Magento\Framework\Session\SessionManagerInterface $session
     * @param \Magento\Framework\View\Asset\Repository $assetRepo
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\Filesystem\Driver\File $fileDriver
     */
    public function __construct(
        PaymentHelper $paymentHelper,
        \Webkul\Recurring\Helper\Stripe $stripeHelper,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\View\Element\Template $template,
        \Magento\Framework\Session\SessionManagerInterface $session,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Filesystem\Driver\File $fileDriver
    ) {
        $this->stripeHelper = $stripeHelper;
        $this->urlBuilder = $urlBuilder;
        $this->storeManager = $storeManager;
        $this->template = $template;
        $this->session = $session;
        $this->assetRepo = $assetRepo;
        $this->request = $request;
        $this->fileDriver = $fileDriver;
        foreach ($this->_methodCodes as $code) {
            $this->_methods[$code] = $paymentHelper->getMethodInstance($code);
        }
    }

    /**
     * Returns cofig data to payment renderer.
     *
     * @return array
     */
    public function getConfig()
    {
        
        if (!$this->stripeHelper->getIsActive()) {
            return [];
        }
        $mediaImageUrl = $this->template->getViewFileUrl(
            self::DEFAULT_IMAGE
        );
        $isFileExists = $this->fileDriver->isExists($mediaImageUrl);
        if (!$isFileExists) {
            $mediaImageUrl = "";
            $params = ['_secure' => $this->request->isSecure()];
            $mediaImageUrl =  $this->assetRepo->getUrlWithParams(
                self::DEFAULT_IMAGE,
                $params
            );
        }

        /**
         * $config array to pass config data to payment renderer component.
         *
         * @var array
         */
        
        $config = [
            'payment' => [
                'recurringstripe' => [
                    'title' => $this->stripeHelper->getConfigValue('title'),
                    'debug' => $this->stripeHelper->getConfigValue('debug'),
                    'api_secret_key' => $this->stripeHelper
                    ->getConfigValue(\Webkul\Recurring\Helper\Stripe::API_SECRET_KEY),
                    'api_publish_key' => $this->stripeHelper
                    ->getConfigValue(\Webkul\Recurring\Helper\Stripe::API_PUBLISH_KEY),
                    'image_on_form' => $mediaImageUrl,
                    'order_status' => $this->stripeHelper->getConfigValue('order_status'),
                    'payment_action' => $this->stripeHelper->getConfigValue('payment_action'),
                    'min_order_total' => $this->stripeHelper->getConfigValue('min_order_total'),
                    'max_order_total' => $this->stripeHelper->getConfigValue('max_order_total'),
                    'sort_order' => $this->stripeHelper->getConfigValue('sort_order'),
                    'method' => $this->_methodCode,
                    'currency' => $this->storeManager->getStore()->getCurrentCurrency()->getCode(),
                    'mediaUrl' => $this->template->getViewFileUrl(self::MEDIA_URL),
                    'locale' => $this->stripeHelper->getLocaleForStripe(),
                    'billingAddress' => (boolean)$this->stripeHelper->getConfigValue('billing_address'),
                    'shippingAddress' => (boolean)$this->stripeHelper->getConfigValue('shipping_address'),
                    "get_session_url" => $this->urlBuilder->getUrl("recurring/stripepayment/getsession")
                ],
            ],
        ];
        return $config;
    }
}
