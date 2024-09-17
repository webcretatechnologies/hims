<?php

namespace Webcreta\ProductRecommendationQuiz\Controller\Jump;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Webcreta\ProductRecommendationQuiz\Helper\Data as QuizHelper;
use Webcreta\ProductRecommendationQuiz\Model\ProductRecommendationQuizFactory;

class GetQuestion extends Action
{
    protected $jsonFactory;
    protected $productRecommendationQuizFactory;
    protected $quizHelper;

    public function __construct(
        Context $context,
        ProductRecommendationQuizFactory $productRecommendationQuizFactory,
        JsonFactory $jsonFactory,
        QuizHelper $quizHelper
    ) {
        parent::__construct($context);
        $this->productRecommendationQuizFactory = $productRecommendationQuizFactory;
        $this->jsonFactory = $jsonFactory;
        $this->quizHelper = $quizHelper;
    }

    public function execute()
    {
        /** @var Json $result */
        $result = $this->jsonFactory->create();

        $data = $this->getRequest()->getPostValue('categoryAttributeValue');
        if (!$data) {
            return $result->setData(['success' => false, 'error' => 'No data received']);
        }

        $quizModel = $this->productRecommendationQuizFactory->create();
        $questionData = $quizModel->getCollection()
            ->addFieldToFilter('attribute_set_id', $data)
            ->addFieldToFilter('default_id', 1)
            ->getFirstItem();

        if (!$questionData->getId()) {
            return $result->setData(['success' => false, 'error' => 'Question not found']);
        }

        $questionId = $questionData->getQuestionId();
        $questionName = $this->quizHelper->getAttributeLabel($questionId);
        $questionOption = $this->quizHelper->getOptionsByQuestionId($questionId ,$data);
        $inputType = $this->quizHelper->getAttributeType($questionId);
        $groupId = $this->quizHelper->getGroupIds($questionId);
        $responseData = [
            'question_id' => $questionId,
            'question' => $questionName,
            'options' => $questionOption,
            'type' => $inputType,
            'group_id' => $groupId
        ];
      
        return $result->setData(['success' => true, 'data' => $responseData]);
    }
}
