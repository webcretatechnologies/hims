<?php
/**
 * Webkul Software.
 *
 * @category   Webkul
 * @package    Webkul_Recurring
 * @author     Webkul Software Private Limited
 * @copyright  Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\Recurring\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;

/**
 * Webkul MpStripe PreDispatchConfigSaveObserver Observer.
 */
class PreDispatchConfigSaveObserver implements ObserverInterface
{
    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;
    /**
     * @var \Magento\Framework\App\Config\Storage\WriterInterface
     */
    protected $configWriter;

    /**
     * @var \Webkul\Recurring\Logger\Logger
     */
    protected $logger;

    /**
     * @param ManagerInterface $messageManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\App\Config\Storage\WriterInterface $configWriter
     * @param \Webkul\Recurring\Logger\Logger $logger
     */
    public function __construct(
        ManagerInterface $messageManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\Config\Storage\WriterInterface $configWriter,
        \Webkul\Recurring\Logger\Logger $logger
    ) {
        $this->messageManager = $messageManager;
        $this->scopeConfig = $scopeConfig;
        $this->configWriter = $configWriter;
        $this->logger = $logger;
    }

    /**
     * Execute
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            $observerRequestData = $observer['request'];
            $params = $observerRequestData->getParams();
            
            if ($params['section'] == 'payment') {
                $currentDebugMode = isset($params['groups']['recurringstripe']['fields']['sandbox']['value'])
                ? $params['groups']['recurringstripe']['fields']['sandbox']['value'] : '';
                $previousDebugMode = $this->getConfig('payment/recurringstripe/sandbox');
                if (($previousDebugMode != '') && ($previousDebugMode != $currentDebugMode)) {
                    $webhookId = $this->getConfig('payment/recurringstripe/webhook_id');
                    if ($webhookId != '') {
                        $webhookEndpoint = \Stripe\WebhookEndpoint::retrieve(
                            $webhookId
                        );
                        $webhookEndpoint->delete();
                    }
                    $this->configWriter->save('payment/recurringstripe/webhook_id', '');
                }
            }
        } catch (\Exception $e) {
            $this->logger->info('PreDispatchConfigSaveObserver'.$e->getMessage());
            $this->messageManager->addErrorMessage(__('Something went wrong.'));
        }
    }

    /**
     * Get Config
     *
     * @param string $configPath
     */
    public function getConfig($configPath)
    {
        return $this->scopeConfig->getValue(
            $configPath,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}
