<?php

namespace Webcreta\ProductRecommendationQuiz\Controller\Adminhtml\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Webcreta\ProductRecommendationQuiz\Model\ProductRecommendationQuizFactory;

class AddNewSet extends Action
{
    protected $jsonFactory;
    protected $productRecommendationQuizFactory;

    public function __construct(
        Context $context,
        ProductRecommendationQuizFactory $productRecommendationQuizFactory,
        JsonFactory $jsonFactory
    ) {
        parent::__construct($context);
        $this->productRecommendationQuizFactory = $productRecommendationQuizFactory;
        $this->jsonFactory = $jsonFactory;
    }

    public function execute()
    {
        $attribute_set_id = $this->getRequest()->getParam('custom_attribute_set');
    
        $collection = $this->productRecommendationQuizFactory->create()->getCollection()
            ->addFieldToFilter('attribute_set_id', $attribute_set_id)
            ->setPageSize(1);
        
        $lastRecord = $this->productRecommendationQuizFactory->create()->getCollection()
            ->setOrder('set_id', 'DESC')
            ->setPageSize(1)
            ->getFirstItem();
    
        if ($lastRecord->getId()) {
            $newSetId = $lastRecord->getSetId() + 1;
            
            $collectionItem = $collection->getFirstItem();

            $duplicatedData = $this->productRecommendationQuizFactory->create();
            $duplicatedData->setData([
                'set_id' => $newSetId,
                'attribute_set_id' => $collectionItem->getData('attribute_set_id'), // Assuming 'attribute_set_id' is a field in your collection
            ]);
            $duplicatedData->save();
            return $this->jsonFactory->create()->setData(['success' => true]);
        }
    
        return $this->jsonFactory->create()->setData(['success' => false]);
    }
     
    
}
