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

namespace Webkul\Recurring\Block\Adminhtml\Config;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;
use Webkul\Recurring\Block\Adminhtml\Config\Form\CustomColumn;

class Form extends AbstractFieldArray
{
    /**
     * @var string
     */
    private $dropdownRenderer;

    /**
     * Prepare render
     */
    protected function _prepareToRender()
    {
        $this->addColumn(
            'cancellation_resason',
            [
                    'label' => __('Reasons'),
                    'class' => 'required-entry validate-no-html-tags',
                ]
        );
        $this->addColumn(
            'sort_order',
            [
                    'label' => __('Sort Order'),
                    'class' => 'required-entry validate-no-html-tags',
                ]
        );
        $this->addColumn(
            'visibility',
            [
                    'label' => __('Visibility'),
                    'renderer' => $this->getDropdownRenderer(),
                ]
        );
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add Reason');
    }

    /**
     * Prepare array row
     *
     * @param DataObject $row
     */
    protected function _prepareArrayRow(DataObject $row)
    {
        $options = [];
        $dropdownField = $row->getDropdownField();
        if ($dropdownField !== null) {
                $options['option_' . $this->getDropdownRenderer()->calcOptionHash($dropdownField)] =
                'selected="selected"';
        }
        $row->setData('option_extra_attrs', $options);
    }

    /**
     * Get dropdown render
     *
     * @return array
     */
    private function getDropdownRenderer()
    {
        if (!$this->dropdownRenderer) {
                $this->dropdownRenderer = $this->getLayout()->createBlock(
                    CustomColumn::class,
                    '',
                    ['data' => ['is_render_to_js_template' => true]]
                );
        }
        return $this->dropdownRenderer;
    }
}
