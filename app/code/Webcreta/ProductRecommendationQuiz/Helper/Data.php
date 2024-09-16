<?php

namespace Webcreta\ProductRecommendationQuiz\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Webcreta\ProductRecommendationQuiz\Model\ProductRecommendationQuizCategoryFactory;
use Webcreta\ProductRecommendationQuiz\Model\ProductRecommendationQuizFactory;
use Webcreta\ProductRecommendationQuiz\Model\ProductRecommendationQuizDataFactory;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute as EavAttribute;
use Magento\Swatches\Model\ResourceModel\Swatch\CollectionFactory as SwatchCollectionFactory;
use Magento\Swatches\Helper\Media as SwatchMediaHelper;
use Magento\Catalog\Model\Product;
use Psr\Log\LoggerInterface;

class Data extends AbstractHelper
{
    /**
     * @var CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * @var ProductRecommendationQuizCategoryFactory
     */
    protected $productRecommendationQuizCategoryFactory;

    /**
     * @var ProductRecommendationQuizFactory
     */
    protected $productRecommendationQuizFactory;

    /**
     * @var ProductRecommendationQuizDataFactory
     */
    protected $productRecommendationQuizDataFactory;
    
    /**
    * @var EavAttribute
    */
    protected $eavAttribute;

    /**
    * @var SwatchCollectionFactory
    */
    protected $swatchCollectionFactory;

    /**
    * @var SwatchMediaHelper
    */
    protected $swatchMediaHelper;
    
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * Data constructor.
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param CategoryRepositoryInterface $categoryRepository
     * @param ProductRecommendationQuizCategoryFactory $productRecommendationQuizCategoryFactory
     * @param ProductRecommendationQuizFactory $productRecommendationQuizFactory
     * @param EavAttribute $eavAttribute
     * @param SwatchCollectionFactory $swatchCollectionFactory
     * @param SwatchMediaHelper $swatchMediaHelper
     * @param ProductRecommendationQuizDataFactory $productRecommendationQuizDataFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        CategoryRepositoryInterface $categoryRepository,
        ProductRecommendationQuizCategoryFactory $productRecommendationQuizCategoryFactory,
        ProductRecommendationQuizFactory $productRecommendationQuizFactory,
        EavAttribute $eavAttribute,
        SwatchCollectionFactory $swatchCollectionFactory,
        SwatchMediaHelper $swatchMediaHelper,
        ProductRecommendationQuizDataFactory $productRecommendationQuizDataFactory,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->categoryRepository = $categoryRepository;
        $this->productRecommendationQuizCategoryFactory = $productRecommendationQuizCategoryFactory;
        $this->productRecommendationQuizFactory = $productRecommendationQuizFactory;
        $this->eavAttribute = $eavAttribute;
        $this->swatchCollectionFactory = $swatchCollectionFactory;
        $this->swatchMediaHelper = $swatchMediaHelper;
        $this->productRecommendationQuizDataFactory = $productRecommendationQuizDataFactory;
        $this->logger = $logger;
    }

    /**
     * Get category attribute value by attribute code and category ID.
     *
     * @param string $attributeCode
     * @param int $id
     * @return mixed|null
     */
    public function getCategoryAttributeValue($attributeCode, $id)
    {
        try {
            $currentCategory = $this->categoryRepository->get($id);

            if ($currentCategory) {
                return $currentCategory->getData($attributeCode);
            }
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            // Handle the exception if category not found
        } catch (\Exception $e) {
            // Handle other possible exceptions
        }
        return null;
    }

    /**
     * Get 'choose_category_quiz' attribute value for a category by its ID.
     *
     * @param int $id
     * @return mixed|null
     */
    public function getChooseCategoryQuizValue($id)
    {
        return $this->getCategoryAttributeValue('choose_category_quiz', $id);
    }

    /**
     * Get the status of a category based on the product recommendation quiz.
     *
     * @param int $id
     * @return array|null
     */
    public function getCategoryStatus($id)
    {
        $collection = $this->productRecommendationQuizCategoryFactory->create()->getCollection()
            ->addFieldToFilter('category', $id);
    
        $data = $collection->getFirstItem()->getData();
        return $data;
    }

    /**
     * Get question data based on attribute set ID.
     *
     * @param int $attributeSetId
     * @return array|null
     */
    public function getQuestionData($attributeSetId)
    {
        $quizModel = $this->productRecommendationQuizFactory->create();
        $questionData = $quizModel->getCollection()
            ->addFieldToFilter('attribute_set_id', $attributeSetId)
            ->getFirstItem();
        return $questionData->getData();
    }

    // get attribute type
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

    public function getLogicandquestionData($currentQuestionId, $selectedOptionId, $attributeSetId)
    {
        $questionData = [];

        try {
            $quizModel = $this->productRecommendationQuizFactory->create();
            if (!empty($selectedOptionId)) {
                $collection = $quizModel->getCollection()
                    ->addFieldToFilter('question_id', $currentQuestionId)
                    ->addFieldToFilter('attribute_set_id', $attributeSetId);

                $options = $collection->getItems();
                foreach ($options as $option) {
                    $value = $option->getOptionId();

                    $conditionString = "$selectedOptionId $value";
                    $condition = eval("return $conditionString;");

                    if ($condition) {
                        $questionData = $option->getData();
                        break;
                    }
                }
            } else {
                $collection = $quizModel->getCollection()
                    ->addFieldToFilter('question_id', $currentQuestionId)
                    ->addFieldToFilter('attribute_set_id', $attributeSetId);

                $questionData = $collection->getData();
            }
        } catch (\Exception $e) {
            $this->logger->error("An error occurred: " . $e->getMessage());
        }

        return $questionData;
    }

    public function getNextQuestionData($currentQuestionId, $selectedOptionId, $attributeSetId, $groupId)
    {

        try {
            $quizModel = $this->productRecommendationQuizFactory->create();

            $collection = $quizModel->getCollection()
                ->addFieldToFilter('attribute_set_id', $attributeSetId)
                ->addFieldToFilter('question_id', $currentQuestionId);
                
                $validQuestions = [];
                $remainingGroupIds = [];
            
                $groupIds = explode(',', $groupId);

                foreach ($groupIds as $key => $groupId) {
                    $filteredCollection = clone $collection; 
                    $filteredCollection->addFieldToFilter('group_id', $groupId);
            
                    if ($filteredCollection->getSize() > 0) {
                        $validQuestions[] = $filteredCollection->getFirstItem();
                        $remainingGroupIds[] = $groupId;
                    }
                }

            if ($collection->getSize() > 0) {
                $questionData = $collection->getFirstItem();
            } else {
                $questionData = null;
                $this->logger->debug("No data found for question ID: $currentQuestionId and option ID: $selectedOptionId");
            }
            $questionData['group_ids'] = $remainingGroupIds;

        } catch (\Exception $e) {
            $this->logger->error("An error occurred: " . $e->getMessage());
        }
        return $questionData;
    }

    public function getnextQuestion($currentQuestionId, $selectedOptionId, $attributeSetId, $groupId)
    {
        $finalCollection = [];
        if (is_array($selectedOptionId)) {
            $selectedOptionId = implode(',', $selectedOptionId);
        }
              
        try {
            $quizModel = $this->productRecommendationQuizFactory->create();
            if (!empty($selectedOptionId)) {

                $collection = $quizModel->getCollection()
                    ->addFieldToFilter('attribute_set_id', $attributeSetId)
                    ->addFieldToFilter('question_id', $currentQuestionId)
                    ->addFieldToFilter('option_id', $selectedOptionId);

                $finalCollection = $quizModel->getCollection()
                    ->addFieldToFilter('attribute_set_id', $attributeSetId)
                    ->addFieldToFilter('question_id', $currentQuestionId)
                    ->addFieldToFilter('option_id', $selectedOptionId);
            } else {
                $collection = $quizModel->getCollection()
                    ->addFieldToFilter('attribute_set_id', $attributeSetId)
                    ->addFieldToFilter('question_id', $currentQuestionId);
            }
           
            if ($collection->getSize() > 1) {
                $questionData = $collection->getFirstItem();
                if($groupId == 1){
                    $finalCollection->addFieldToFilter('group_id', $groupId);
                    $questionData = $finalCollection->getSize() > 0 ? $finalCollection->getFirstItem() : $collection->getFirstItem();
                }else{
                    $collection->addFieldToFilter('group_id', $groupId);
                    $items = $collection;
                    foreach($items as $item){
                        if($item->getData('group_id') == $groupId ){
                            $questionData = $item;
                        }
                    }
                }
            }else if ($collection->getSize() > 0) {
                $questionData = $collection->getFirstItem();
            } else {
                $questionData = null;
                $this->logger->debug("No data found for question ID: $currentQuestionId and option ID: $selectedOptionId");
            }
        } catch (\Exception $e) {
            $this->logger->error("An error occurred: " . $e->getMessage());
        }
        return $questionData;
    }

    // get question name
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

    public function getOptionsByQuestionId($questionId,$attributeSetId)
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
                                $groupid = $this->getGroupIdByOptionAndQuestion($option['value'], $questionId,$attributeSetId);
                                
                                $optionId = $option['value'];
                                $swatchCollection = $this->swatchCollectionFactory->create();
                                $swatchCollection->addFieldtoFilter('option_id', $optionId);
                                $item = $swatchCollection->getFirstItem();
                                $images = $this->swatchMediaHelper->getSwatchAttributeImage('swatch_thumb', $item->getValue());
                                $options[] = [
                                    'value' => $option['value'],
                                    'label' => $option['label'],
                                    'images' => $images,
                                    'groupid' => $groupid, // Add the group ID here
                                ];
                            }
                        }
                    } else {
                        $optionsData = $attributeModel->getSource()->getAllOptions();
    
                        foreach ($optionsData as $option) {
                            if (!empty($option['value'])) {
                                // Fetch group ID from the custom table
                                $groupid = $this->getGroupIdByOptionAndQuestion($option['value'], $questionId, $attributeSetId);
    
                                $options[] = [
                                    'value' => $option['value'],
                                    'label' => $option['label'],
                                    'groupid' => $groupid, // Add the group ID here
                                ];
                            }
                        }
                    }
                } else {
                    $optionsData = $attributeModel->getSource()->getAllOptions();

                    foreach ($optionsData as $option) {
                        if (!empty($option['value'])) {
                            $groupid = $this->getGroupIdByOptionAndQuestion($option['value'], $questionId, $attributeSetId);
    
                            $options[] = [
                                'value' => $option['value'],
                                'label' => $option['label'],
                                'groupid' => $groupid,
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

    private function getGroupIdByOptionAndQuestion($optionValue, $questionId, $attributeSetId)
{
    // Fetch the group ID from the custom table based on the option value and question ID
    $quizModel = $this->productRecommendationQuizFactory->create();
    $collection = $quizModel->getCollection()
        ->addFieldToFilter('attribute_set_id', $attributeSetId)
        ->addFieldToFilter('question_id', $questionId)
        ->addFieldToFilter('option_id', $optionValue);

    
        $groupIds = [];
        foreach ($collection as $quizItem) {
            $groupIds[] = $quizItem->getGroupId(); // Replace 'getGroupId' with the correct method to get the group ID
        }  
    return $groupIds; // Replace 'getGroupId' with the correct method to get the group ID
}
    // save customer data in database
    public function saveQuizData($customerId, $currentQuestionId, $selectedOptionId, $attributeSetId, $nextQuestionId, $productName)
    {
        $questionSet = json_encode([$currentQuestionId => $selectedOptionId]);

        if ($customerId) {
            $quizDataModel = $this->productRecommendationQuizDataFactory->create();
            $existingRecord = $quizDataModel->getCollection()
                ->addFieldToFilter('customer_id', $customerId)
                ->addFieldToFilter('category', $attributeSetId)
                ->getFirstItem();

            if ($existingRecord->getId() && $existingRecord->getQuestionSet()) {
                if ($nextQuestionId == 'final_question') {
                    $existingRecord->setData('product', $productName);
                } else {
                    $existingRecord->setData('product', "currently not define");
                }

                $existingQuestionSet = json_decode($existingRecord->getData('question_set'), true);
                $existingQuestionSet[$currentQuestionId] = $selectedOptionId;

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
                $quizDataModel->setData('category', $attributeSetId);
                $quizDataModel->setData('product', "currently not define");

                try {
                    $quizDataModel->save();
                } catch (\Exception $e) {
                    $this->logger->critical($e);
                }
            }
        }
    }

    public function getGroupIds($questionId)
    {
        try {
            $quizDataCollection = $this->productRecommendationQuizFactory->create();

            $existingRecord = $quizDataCollection->getCollection()
                ->addFieldToFilter('question_id', $questionId)
                ->getFirstItem();

            if ($existingRecord->getId()) {
                return $existingRecord->getData('group_id');
            } else {
                return null;
            }
        } catch (\Exception $e) {
            $this->logger->error("An error occurred while getting the group ID: " . $e->getMessage());
            return null;
        }
    }
    public function getOptionsByQuestionIds($type,$questionId,$attributeSetId,$groupId)
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
                                // Fetch group ID from the custom table
                                $groupid = $this->getGroupIdByOptionAndQuestions($type, $option['value'], $questionId,$attributeSetId,$groupId);
                                
                                $optionId = $option['value'];
                                $swatchCollection = $this->swatchCollectionFactory->create();
                                $swatchCollection->addFieldtoFilter('option_id', $optionId);
                                $item = $swatchCollection->getFirstItem();
                                $images = $this->swatchMediaHelper->getSwatchAttributeImage('swatch_thumb', $item->getValue());
                                $options[] = [
                                    'value' => $option['value'],
                                    'label' => $option['label'],
                                    'images' => $images,
                                    'groupid' => $groupid, // Add the group ID here
                                ];
                            }
                        }
                    } else {
                        $optionsData = $attributeModel->getSource()->getAllOptions();
                        
                        foreach ($optionsData as $option) {
                            if (!empty($option['value'])) {
                                // Fetch group ID from the custom table
                                $groupid = $this->getGroupIdByOptionAndQuestion($option['value'], $questionId, $attributeSetId,$groupId);
    
                                $options[] = [
                                    'value' => $option['value'],
                                    'label' => $option['label'],
                                    'groupid' => $groupid, // Add the group ID here
                                ];
                            }
                        }

                    }
                } else {
                    $optionsData = $attributeModel->getSource()->getAllOptions();
                    
                    foreach ($optionsData as $option) {
                        if (!empty($option['value'])) {
                            // Fetch group ID from the custom table
                            $groupid = $this->getGroupIdByOptionAndQuestions($type, $option['value'], $questionId, $attributeSetId,$groupId);
                            
                            $options[] = [
                                'value' => $option['value'],
                                'label' => $option['label'],
                                'groupid' => $groupid, 
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
    public function getGroupIdByOptionAndQuestions($type, $optionValue, $questionId, $attributeSetId, $groupId)
    {
        $quizModel = $this->productRecommendationQuizFactory->create();
        $collection = $quizModel->getCollection()
        ->addFieldToFilter('attribute_set_id', $attributeSetId)
        ->addFieldToFilter('question_id', $questionId);
        $collection->addFieldToFilter('option_id', ['like' => '%' . $optionValue . '%']);
        
        $validQuestions = [];
        $remainingGroupIds = [];

        $groupIds = explode(',', $groupId);

        foreach ($groupIds as $key => $groupId) {
            $filteredCollection = clone $collection;
            $filteredCollection->addFieldToFilter('group_id', $groupId);

            if ($filteredCollection->getSize() > 0) {
                $validQuestions[] = $filteredCollection->getFirstItem();
                $remainingGroupIds[] = $groupId;
            }
        }
        return !empty($remainingGroupIds) ? $remainingGroupIds : null;
    }


}
