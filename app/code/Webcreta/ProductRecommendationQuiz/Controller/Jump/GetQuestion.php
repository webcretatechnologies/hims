<?php

namespace Webcreta\ProductRecommendationQuiz\Controller\Jump;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Webcreta\ProductRecommendationQuiz\Model\ProductRecommendationQuizFactory;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;

class GetQuestion extends Action
{
    protected $jsonFactory;
    protected $productRecommendationQuizFactory;
    protected $eavAttribute;

    public function __construct(
        Context $context,
        ProductRecommendationQuizFactory $productRecommendationQuizFactory,
        JsonFactory $jsonFactory,
        Attribute $eavAttribute
    ) {
        parent::__construct($context);
        $this->productRecommendationQuizFactory = $productRecommendationQuizFactory;
        $this->jsonFactory = $jsonFactory;
        $this->eavAttribute = $eavAttribute;
    }

    public function execute()
    {         
        $result = $this->jsonFactory->create();

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $logger = $objectManager->get('Psr\Log\LoggerInterface');
        
        $data = $this->getRequest()->getPostValue('categoryAttributeValue');
        
        if (!empty($data)) {

            $quizModel = $this->productRecommendationQuizFactory->create();
            $questionData = $quizModel->getCollection()
                ->addFieldToFilter('attribute_set_id', $data)
                ->addFieldToFilter('default_id', 1)
                ->getFirstItem();
            $questionName = $this->getAttributeLabel($questionData['question_id']);
            $questionOption = $this->getOptionsByQuestionId($questionData['question_id']);
            
            $inputType = $this->getAttributeType($questionData['question_id']);
            $logger->debug(print_r($questionName, true));
            $logger->debug(print_r($questionOption, true));
            $logger->debug(print_r($inputType, true));
            $responseData = [
                'question_id'=>$questionData['question_id'],
                'question' => $questionName, 
                'options' => $questionOption,
                'type' => $inputType
            ];
            return $result->setData(['success' => true, 'data' => $responseData]);

        } else {
            return $result->setData(['success' => false, 'error' => 'No data received']);
        }
    }

    public function getAttributeLabel($questionId)
    {

        $attribute = $this->eavAttribute->load($questionId);

        return $attribute->getDefaultFrontendLabel();
    }  
    
    public function getOptionsByQuestionId($questionId)
    {
        $options = [];

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        $logger = $objectManager->get(\Psr\Log\LoggerInterface::class);

        try {
        $attributeModel = $this->eavAttribute->load($questionId);
            if ($attributeModel && $attributeModel->getId()) {
                $optionsData = $attributeModel->getSource()->getAllOptions();

                foreach ($optionsData as $option) {
                    if (!empty($option['value'])) {
                        $options[] = [
                            'value' => $option['value'],
                            'label' => $option['label'],
                        ];
                    }
                }
            } else {
                $logger->info("Attribute with code {$questionId} not found.");
            }
        } catch (\Exception $e) {
            $logger->error("An error occurred: " . $e->getMessage());
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
