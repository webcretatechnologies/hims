<?php
namespace Hims\Testimonial\Controller\Adminhtml\Index;

class Index extends \Magento\Backend\App\Action
{
    private $resultPageFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Hims_Testimonial::manager');
        $resultPage->getConfig()->getTitle()->prepend(__('Testimonial'));
        return $resultPage;
    }
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Hims_Testimonial::manager');
    }
}
