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
namespace Webkul\Recurring\Controller\Adminhtml\System;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;

class GenerateWebHook extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $jsonResultFactory;
    /**
     * @var \Magento\Framework\App\Config\Storage\WriterInterface
     */
    protected $configWriter;
    /**
     * @var \Magento\Framework\Url
     */
    protected $urlHelper;
    /**
     * @var \Webkul\Recurring\Helper\Stripe
     */
    protected $stripeHelper;
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var string
     */
    protected $stripeClient;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonResultFactory
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Framework\Url $urlHelper
     * @param \Magento\Framework\App\Config\Storage\WriterInterface $configWriter
     * @param \Webkul\Recurring\Helper\Stripe $stripeHelper
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $jsonResultFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\Url $urlHelper,
        \Magento\Framework\App\Config\Storage\WriterInterface $configWriter,
        \Webkul\Recurring\Helper\Stripe $stripeHelper
    ) {
        $this->messageManager = $messageManager;
        $this->jsonResultFactory = $jsonResultFactory;
        $this->configWriter = $configWriter;
        $this->urlHelper = $urlHelper;
        $this->stripeHelper = $stripeHelper;
        parent::__construct($context);
    }
    
    /**
     *  To create webhooks on Stripe
     */
    public function execute()
    {
        $resultJson = $this->jsonResultFactory->create();
        $webHookId = $this->stripeHelper->getConfigValue('webhook_id');
        $secretKey = $this->stripeHelper->getConfigValue(\Webkul\Recurring\Helper\Stripe::API_SECRET_KEY);

        if (!$webHookId || !$secretKey) {
            $this->stripeClient = $this->stripeHelper->setStripeInstance();
            $webHookResponse = $this->stripeClient->webhookEndpoints->create([
                "url" => $this->urlHelper->getBaseUrl(
                    '',
                    [
                        '_nosid' => true,
                    ]
                ) . 'rest/V1/subscription/webhook',
                "enabled_events" => [
                    "checkout.session.completed",
                    "invoice.payment_succeeded",
                    "invoice.payment_failed",
                    "charge.dispute.closed"
                ]
            ]);

            if ($webHookResponse['id']) {
                $this->configWriter->save(
                    'payment/recurringstripe/webhook_id',
                    $webHookResponse['id'],
                    ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                );
                $response['error'] = 0;
                $message = __('WebHooks Generated Successfully');
                $this->messageManager->addSuccessMessage($message);
            } else {
                $response['error'] = 1;
                $message = __('Invalid Request Check Credentials');
                $this->messageManager->addErrorMessage($message);
            }
            
            return $resultJson->setData($response);
        } elseif ($webHookId) {
            $response['error'] = 1;
            $message = __('WebHooks Already Generated');
            $this->messageManager->addSuccessMessage($message);
            return $resultJson->setData($response);
        } else {
            $response['error'] = 1;
            $message = __('Invalid Request Check Credentials');
            $this->messageManager->addErrorMessage($message);
            return $resultJson->setData($response);
        }
    }
}
