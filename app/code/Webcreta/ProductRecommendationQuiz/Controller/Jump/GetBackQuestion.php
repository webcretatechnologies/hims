<?php

namespace Webcreta\ProductRecommendationQuiz\Controller\Jump;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Webcreta\ProductRecommendationQuiz\Model\ProductRecommendationQuizFactory;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Psr\Log\LoggerInterface;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Swatches\Helper\Media;
use Magento\Swatches\Model\ResourceModel\Swatch\CollectionFactory;
use Magento\Catalog\Model\Product;

class GetBackQuestion extends Action
{
    protected $jsonFactory;
    protected $productRecommendationQuizFactory;
    protected $eavAttribute;
    protected $logger;
    protected $sessionManager;
    protected $swatchMediaHelper;
    protected $swatchCollectionFactory;

    public function __construct(
        Context $context,
        ProductRecommendationQuizFactory $productRecommendationQuizFactory,
        JsonFactory $jsonFactory,
        Attribute $eavAttribute,
        LoggerInterface $logger,
        SessionManagerInterface $sessionManager,
        Media $swatchMediaHelper,
        CollectionFactory $swatchCollectionFactory
    ) {
        parent::__construct($context);
        $this->productRecommendationQuizFactory = $productRecommendationQuizFactory;
        $this->jsonFactory = $jsonFactory;
        $this->eavAttribute = $eavAttribute;
        $this->logger = $logger;
        $this->sessionManager = $sessionManager;
        $this->swatchMediaHelper = $swatchMediaHelper;
        $this->swatchCollectionFactory = $swatchCollectionFactory;
    }

    public function execute()
    {
        $result = $this->jsonFactory->create();
        $data = $this->getRequest()->getPostValue();
        $this->logger->debug(print_r("GetNextQuestion", true));

        $this->logger->debug(print_r($data, true));

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
            if($type == 'text'){
                $questionData = $this->getLogicandquestionData($current_question_id, $selected_option_id, $attribute_set_id);

            }else{
                $questionData = $this->getQuestionData($current_question_id, $selected_option_id, $attribute_set_id);
            }
            if (!$questionData) {
                return $result->setData(['success' => false, 'error' => 'Question data not found']);
            }
    
            $next_question_id = $questionData['question_id'];
            $this->logger->debug(print_r($next_question_id, true));

            $questionName = $this->getAttributeLabel($next_question_id);
            $questionOption = $this->getOptionsByQuestionId($next_question_id);

            $attributeType = $this->getAttributeType($next_question_id);
            $this->logger->debug(print_r($questionName, true));
            $this->logger->debug(print_r($questionOption, true));

            // $attributeType = $this->getSwatchImages($next_question_id);

            if($questionData['question_id'] == 'final_question'){
                $productId = $questionData['product'];
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

                $productId = $questionData['product'];
                $product = $objectManager->create('Magento\Catalog\Model\Product')->load($productId);
                $productName = $product->getName();
                $productPrice = $product->getPrice();

                $productImageUrl = $objectManager->get('Magento\Catalog\Helper\Image')
                ->init($product, 'product_page_image_large')
                ->getUrl();

                $responseData = [
                    "final" => true,
                    "productName" =>$productName,
                    "productImage" =>$productImageUrl
                ];

            }else{
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
            $this->logger->error("An error occurred: " . $e->getMessage());
            return $result->setData(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    protected function getLogicandquestionData($current_question_id, $selected_option_id , $attribute_set_id)
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
            }else{
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
            $selected_option_id = implode(',' , $selected_option_id);
        }

        try {
            $quizModel = $this->productRecommendationQuizFactory->create();
            if (!empty($selected_option_id)) {
    
                $this->logger->debug(print_r("selected option value ", true));
                $this->logger->debug(print_r($selected_option_id, true));

                $collection = $quizModel->getCollection()
                    ->addFieldToFilter('attribute_set_id', $attribute_set_id)
                    ->addFieldToFilter('question_id', $current_question_id);
            } else {
                $collection = $quizModel->getCollection()
                    ->addFieldToFilter('attribute_set_id', $attribute_set_id)                             
                    ->addFieldToFilter('question_id', $current_question_id);
            }

            if ($collection->getSize() > 0) {
                $questionData = $collection->getFirstItem();
            } else {
                $questionData=null;
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
            $this->logger->debug(print_r($questionId, true));

            if ($attributeModel && $attributeModel->getId()) {
                $this->logger->debug(print_r($attributeModel->getId(), true));

                $additionalData = $attributeModel->getAdditionalData();
                // $this->logger->debug(print_r($additionalData->getData(), true));

                if (!empty($additionalData)) {
                    $additionalDataArray = json_decode($additionalData, true);
                    $this->logger->debug(print_r($additionalDataArray, true));

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
                    } else {
                        $optionsData = $attributeModel->getSource()->getAllOptions();
                        $this->logger->debug(print_r($optionsData, true));
    
                        foreach ($optionsData as $option) {
                            if (!empty($option['value'])) {
                                $options[] = [
                                    'value' => $option['value'],
                                    'label' => $option['label'],
                                ];
                            }
                        }
                    }
                } else {
                    $optionsData = $attributeModel->getSource()->getAllOptions();
                    $this->logger->debug(print_r($optionsData, true));

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
