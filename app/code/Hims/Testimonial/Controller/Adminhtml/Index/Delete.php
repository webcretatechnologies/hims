<?php
namespace Hims\Testimonial\Controller\Adminhtml\Index;

use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Hims\Testimonial\Model\ResourceModel\Grid\CollectionFactory;

class Delete extends \Magento\Backend\App\Action
{
    protected $_filter;
    protected $_collectionFactory;
    
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory
    ) {

        $this->_filter = $filter;
        $this->_collectionFactory = $collectionFactory;
        parent::__construct($context);
    }
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $collection = $this->_collectionFactory->create();
        $collection->addFieldToFilter('testimonial_id', $id);
        foreach ($collection->getItems() as $item) {
            $item->delete();
        }
        $this->messageManager->addSuccess(__('record have been deleted.'));
         return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('*/*/index');
    }
}
