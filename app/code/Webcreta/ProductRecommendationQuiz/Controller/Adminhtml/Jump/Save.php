<?php

namespace Webcreta\ProductRecommendationQuiz\Controller\Adminhtml\Jump;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Webcreta\ProductRecommendationQuiz\Model\ProductRecommendationQuizFactory;
use Webcreta\ProductRecommendationQuiz\Model\ProductRecommendationQuizCategoryFactory;
use Magento\Catalog\Model\CategoryFactory;

class Save extends Action
{
    protected $jsonFactory;
    protected $productRecommendationQuizFactory;
    protected $productRecommendationQuizCategoryFactory;
    protected $categoryFactory;

    public function __construct(
        Context $context,
        ProductRecommendationQuizFactory $productRecommendationQuizFactory,
        ProductRecommendationQuizCategoryFactory $productRecommendationQuizCategoryFactory,
        JsonFactory $jsonFactory,
        CategoryFactory $categoryFactory
    ) {
        parent::__construct($context);
        $this->productRecommendationQuizFactory = $productRecommendationQuizFactory;
        $this->productRecommendationQuizCategoryFactory = $productRecommendationQuizCategoryFactory;
        $this->jsonFactory = $jsonFactory;
        $this->categoryFactory = $categoryFactory;
    }

    public function execute()
    { 
        $result = $this->jsonFactory->create();
       
        $data = $this->getRequest()->getPostValue();
        
        $setId = $data['category_id'];
        
        $collection = $this->productRecommendationQuizCategoryFactory->create()->getCollection()
            ->addFieldToFilter('id', $setId);
        
        $category = $collection->getFirstItem();
        $categoryId = $category->getCategory();
        
        $category = $this->categoryFactory->create()->load($categoryId);
        $attributeValue = $category->getData('choose_category_quiz');
        
        if (!empty($data)) {
        
            try {
                $lastNextQuestionId = null; 
                $lastIndex = count($data['product']) - 1;
                $groupId = 1; // Initialize group_id
        
                foreach ($data['question_id'] as $key => $questionId) {
                    $customData = $this->productRecommendationQuizFactory->create();
        
                    if (is_array($data['option_id']) && isset($data['option_id'][$key])) {
                        $option_id = implode(',', (array) $data['option_id'][$key]);
                    }
        
                    if (!empty($data['id'][$key])) { 
        
                        $id = $data['id'][$key];
                        $optionId = $data['default_id'][$key];
                        if (!empty($optionId)) {
                            $valueToSave = 1;
                        } else {
                            $valueToSave = 0;
                        }
        
                        $existingEntry = $this->productRecommendationQuizFactory->create()->load($id);
                        $existingEntry->setData([
                            'id' => $id,
                            'set_id' => $setId,
                            'attribute_set_id' => $attributeValue,
                            'question_id' => $questionId,
                            'option_id' => $option_id,
                            'next_question_id' => $data['next_question_id'][$key],
                            'product' => $data['product'][$key],
                            'default_id' => $valueToSave,
                            'group_id' => $groupId, // Add group_id
                        ]);
        
                        $existingEntry->save();
        
                    } else {
                        $optionId = $data['default_id'][$key];
                        if (!empty($optionId)) {
                            $valueToSave = 1;
                        } else {
                            $valueToSave = 0;
                        }
                
                        if (is_array($data['option_id']) && isset($data['option_id'][$key])) {
                            $option_id = implode(',', (array) $data['option_id'][$key]);
                        }
        
                        $customData->setData([
                            'attribute_set_id' => $attributeValue,
                            'set_id' => $setId,
                            'question_id' => $questionId,
                            'option_id' => $option_id,
                            'next_question_id' => $data['next_question_id'][$key],
                            'product' => $data['product'][$key],
                            'default_id' => $valueToSave,
                            'group_id' => $groupId, // Add group_id
                        ]);
        
                        if ($key === $lastIndex) {
                            $customData->setData('product', $data['product'][$lastIndex]);
                        }
        
                        $customData->save();
                    }
        
                    if (!empty($data['product'][$key])) {
                        $groupId++; // Increment group_id
                    }
                }
                return $result->setData(['success' => true]);
            } catch (\Exception $e) {
                return $result->setData(['success' => false, 'error' => $e->getMessage()]);
            }
        } else {
            return $result->setData(['success' => false, 'error' => 'No data received']);
        }
    }        
}
