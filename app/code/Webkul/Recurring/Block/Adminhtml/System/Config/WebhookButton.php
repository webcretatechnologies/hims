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
namespace Webkul\Recurring\Block\Adminhtml\System\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;

class WebhookButton extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @var string
     */
    protected $_template = 'Webkul_Recurring::system/config/webhookbutton.phtml';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Constructor function
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param ScopeConfigInterface $scopeConfig
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        ScopeConfigInterface $scopeConfig,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Render function
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    /**
     * Get element html function
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        return $this->_toHtml();
    }

    /**
     * Get Ajax url function
     *
     * @return string
     */
    public function getAjaxUrl()
    {
        return $this->getUrl('recurring/system/generatewebhook');
    }

    /**
     * Generate webhook button function
     *
     * @return string
     */
    public function getButtonHtml()
    {
        $webHookId = $this->scopeConfig->getValue(
            'payment/recurringstripe/webhook_id',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $secretKey = $this->scopeConfig->getValue(
            'payment/recurringstripe/api_secret_key',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if (!$webHookId && $secretKey) {
            $button = $this->getLayout()->createBlock(
                \Magento\Backend\Block\Widget\Button::class
            )->setData(
                [
                    'id' => 'generatewebhook',
                    'label' => __('Generate Webhooks'),
                ]
            );
            return $button->toHtml();
        }
        return '';
    }
}
