<?php

namespace Webcreta\ProductRecommendationQuiz\Controller\Jump;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Webcreta\ProductRecommendationQuiz\Model\ProductRecommendationQuizFactory;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Psr\Log\LoggerInterface;
use Magento\Swatches\Helper\Media;
use Magento\Swatches\Model\ResourceModel\Swatch\CollectionFactory;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Helper\Image;
use Magento\Catalog\Model\Product\Url as ProductUrl;
use Magento\Customer\Model\Session;
use Webcreta\ProductRecommendationQuiz\Model\ProductRecommendationQuizDataFactory;

class GetNextQuestion extends Action
{
    protected $jsonFactory;
    protected $productRecommendationQuizFactory;
    protected $eavAttribute;
    protected $logger;
    protected $swatchMediaHelper;
    protected $swatchCollectionFactory;
    protected $productFactory;
    protected $imageHelper;
    protected $productUrl;
    protected $customerSession;
    protected $productRecommendationQuizDataFactory;

    public function __construct(
        Context $context,
        ProductRecommendationQuizFactory $productRecommendationQuizFactory,
        JsonFactory $jsonFactory,
        Attribute $eavAttribute,
        LoggerInterface $logger,
        Media $swatchMediaHelper,
        CollectionFactory $swatchCollectionFactory,
        ProductFactory $productFactory,
        Image $imageHelper,
        ProductUrl $productUrl,
        Session $customerSession,
        ProductRecommendationQuizDataFactory $productRecommendationQuizDataFactory
    ) {
        parent::__construct($context);
        $this->productRecommendationQuizFactory = $productRecommendationQuizFactory;
        $this->jsonFactory = $jsonFactory;
        $this->eavAttribute = $eavAttribute;
        $this->logger = $logger;
        $this->swatchMediaHelper = $swatchMediaHelper;
        $this->swatchCollectionFactory = $swatchCollectionFactory;
        $this->productFactory = $productFactory;
        $this->imageHelper = $imageHelper;
        $this->productUrl = $productUrl;
        $this->customerSession = $customerSession;
        $this->productRecommendationQuizDataFactory = $productRecommendationQuizDataFactory;
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

            $type = $this->getAttributeType($current_question_id);
            // print_r($type);
            if ($type == 'text') {
                $questionData = $this->getLogicandquestionData($current_question_id, $selected_option_id, $attribute_set_id);
            }
            elseif ($type == 'date') {
                $questionData = $this->getNextQuestionData($current_question_id, $selected_option_id, $attribute_set_id);
            } elseif ($type == 'media_image') {
                $questionData = $this->getNextQuestionData($current_question_id, $selected_option_id, $attribute_set_id);
            } else {
                $questionData = $this->getQuestionData($current_question_id, $selected_option_id, $attribute_set_id);
            }

            // print_r($questionData->getData());
            // die();
            $customerId = $this->customerSession->getCustomerId();


            if ($questionData) {
                $next_question_id = $questionData['next_question_id'];
                $questionName = $this->getAttributeLabel($next_question_id);
                $questionOption = $this->getOptionsByQuestionId($next_question_id);
                $attributeType = $this->getAttributeType($next_question_id);

                // Final question handling
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

                // Save or update quiz data
                $questionSet = json_encode([$current_question_id => $selected_option_id]);

                if ($customerId) {
                    $quizDataModel = $this->productRecommendationQuizDataFactory->create();
                    $existingRecord = $quizDataModel->getCollection()
                        ->addFieldToFilter('customer_id', $customerId)
                        ->addFieldToFilter('category', $attribute_set_id)
                        ->getFirstItem();

                    if ($existingRecord->getId() && $existingRecord->getQuestionSet()) {
                        if ($next_question_id == 'final_question') {
                            $existingRecord->setData('product', $productName);
                        } else {
                            $existingRecord->setData('product', "currently not define");
                        }

                        $existingQuestionSet = json_decode($existingRecord->getData('question_set'), true);
                        $existingQuestionSet[$current_question_id] = $selected_option_id;

                        $questionSet = json_encode($existingQuestionSet);
                        $existingRecord->setData('question_set', $questionSet);

                        try {
                            $existingRecord->save();
                        } catch (\Exception $e) {
                            $this->logger->critical($e);
                        }
                    } else {
                        $quizDataModel->setData('customer_id', $customerId);
                        $quizDataModel->setData('question_set', $questionSet);
                        $quizDataModel->setData('category', $attribute_set_id);
                        $quizDataModel->setData('product', "currently not define");

                        try {
                            $quizDataModel->save();
                        } catch (\Exception $e) {
                            $this->logger->critical($e);
                        }
                    }
                }

                return $result->setData(['success' => true, 'data' => $responseData]);
            }

            return $result->setData(['success' => false, 'error' => 'Question data not found']);
        } catch (\Exception $e) {
            $this->logger->error("An error occurred: " . $e->getMessage());
            return $result->setData(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    protected function getLogicandquestionData($current_question_id, $selected_option_id, $attribute_set_id)
    {
        $questionData = [];

        try {
            $quizModel = $this->productRecommendationQuizFactory->create();
            if (!empty($selected_option_id)) {
                $collection = $quizModel->getCollection()
                    ->addFieldToFilter('question_id', $current_question_id)
                    ->addFieldToFilter('attribute_set_id', $attribute_set_id);

                $options = $collection->getItems();
                foreach ($options as $option) {
                    $value = $option->getOptionId();

                    $conditionString = "$selected_option_id $value";
                    $condition = eval("return $conditionString;");

                    if ($condition) {
                        $questionData = $option->getData();
                        break;
                    }
                }
            } else {
                $collection = $quizModel->getCollection()
                    ->addFieldToFilter('question_id', $current_question_id)
                    ->addFieldToFilter('attribute_set_id', $attribute_set_id);

                $questionData = $collection->getData();
            }
        } catch (\Exception $e) {
            $this->logger->error("An error occurred: " . $e->getMessage());
        }

        return $questionData;
    }

    protected function getQuestionData($current_question_id, $selected_option_id, $attribute_set_id)
    {
        if (is_array($selected_option_id)) {
            $selected_option_id = implode(',', $selected_option_id);
        }

        try {
            $quizModel = $this->productRecommendationQuizFactory->create();
            if (!empty($selected_option_id)) {

                $collection = $quizModel->getCollection()
                    ->addFieldToFilter('attribute_set_id', $attribute_set_id)
                    ->addFieldToFilter('question_id', $current_question_id)
                    ->addFieldToFilter('option_id', $selected_option_id);
            } else {
                $collection = $quizModel->getCollection()
                    ->addFieldToFilter('attribute_set_id', $attribute_set_id)
                    ->addFieldToFilter('question_id', $current_question_id);
            }

            if ($collection->getSize() > 0) {
                $questionData = $collection->getFirstItem();
            } else {
                $questionData = null;
                $this->logger->debug("No data found for question ID: $current_question_id and option ID: $selected_option_id");
            }
        } catch (\Exception $e) {
            $this->logger->error("An error occurred: " . $e->getMessage());
        }

        return $questionData;
    }

    protected function getNextQuestionData($current_question_id, $selected_option_id, $attribute_set_id)
    {

        try {
            $quizModel = $this->productRecommendationQuizFactory->create();

            $collection = $quizModel->getCollection()
                ->addFieldToFilter('attribute_set_id', $attribute_set_id)
                ->addFieldToFilter('question_id', $current_question_id);

            // print_r($collection->getData());

            if ($collection->getSize() > 0) {
                $questionData = $collection->getFirstItem();
            } else {
                $questionData = null;
                $this->logger->debug("No data found for question ID: $current_question_id and option ID: $selected_option_id");
            }
        } catch (\Exception $e) {
            $this->logger->error("An error occurred: " . $e->getMessage());
        }

        return $questionData;
    }

    public function getAttributeLabel($questionId)
    {
        try {
            $attribute = $this->eavAttribute->load($questionId);
            return $attribute->getDefaultFrontendLabel();
        } catch (\Exception $e) {
            $this->logger->error("An error occurred while getting attribute label: " . $e->getMessage());
            return null;
        }
    }

    public function getOptionsByQuestionId($questionId)
    {
        $options = [];
        try {
            $attributeModel = $this->eavAttribute->loadByCode(Product::ENTITY, $questionId);

            if ($attributeModel && $attributeModel->getId()) {
                $additionalData = $attributeModel->getAdditionalData();
                if (!empty($additionalData)) {
                    $additionalDataArray = json_decode($additionalData, true);
                    if (isset($additionalDataArray['swatch_input_type']) && $additionalDataArray['swatch_input_type'] === 'visual') {
                        $optionsData = $attributeModel->getSource()->getAllOptions();
                        foreach ($optionsData as $option) {
                            if (!empty($option['value'])) {
                                $optionId = $option['value'];
                                $swatchCollection = $this->swatchCollectionFactory->create();
                                $swatchCollection->addFieldtoFilter('option_id', $optionId);
                                $item = $swatchCollection->getFirstItem();
                                $images = $this->swatchMediaHelper->getSwatchAttributeImage('swatch_thumb', $item->getValue());
                                $options[] = [
                                    'value' => $option['value'],
                                    'label' => $option['label'],
                                    'images' => $images,
                                ];
                            }
                        }
                    }
                } else {
                    $optionsData = $attributeModel->getSource()->getAllOptions();
                    foreach ($optionsData as $option) {
                        if (!empty($option['value'])) {
                            $options[] = [
                                'value' => $option['value'],
                                'label' => $option['label'],
                            ];
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            $this->logger->error("An error occurred: " . $e->getMessage());
        }

        return $options;
    }


    public function getAttributeType($questionId)
    {
        try {
            $attribute = $this->eavAttribute->load($questionId);
            return $attribute->getFrontendInput();
        } catch (\Exception $e) {
            $this->logger->error("An error occurred while getting attribute label: " . $e->getMessage());
            return null;
        }
    }
}
