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
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $logger = $objectManager->get('Psr\Log\LoggerInterface');
        $logger->log(100, "I am Save");
        
        $data = $this->getRequest()->getPostValue();
        $logger->log(100, print_r($data, true));

        $setId = $data['category_id'];
        $logger->log(100, print_r($setId, true));

        $collection = $this->productRecommendationQuizCategoryFactory->create()->getCollection()
            ->addFieldToFilter('id', $setId);
            
            // $logger->log(100, print_r($collection->getData(), true));

        $category = $collection->getFirstItem(); // Retrieve the first item from the collection
        $categoryId = $category->getCategory(); // Assuming 'category_id' is the field name in your database table

        $category = $this->categoryFactory->create()->load($categoryId);
        $attributeValue = $category->getData('choose_category_quiz');
        $logger->log(100, print_r($attributeValue, true));


        if (!empty($data)) {
            $logger->log(100, print_r($data, true));

            try {
                $lastNextQuestionId = null; 
                $lastIndex = count($data['product']) - 1;

                foreach ($data['question_id'] as $key => $questionId) {
                    $customData = $this->productRecommendationQuizFactory->create();

                    if (is_array($data['option_id']) && isset($data['option_id'][$key])) {
                        $option_id = implode(',', (array) $data['option_id'][$key]);
                    }

                    if (!empty($data['id'][$key])) { 
                        $logger->log(100, print_r("updated data", true));

                        $id = $data['id'][$key];
                        $logger->log(100, print_r($id, true));
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
                            'attribute_set_id' =>$attributeValue,
                            'question_id' => $questionId,
                            'option_id' => $option_id,
                            'next_question_id' => $data['next_question_id'][$key],
                            'product' => $data['product'][$key],
                            'default_id' => $valueToSave,
                        ]);
                        $logger->log(100, print_r('updated success', true));

                        $existingEntry->save();

                    }else{
                        $optionId = $data['default_id'][$key];
                        if (!empty($optionId)) {
                            $valueToSave = 1;
                        } else {
                            $valueToSave = 0;
                        }

                        $logger->log(100, print_r("new data", true));

                        if (is_array($data['option_id']) && isset($data['option_id'][$key])) {
                            $option_id = implode(',', (array) $data['option_id'][$key]);
                        }

                        $customData->setData([
                            'attribute_set_id' =>$attributeValue,
                            'set_id' => $setId,
                            'question_id' => $questionId,
                            'option_id' => $option_id,
                            'next_question_id' => $data['next_question_id'][$key],
                            'product' => $data['product'][$key],
                            'default_id' => $valueToSave,
                        ]);
                        $logger->log(100, print_r("key" . $key, true));

                        if ($key === $lastIndex) {
                            $customData->setData('product', $data['product'][$lastIndex]);
                        }
                        $logger->log(100, print_r('insert success', true));

                        $customData->save();

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
