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
namespace Webkul\Recurring\Block\Adminhtml;

use Magento\Backend\Block\Widget\Grid\Container;

/**
 * Adminhtml Recurring Block Plans
 */
class Plans extends Container
{
    /**
     * Constructor
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_plans';
        $this->_blockGroup = 'Webkul_Recurring';
        $this->_headerText = __('Manage Subscription Type');
        parent::_construct();
        $this->buttonList->update('add', 'label', __('Add New Subscription Type'));
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
}
