<?php

namespace Webcreta\ProductRecommendationQuiz\Controller\Jump;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Psr\Log\LoggerInterface;
use Magento\Customer\Model\Session;
use Webcreta\ProductRecommendationQuiz\Model\ProductRecommendationQuizDataFactory;

class GetOldQuestion extends Action
{
    protected $jsonFactory;
    protected $eavAttribute;
    protected $customerSession;
    protected $productRecommendationQuizDataFactory;
    protected $logger;

    public function __construct(
        Context $context,
        JsonFactory $jsonFactory,
        Attribute $eavAttribute,
        Session $customerSession,
        ProductRecommendationQuizDataFactory $productRecommendationQuizDataFactory,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
        $this->eavAttribute = $eavAttribute;
        $this->customerSession = $customerSession;
        $this->productRecommendationQuizDataFactory = $productRecommendationQuizDataFactory;
        $this->logger = $logger;
    }

    public function execute()
    {
        $result = $this->jsonFactory->create();
        $categoryId = $this->getRequest()->getPostValue('categoryAttributeValue');
        $this->logger->debug('Category ID: ' . $categoryId);
    
        $customerId = $this->customerSession->getCustomerId();
        $this->logger->debug('Customer ID: ' . $customerId);
    
        $collection = $this->productRecommendationQuizDataFactory->create()->getCollection();
        $collection->addFieldToFilter('category', $categoryId)
                   ->addFieldToFilter('customer_id', $customerId);
    
        if ($collection->getSize() > 0) {
            // Get the first item
            $firstItem = $collection->getFirstItem();
            
            // Check if the item exists
            if ($firstItem->getId()) {
                $set = json_decode($firstItem->getQuestionSet(), true);
            
                // Initialize an empty array to store the modified set with sequential IDs
                $modifiedSet = [];
                
                // Iterate through the $set array and re-index it
                $index = 1;
    foreach ($set as $key => $value) {
        $modifiedSet[] = [
            'id' => $index,
            'key' => $key,
            'value' => $value
        ];
        $index++;
    }
            
                // print_r($modifiedSet);
                $result->setData([
                    'success' => 'question_set field set to null',
                    'data' => $modifiedSet
                ]);
            }
        } else {
            $result->setData(['error' => 'No item found for this category and customer']);
        }
    
        return $result;
    }
    
}
