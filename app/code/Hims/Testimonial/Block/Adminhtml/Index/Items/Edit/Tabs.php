<?php

namespace Hims\Testimonial\Block\Adminhtml\Index\Items\Edit;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    protected function _construct()
    {
        parent::_construct();
        $this->setId('testimonial_edit_tab');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Testimonial'));
    }
}
