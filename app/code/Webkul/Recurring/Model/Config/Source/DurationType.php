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

class DurationType implements OptionSourceInterface
{
    public const DAY  = 'day';
    public const WEEK = 'week';
    public const MONTH = 'month';
    public const YEAR = "year";

    /**
     * Get duration type items
     */
    public function toOptionArray()
    {
        $options = [
                [
                    'label' => __("Day"),
                    'value' => self::DAY,
                ],
                [
                    'label' => __("Week"),
                    'value' => self::WEEK,
                ],
                [
                    'label' => __("Month"),
                    'value' => self::MONTH,
                ],
                [
                    'label' => __("Year"),
                    'value' => self::YEAR,
                ]
            ];

        return $options;
    }
}
