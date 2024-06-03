<?php
namespace Hims\Testimonial\Controller\Adminhtml\Index;

use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Hims\Testimonial\Model\ResourceModel\Grid\CollectionFactory;

class MassApproved extends \Magento\Backend\App\Action
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

        $collection = $this->_filter->getCollection($this->_collectionFactory->create());
        $recordApproved = 0;
        foreach ($collection->getItems() as $record) {
            if ($record['status'] == "Approved") {
                // If the record is already approved, increment the approved count
                $recordApproved++;
            } else {
             // Set the status to "Approved"
             $record['status'] = "Approved";
             $record->save();
             $recordApproved++;
         }
        }
        $this->messageManager->addSuccess(__('A total of %1 record(s) have been approved.', $recordApproved));

        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('*/*/index');
    }
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webcreta_Brand::row_data_approve');
    }
}