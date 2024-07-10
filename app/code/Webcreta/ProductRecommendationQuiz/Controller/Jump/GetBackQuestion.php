<?php

namespace Webcreta\ProductRecommendationQuiz\Controller\Jump;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Webcreta\ProductRecommendationQuiz\Helper\Data as QuizHelper;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Helper\ImageFactory;

class GetBackQuestion extends Action
{
    protected $jsonFactory;
    protected $quizHelper;
    protected $productFactory;
    protected $imageHelper;

    public function __construct(
        Context $context,
        JsonFactory $jsonFactory,
        QuizHelper $quizHelper,
        ProductFactory $productFactory,
        ImageFactory $imageHelper,
    ) {
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
        $this->quizHelper = $quizHelper;
        $this->productFactory = $productFactory;
        $this->imageHelper = $imageHelper;
    }

    public function execute()
    {
        $result = $this->jsonFactory->create();
        $data = $this->getRequest()->getPostValue();

        if (empty($data)) {
            return $result->setData(['success' => false, 'error' => 'No data received']);
        }

        try {
            $current_question_id = $data['current_question_id'] ?? null;
            $selected_option_id = $data['selected_option_id'] ?? null;
            $attribute_set_id = $data['attribute_set_id'] ?? null;

            if (!$current_question_id) {
                return $result->setData(['success' => false, 'error' => 'Invalid request parameters']);
            }

            $type = $this->quizHelper->getAttributeType($current_question_id);

            if ($type == 'text') {
                $questionData = $this->quizHelper->getLogicandquestionData($current_question_id, $selected_option_id, $attribute_set_id);
            } else {
                $questionData = $this->quizHelper->getNextQuestionData($current_question_id, $selected_option_id, $attribute_set_id);
            }

            if (!$questionData) {
                return $result->setData(['success' => false, 'error' => 'Question data not found']);
            }

            $next_question_id = $questionData['question_id'];

            $questionName = $this->quizHelper->getAttributeLabel($next_question_id);
            $questionOption = $this->quizHelper->getOptionsByQuestionId($next_question_id);

            $attributeType = $this->quizHelper->getAttributeType($next_question_id);

            if ($questionData['question_id'] == 'final_question') {
                $productId = $questionData['product'];
                $product = $this->productFactory->create()->load($productId);

                $productName = $product->getName();
                $productPrice = $product->getPrice();

                $productImageUrl = $this->imageHelper->create()->init($product, 'product_page_image_large')->getUrl();

                $responseData = [
                    "final" => true,
                    "productName" => $productName,
                    "productImage" => $productImageUrl
                ];
            } else {
                $responseData = [
                    'question_id' => $questionData['question_id'],
                    'type' => $attributeType,
                    'question' => $questionName,
                    'options' => $questionOption,
                    "final" => false
                ];
            }

            return $result->setData(['success' => true, 'data' => $responseData]);
        } catch (\Exception $e) {
            return $result->setData(['success' => false, 'error' => $e->getMessage()]);
        }
    }
}
