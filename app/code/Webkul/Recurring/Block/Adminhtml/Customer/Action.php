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

namespace Webkul\Recurring\Block\Adminhtml\Customer;

class Action extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * Array to store all options data
     *
     * @var array
     */
    protected $actions = [];

    /**
     * Render data
     *
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $this->actions = [];
        if ($row->getId()) {
            $reorderAction = [
                '@' => [
                    'href' => $this->getUrl('recurring/subscriptions/edit', ['id' => $row->getId()]),
                ],
                '#' => __('View In details'),
            ];
            $this->addToActions($reorderAction);
        }
        return $this->_actionsToHtml();
    }

    /**
     * Render options array as a HTML string
     *
     * @param array $actions
     * @return string
     */
    protected function _actionsToHtml(array $actions = [])
    {
        $html = [];
        $attributesObject = new \Magento\Framework\DataObject();

        if (empty($actions)) {
            $actions = $this->actions;
        }

        foreach ($actions as $action) {
            $attributesObject->setData($action['@']);
            $html[] = '<a ' . $attributesObject->serialize() . '>' . $action['#'] . '</a>';
        }
        return implode('', $html);
    }

    /**
     * Add one action array to all options data storage
     *
     * @param array $actionArray
     */
    public function addToActions($actionArray)
    {
        $this->actions[] = $actionArray;
    }
}
