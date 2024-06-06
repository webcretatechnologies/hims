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

use Magento\Framework\Data\OptionSourceInterface;

class Status implements OptionSourceInterface
{
    public const ENABLED  = 1;
    public const DISABLED = 0;

    /**
     * Get Enable and Disable function
     */
    public function toOptionArray()
    {
        $options = [
                [
                    'label' => __("Enabled"),
                    'value' => self::ENABLED,
                ],
                [
                    'label' => __("Disabled"),
                    'value' => self::DISABLED,
                ]
            ];

        return $options;
    }
}
