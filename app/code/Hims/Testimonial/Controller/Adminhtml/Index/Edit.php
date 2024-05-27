<?php

namespace Hims\Testimonial\Controller\Adminhtml\Index;

class Edit extends \Hims\Testimonial\Controller\Adminhtml\Index\AddRow
{
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        
        $model = $this->gridFactory->create();

        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addError(__('This item no longer exists.'));
                $this->_redirect('testimonial/*');
                return;
            }
        }
        $this->_coreRegistry->register('row_data', $model);
        $this->_initAction();
        $this->_view->getLayout()->getBlock('testimonial_items_edit');
        $this->_view->renderLayout();
    }
}