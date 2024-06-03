<?php
namespace Hims\Testimonial\Controller\Adminhtml\Index;

use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Framework\Image\AdapterFactory;

abstract class AddRow extends \Magento\Backend\App\Action
{
    protected $_coreRegistry;
    protected $gridFactory;
    protected $resultForwardFactory;
    protected $resultPageFactory;
    protected $uploaderFactory;
    protected $adapterFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Hims\Testimonial\Model\GridFactory $gridFactory,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        UploaderFactory $uploaderFactory,
        AdapterFactory $adapterFactory,
        \Magento\Framework\Filesystem\Driver\File $file
    ) {
        $this->gridFactory = $gridFactory;
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context);
    }

    protected function _initAction()
    {
        $this->_view->loadLayout();
        $this->_setActiveMenu('Hims_Testimonial::manager')->_addBreadcrumb(__('Testimonial'), __('Testimonial'));
        return $this;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Hims_Testimonial::add_row');
    }
}
