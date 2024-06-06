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
namespace Webkul\Recurring\Model\Subscriptions\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Webkul Recurring Plans Source Status Model
 */
class Status implements OptionSourceInterface
{
    public const ENABLED  = 1;
    public const DISABLED = 0;
    
    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [
            [
                'label' => __("Subscribed"),
                'value' => self::ENABLED,
            ],
            [
                'label' => __("UnSubscribed"),
                'value' => self::DISABLED,
            ]
        ];
        return $options;
    }
}
