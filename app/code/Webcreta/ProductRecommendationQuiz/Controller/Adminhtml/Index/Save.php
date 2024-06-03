<?php

namespace Webcreta\ProductRecommendationQuiz\Controller\Adminhtml\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Webcreta\ProductRecommendationQuiz\Model\ProductRecommendationQuizFactory;

class Save extends Action
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
        $result = $this->jsonFactory->create();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $logger = $objectManager->get('Psr\Log\LoggerInterface');
        $logger->log(100, "I am Save");
    
        $data = $this->getRequest()->getPostValue();
        
        if (isset($data['question_id']) && isset($data['option_id']) && is_array($data['question_id']) && is_array($data['option_id'])) {
            $questionSet = [];
            foreach ($data['question_id'] as $index => $questionId) {
                $optionId = isset($data['option_id'][$index]) ? $data['option_id'][$index] : null;
                $nextQuestionId = isset($data['next_question_id'][$index]) ? $data['next_question_id'][$index] : null;
                $conditionId = isset($data['condition_id'][$index]) ? $data['condition_id'][$index] : null;
                $questionSet[] = [
                    'question_id' => $questionId,
                    'option_id' => $optionId,
                    'next_question_id' => $nextQuestionId,
                    'condition_id' => $conditionId,
                ];
            }
            $jsonData = json_encode($questionSet);
            $logger->log(100, print_r($jsonData, true));
            $customData = $this->productRecommendationQuizFactory->create();
            if (isset($data['id']) && $data['id']) {
                $customData->load($data['id']);
            }
            $customData->setData('question_set', $jsonData);
            $customData->setData('product', $data['product']);
            $customData->setData('attribute_set_id', $data['attribute_set_id']);
            $customData->setData('set_id', $data['set_id']);
    
            $customData->save();
        }
    
        return $result->setData(['success' => true]);
    }
    
}
