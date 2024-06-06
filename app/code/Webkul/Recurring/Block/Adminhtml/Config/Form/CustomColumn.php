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

namespace Webkul\Recurring\Block\Adminhtml\Config\Form;

use Magento\Framework\View\Element\Html\Select;

class CustomColumn extends Select
{
    /**
     * Set input name
     *
     * @param string $value
     * @return string
     */
    public function setInputName($value)
    {
        return $this->setName($value);
    }

    /**
     * Set input id
     *
     * @param string $value
     * @return string
     */
    public function setInputId($value)
    {
        return $this->setId($value);
    }

    /**
     * To html
     *
     * @return string
     */
    public function _toHtml()
    {
        if (!$this->getOptions()) {
            $this->setOptions($this->getSourceOptions());
        }
        return parent::_toHtml();
    }

    /**
     * Get options
     *
     * @return array
     */
    private function getSourceOptions()
    {
        return [
            ['label' => 'Yes', 'value' => '1'],
            ['label' => 'No', 'value' => '0'],
        ];
    }
}
