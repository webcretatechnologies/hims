<?php
namespace Webcreta\ProductRecommendationQuiz\Block;

use Magento\Framework\View\Element\Template;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Eav\Api\AttributeSetRepositoryInterface;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory as AttributeGroupCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory as AttributeCollectionFactory;
use Webcreta\ProductRecommendationQuiz\Model\ProductRecommendationQuizFactory;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute as EavAttribute;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory;
use Webcreta\ProductRecommendationQuiz\Model\ProductRecommendationQuizCategoryFactory;

class GetCurrentCategory extends Template
{
    protected $layerResolver;
    protected $attributeSetRepository;
    protected $attributeGroupCollectionFactory;
    protected $productCollectionFactory;
    protected $attributeCollectionFactory;
    protected $productRecommendationQuizFactory;
    protected $eavAttribute;
    protected $productAttributeRepository;
    protected $attributeFactory;
    protected $productRecommendationQuizCategoryFactory;

    public function __construct(
        Template\Context $context,
        Resolver $layerResolver,
        AttributeSetRepositoryInterface $attributeSetRepository,
        AttributeGroupCollectionFactory $attributeGroupCollectionFactory,
        ProductCollectionFactory $productCollectionFactory,
        AttributeCollectionFactory $attributeCollectionFactory,
        ProductRecommendationQuizFactory $productRecommendationQuizFactory,
        EavAttribute $eavAttribute,
        ProductAttributeRepositoryInterface $productAttributeRepository,
        AttributeFactory $attributeFactory,
        ProductRecommendationQuizCategoryFactory $productRecommendationQuizCategoryFactory,
        array $data = []
    ) {
        $this->layerResolver = $layerResolver;
        $this->attributeSetRepository = $attributeSetRepository;
        $this->attributeGroupCollectionFactory = $attributeGroupCollectionFactory;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->attributeCollectionFactory = $attributeCollectionFactory;
        $this->productRecommendationQuizFactory = $productRecommendationQuizFactory;
        $this->eavAttribute = $eavAttribute;
        $this->productAttributeRepository = $productAttributeRepository;
        $this->attributeFactory = $attributeFactory;
        $this->productRecommendationQuizCategoryFactory = $productRecommendationQuizCategoryFactory;
        parent::__construct($context, $data);
    }

    public function getCurrentCategory()
    {
        return $this->layerResolver->get()->getCurrentCategory();
    }

    public function getCategoryStatus()
    {
        $currentCategory = $this->getCurrentCategory();
        $id  = $currentCategory['entity_id'];

        $collection = $this->productRecommendationQuizCategoryFactory->create()->getCollection()
        ->addFieldToFilter('category',$id);
    
        $data = $collection->getFirstItem()->getData();
        return $data;
    }
    
    public function getCategoryAttributeValue($attributeCode)
    {
        $currentCategory = $this->getCurrentCategory();

        if ($currentCategory) {
            return $currentCategory->getData($attributeCode);
        }
        return null;
    }

    public function getChooseCategoryQuizValue()
    {
        return $this->getCategoryAttributeValue('choose_category_quiz');
    }

    public function getQuestionData($yourAttributeSetId)
    {
        $quizModel = $this->productRecommendationQuizFactory->create();
        $questionData = $quizModel->getCollection()->addFieldToFilter('attribute_set_id', $yourAttributeSetId)->getFirstItem();
        return $questionData->getData();
    }

    

    public function getQuizQuestions($attributeSetId)
    {
        $om = \Magento\Framework\App\ObjectManager::	getInstance();
        $storeManager = $om->get('Psr\Log\LoggerInterface');
        $storeManager->log(100,print_r("i am getQuizQuestions",true));


        $collection = $this->productRecommendationQuizFactory->create()->getCollection();
        $collection->setOrder('set_id', 'ASC');
        $collection->addFieldToFilter('attribute_set_id', $attributeSetId);
        $uniqueQuestionIds = [];
        $uniqueData = [];
        
        foreach ($collection as $item) {
            $storeManager->log(100,print_r($item->getData(),true));
            $questionSetJson = $item->getData('question_set');
            $questionSetArray = json_decode($questionSetJson, true);
        }
        
        return $questionSetArray;
    }

    public function getAttributeLabel($attributeId)
    {
        $attribute = $this->eavAttribute->load($attributeId);
        return $attribute->getDefaultFrontendLabel();
    }

    public function getOptionsByQuestionId($questionId)
    {
        $options = [];

        try {
            /** @var Attribute $attributeModel */
            $attributeModel = $this->attributeFactory->create()->load($questionId);

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
                $this->logger->info("Attribute with code {$questionId} not found.");
            }
        } catch (\Exception $e) {
            // Log any exceptions
            $this->logger->error("An error occurred: " . $e->getMessage());
        }

        return $options;
    }
    
    public function getProductAttributeType($attributeCode)
    {
        try {
            $attribute = $this->productAttributeRepository->get($attributeCode);
            return $attribute->getFrontendInput();
        } catch (\Exception $e) {
            return null;
        }
    }

    public function getAllQuestions($questionId) 
    {
        $collection = $this->productRecommendationQuizFactory->create()->getCollection();
        $collection->addFieldToFilter('question_id', $questionId);

        $optionIds = [];
        foreach ($collection as $question) {
            $optionIds[] = [
                'option_id' => $question->getOptionId(),
                'next_question_id' => $question->getNextQuestionId(),
            ];
        }
        return $optionIds;

    }
}
