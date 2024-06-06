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
namespace Webkul\Recurring\Model;

use Webkul\Recurring\Api\WebhookInterface;
use Magento\Framework\Json\Helper\Data as JsonHelper;

/**
 * Webkul Recurring Landing page Index Controller.
 */
class WebHook implements WebhookInterface
{
    /**
     * @var \Webkul\MpStripe\Helper\Data
     */
    private $helper;

    /**
     * @var \Magento\Framework\Filesystem\Driver\File
     */
    private $driver;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    private $jsonResultFactory;

    /**
     * @var Magento\Framework\Json\Helper\Data
     */
    private $jsonHelper;

    /**
     * @var array
     */
    private $_webhookEvent;

    /**
     * @param \Webkul\MpStripe\Helper\Data $helper
     * @param JsonHelper $jsonHelper
     * @param \Magento\Framework\Filesystem\Driver\File $driver
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonResultFactory
     * @param array $webhookEvent
     */
    public function __construct(
        \Webkul\Recurring\Helper\Data $helper,
        JsonHelper $jsonHelper,
        \Magento\Framework\Filesystem\Driver\File $driver,
        \Magento\Framework\Controller\Result\JsonFactory $jsonResultFactory,
        $webhookEvent = []
    ) {
        $this->helper = $helper;
        $this->driver = $driver;
        $this->jsonHelper = $jsonHelper;
        $this->jsonResultFactory = $jsonResultFactory;
        $this->_webhookEvent = $webhookEvent;
    }

    /**
     * Handle Webhook implementation
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function executeWebhook()
    {
        $data = $this->driver->fileGetContents('php://input');
        $stripeResponse = $this->jsonHelper->jsonDecode($data);
        $webhookType = $stripeResponse['type'];
        if ($webhookType && isset($this->_webhookEvent[$webhookType])) {
            try {
                $this->_webhookEvent[$webhookType]->process($stripeResponse);
            } catch (\Exception $e) {
                $this->helper->logDataInLogger('webhook trace '.$this->jsonHelper->jsonEncode($e->getTrace()));
            }
        }
        $result = $this->jsonResultFactory->create();
        
        $result->setHttpResponseCode(200);
        return $result;
    }
}
