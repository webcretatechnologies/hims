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

use Magento\Email\Model\Template\Config;
use Magento\Framework\Option\ArrayInterface;

class Email extends \Magento\Framework\DataObject implements ArrayInterface
{
    /**
     * @var Config
     */
    protected $emailTemplateConfig;

    /**
     * @param Config $emailTemplateConfig
     */
    public function __construct(
        Config $emailTemplateConfig
    ) {
        $this->emailTemplateConfig = $emailTemplateConfig;
    }

    /**
     * Options getter.
     *
     * @return array
     */
    public function toOptionArray()
    {
        $emailTemplates = [];
        $availableTemplates = $this->emailTemplateConfig->getAvailableTemplates();
        foreach ($availableTemplates as $template) {
            if ($template['group'] == 'Webkul_Recurring') {
                $emailTemplates[$template['value']] = [
                    'label' => __($template['label']),
                    'value' => $template['value']
                ];
            }
        }
        return $emailTemplates;
    }
}
