<?php

namespace Hims\Testimonial\Block\Adminhtml\Index;


class AddRow extends \Magento\Backend\Block\Widget\Grid\Container
{

    public function __construct() {
        $this->_controller = 'items';
        $this->_headerText = __('Items');
        $this->_addButtonLabel = __('Add New Item');
        parent::_construct();
    }
}
