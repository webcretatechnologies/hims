<?php

namespace Hims\Testimonial\Controller\Adminhtml\Index;

class NewAction extends \Hims\Testimonial\Controller\Adminhtml\Index\AddRow
{

    public function execute()
    {
        $this->_forward('edit');
    }
}
