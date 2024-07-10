<?php

namespace Webcreta\ProductRecommendationQuiz\Controller\Jump;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Psr\Log\LoggerInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Helper\Image;
use Magento\Catalog\Model\Product\Url as ProductUrl;
use Magento\Customer\Model\Session;
use Webcreta\ProductRecommendationQuiz\Model\ProductRecommendationQuizDataFactory;
use Webcreta\ProductRecommendationQuiz\Helper\Data as QuizHelper;

class GetNextQuestion extends Action
{
    protected $jsonFactory;
    protected $logger;
    protected $productFactory;
    protected $imageHelper;
    protected $productUrl;
    protected $customerSession;
    protected $productRecommendationQuizDataFactory;
    protected $quizHelper;

    public function __construct(
        Context $context,
        JsonFactory $jsonFactory,
        LoggerInterface $logger,
        ProductFactory $productFactory,
        Image $imageHelper,
        ProductUrl $productUrl,
        Session $customerSession,
        ProductRecommendationQuizDataFactory $productRecommendationQuizDataFactory,
        QuizHelper $quizHelper
    ) {
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
        $this->logger = $logger;
        $this->productFactory = $productFactory;
        $this->imageHelper = $imageHelper;
        $this->productUrl = $productUrl;
        $this->customerSession = $customerSession;
        $this->productRecommendationQuizDataFactory = $productRecommendationQuizDataFactory;
        $this->quizHelper = $quizHelper;
    }

    public function execute()
    {
        $result = $this->jsonFactory->create();
        $data = $this->getRequest()->getPostValue();

        if (empty($data)) {
            return $result->setData(['success' => false, 'error' => 'No data received']);
        }

        try {
            $current_question_id = isset($data['current_question_id']) ? $data['current_question_id'] : null;
            $selected_option_id = isset($data['selected_option_id']) ? $data['selected_option_id'] : null;
            $attribute_set_id = isset($data['attribute_set_id']) ? $data['attribute_set_id'] : null;

            if (!$current_question_id) {
                return $result->setData(['success' => false, 'error' => 'Invalid request parameters']);
            }

            $type = $this->quizHelper->getAttributeType($current_question_id);

            if ($type == 'text') {
                $questionData = $this->quizHelper->getLogicandquestionData($current_question_id, $selected_option_id, $attribute_set_id);
            } elseif ($type == 'date' || $type == 'media_image') {
                $questionData = $this->quizHelper->getNextQuestionData($current_question_id, $selected_option_id, $attribute_set_id);
            } else {
                $questionData = $this->quizHelper->getnextQuestion($current_question_id, $selected_option_id, $attribute_set_id);
            }

            $customerId = $this->customerSession->getCustomerId();

            if ($questionData) {
                $next_question_id = $questionData['next_question_id'];
                $questionName = $this->quizHelper->getAttributeLabel($next_question_id);
                $questionOption = $this->quizHelper->getOptionsByQuestionId($next_question_id);
                $attributeType = $this->quizHelper->getAttributeType($next_question_id);
                $productName = '';
                if ($next_question_id == 'final_question') {
                    $productId = $questionData['product'];
                    $product = $this->productFactory->create()->load($productId);
                    $productName = $product->getName();
                    $productPrice = $product->getPrice();
                    $productImageUrl = $this->imageHelper->init($product, 'product_page_image_large')->getUrl();
                    $productUrl = $this->productUrl->getUrl($product);

                    $responseData = [
                        "final" => true,
                        "productName" => $productName,
                        "productImage" => $productImageUrl,
                        "productUrl" => $productUrl
                    ];
                } else {
                    $quizDataModel = $this->productRecommendationQuizDataFactory->create();
                    $existingRecord = $quizDataModel->getCollection()
                        ->addFieldToFilter('customer_id', $customerId)
                        ->addFieldToFilter('category', $attribute_set_id)
                        ->getFirstItem();

                    $matchedValue = '';
                    if ($existingRecord->getId() && $existingRecord->getQuestionSet()) {
                        $question = $existingRecord->getQuestionSet();
                        $dataArray = json_decode($question, true);
                        if (array_key_exists($next_question_id, $dataArray)) {
                            $matchedValue = $dataArray[$next_question_id];
                        }
                    }

                    $responseData = [
                        'question_id' => $next_question_id,
                        'type' => $attributeType,
                        'question' => $questionName,
                        'options' => $questionOption,
                        'selected_value' => $matchedValue,
                        "final" => false
                    ];
                }

                $this->quizHelper->saveQuizData($customerId, $current_question_id, $selected_option_id, $attribute_set_id, $next_question_id, $productName);

                return $result->setData(['success' => true, 'data' => $responseData]);
            }

            return $result->setData(['success' => false, 'error' => 'Question data not found']);
        } catch (\Exception $e) {
            $this->logger->error("An error occurred: " . $e->getMessage());
            return $result->setData(['success' => false, 'error' => $e->getMessage()]);
        }
    }
}
