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

namespace Webkul\Recurring\Helper;

use Magento\Customer\Model\Session;

/**
 * Webkul Recurring Helper Email.
 */
class Email extends \Magento\Framework\App\Helper\AbstractHelper
{
    public const EMAIL_ON_SUBSCRIPTION_STATUS = 'recurring/email/email_on_subscription_status';
    public const EMAIL_SENDER_NAME = 'trans_email/ident_general/name';
    public const EMAIL_SENDER_MAIL = 'trans_email/ident_general/email';
    public const EMAIL_TEMPLATE_NEW_SUBSCRIPTION = 'recurring_email_new_subscription_template';
    public const EMAIL_TEMPLATE_CANCEL_SUBSCRIPTION = 'recurring_email_subscription_cancelled_template';
    public const EMAIL_TEMPLATE_FAILED_SUBSCRIPTION = 'recurring_email_failed_transaction_template';
    public const EMAIL_TEMPLATE_EXPIRY = 'recurring_email_subscription_expiry_template';
    public const NEW_SUBSCRIPTION_COPY_EMAIL_TO_ADMIN = 'recurring_email_copy_on_new_subscription';
    
    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    protected $_inlineTranslation;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $_transportBuilder;

    /**
     * @var string
     */
    protected $_template;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $_messageManager;

    /**
     * @var \Webkul\Recurring\Logger\Logger
     */
    protected $logger;

      /**
       * @var \Webkul\Recurring\Helper\Data
       */
    protected $helper;

    /**
     * Construct function
     *
     * @param Magento\Framework\App\Helper\Context              $context
     * @param Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
     * @param Magento\Framework\Mail\Template\TransportBuilder  $transportBuilder
     * @param \Magento\Framework\Message\ManagerInterface       $messageManager
     * @param Magento\Store\Model\StoreManagerInterface         $storeManager
     * @param \Webkul\Recurring\Logger\Logger                   $logger
     * @param \Webkul\Recurring\Helper\Data                     $helper
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Webkul\Recurring\Logger\Logger $logger,
        \Webkul\Recurring\Helper\Data $helper
    ) {
        parent::__construct($context);
        $this->_inlineTranslation = $inlineTranslation;
        $this->_transportBuilder = $transportBuilder;
        $this->_storeManager = $storeManager;
        $this->_messageManager = $messageManager;
        $this->logger = $logger;
        $this->helper = $helper;
    }

    /**
     * Return store configuration value.
     *
     * @param string $path
     * @param int    $storeId
     *
     * @return mixed
     */
    protected function getConfigValue($path, $storeId)
    {
        return $this->scopeConfig->getValue(
            $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Return store.
     *
     * @return Store
     */
    public function getStore()
    {
        return $this->_storeManager->getStore();
    }

    /**
     * Check email template enable by admin
     *
     * @param string $templateId
     * @return bool
     */
    protected function checkMailEnable($templateId)
    {
        $subscriptionEmails =
        $this->getConfigValue(self::EMAIL_ON_SUBSCRIPTION_STATUS, $this->getStore()->getStoreId());
        $templateIds = !empty($subscriptionEmails) ? explode(',', $subscriptionEmails) : [];
        if (in_array($templateId, $templateIds)) {
            return true;
        } else {
            $this->logger->info("checkMailEnable : email template not configured by admin.");
            return false;
        }
    }

    /**
     * Get sender info function
     *
     * @return array
     */
    protected function getSenderInfo()
    {
        $storeId = $this->getStore()->getStoreId();
        return [
            'name' => $this->getConfigValue(self::EMAIL_SENDER_NAME, $storeId),
            'email' => $this->getConfigValue(self::EMAIL_SENDER_MAIL, $storeId)
        ];
    }

    /**
     * [generateTemplate description].
     *
     * @param Mixed $emailTemplateVariables
     * @param Mixed $senderInfo
     * @param Mixed $receiverInfo
     */
    public function generateTemplate($emailTemplateVariables, $senderInfo, $receiverInfo)
    {
        $senderEmail = $senderInfo['email'];
        $template = $this->_transportBuilder->setTemplateIdentifier($this->_template)
            ->setTemplateOptions(
                [
                        'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                        'store' => $this->_storeManager->getStore()->getId(),
                    ]
            )
            ->setTemplateVars($emailTemplateVariables)
            ->setFrom($senderInfo)
            ->addTo($receiverInfo['email'], $receiverInfo['name'])
            ->setReplyTo($senderEmail, $senderInfo['name']);
        return $this;
    }

    /**
     * Send Subscription Cancel Email
     *
     * @param array $emailTemplateVariables
     * @param array $receiverInfo
     */
    public function sendSubscriptionCancelEmail($emailTemplateVariables, $receiverInfo)
    {
        $templateId = self::EMAIL_TEMPLATE_CANCEL_SUBSCRIPTION;
        $checkMailEnable = $this->checkMailEnable($templateId);
        if ($checkMailEnable) {
            $this->_template = $templateId;
            $this->_inlineTranslation->suspend();
            $senderInfo = $this->getSenderInfo();
            $this->generateTemplate($emailTemplateVariables, $senderInfo, $receiverInfo);
            try {
                $transport = $this->_transportBuilder->getTransport();
                $transport->sendMessage();
            } catch (\Exception $e) {
                $this->logger->info('sendSubscriptionCancelEmail: '.$e->getMessage());
                $this->_messageManager->addError(__('Something went wrong.'));
            }
            $this->_inlineTranslation->resume();
        }
    }

    /**
     * Send Subscription Cancel Email
     *
     * @param array $emailTemplateVariables
     * @param array $receiverInfo
     */
    public function sendNewSubscriptionEmail($emailTemplateVariables, $receiverInfo)
    {
        $templateId = self::EMAIL_TEMPLATE_NEW_SUBSCRIPTION;
        $checkMailEnable = $this->checkMailEnable($templateId);
        if ($checkMailEnable) {
            $this->_template = $templateId;
            $this->_inlineTranslation->suspend();
            $senderInfo = $this->getSenderInfo();
            $this->generateTemplate($emailTemplateVariables, $senderInfo, $receiverInfo);
            try {
                $transport = $this->_transportBuilder->getTransport();
                $transport->sendMessage();
            } catch (\Exception $e) {
                $this->logger->info('sendNewSubscriptionEmail: '.$e->getMessage());
                $this->_messageManager->addError(__('Something went wrong.'));
            }
            $this->_inlineTranslation->resume();
        }
    }

    /**
     * Send Subscription failed Email
     *
     * @param array $emailTemplateVariables
     * @param array $receiverInfo
     */
    public function sendSubscriptionFailedEmail($emailTemplateVariables, $receiverInfo)
    {
        $templateId = self::EMAIL_TEMPLATE_FAILED_SUBSCRIPTION;
        $checkMailEnable = $this->checkMailEnable($templateId);
        if ($checkMailEnable) {
            $this->_template = $templateId;
            $this->_inlineTranslation->suspend();
            $senderInfo = $this->getSenderInfo();
            $this->generateTemplate($emailTemplateVariables, $senderInfo, $receiverInfo);
            try {
                $transport = $this->_transportBuilder->getTransport();
                $transport->sendMessage();
            } catch (\Exception $e) {
                $this->logger->info('sendSubscriptionFailedEmail: '.$e->getMessage());
                $this->_messageManager->addError(__('Something went wrong.'));
            }
            $this->_inlineTranslation->resume();
        }
    }

    /**
     * Send upcoming payment Email
     *
     * @param array $emailTemplateVariables
     * @param array $receiverInfo
     */
    public function sendExpiryEmail($emailTemplateVariables, $receiverInfo)
    {
        $templateId = self::EMAIL_TEMPLATE_EXPIRY;
        $checkMailEnable = $this->checkMailEnable($templateId);
        if ($checkMailEnable) {
            $this->_template = $templateId;
            $this->_inlineTranslation->suspend();
            $senderInfo = $this->getSenderInfo();
            $this->generateTemplate($emailTemplateVariables, $senderInfo, $receiverInfo);
            try {
                $transport = $this->_transportBuilder->getTransport();
                $transport->sendMessage();
            } catch (\Exception $e) {
                $this->logger->info('sendExpiryEmail: '.$e->getMessage());
                $this->_messageManager->addError(__('Something went wrong.'));
            }
            $this->_inlineTranslation->resume();
        }
    }

    /**
     * Prepare and send copy email message
     *
     * @param array $emailTemplateVariables
     */
    public function sendCopyTo($emailTemplateVariables)
    {
        $copyTo = $this->helper->getAdminEmail();
        if (!empty($copyTo)) {
            $templateId = self::NEW_SUBSCRIPTION_COPY_EMAIL_TO_ADMIN;
            $this->_template = $templateId;
            $this->_inlineTranslation->suspend();
            $senderInfo = $this->getSenderInfo();
            $this->generateTemplate($emailTemplateVariables, $senderInfo, $copyTo);
            try {
                $transport = $this->_transportBuilder->getTransport();
                $transport->sendMessage();
            } catch (\Exception $e) {
                $this->logger->info('sendCopyTo: '.$e->getMessage());
                $this->_messageManager->addError(__('Something went wrong.'));
            }
            $this->_inlineTranslation->resume();
        }
    }
}
