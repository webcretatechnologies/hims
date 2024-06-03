<?php
namespace Webcreta\ProductRecommendationQuiz\Block\Adminhtml\Jump;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Eav\Model\Entity\Attribute\SetFactory;
use Webcreta\ProductRecommendationQuiz\Model\ProductRecommendationQuizFactory;
use Webcreta\ProductRecommendationQuiz\Model\ProductRecommendationQuizCategoryFactory;
use Magento\Eav\Api\AttributeSetRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory as AttributeGroupCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Psr\Log\LoggerInterface;

class GetQuestion extends Template
{
    protected $attributeSetFactory;
    protected $ProductRecommendationQuizFactory;
    protected $attributeSetRepository;
    protected $attributeCollectionFactory;
    protected $_productCollectionFactory;
    protected $ProductRecommendationQuizCategoryFactory;
    protected $categoryFactory;
    protected $attributeGroupCollectionFactory;
    protected $attributeFactory;
    protected $attributeRepository;
    protected $logger;

    public function __construct(
        Context $context,
        SetFactory $attributeSetFactory,
        ProductRecommendationQuizFactory $ProductRecommendationQuizFactory,
        AttributeSetRepositoryInterface $attributeSetRepository,
        CollectionFactory $attributeCollectionFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        ProductRecommendationQuizCategoryFactory $ProductRecommendationQuizCategoryFactory,
        CategoryFactory $categoryFactory,
        AttributeGroupCollectionFactory $attributeGroupCollectionFactory,
        AttributeFactory $attributeFactory,
        ProductAttributeRepositoryInterface $attributeRepository,
        LoggerInterface $logger,
        array $data = []
    ) {
        $this->attributeSetFactory = $attributeSetFactory;
        $this->ProductRecommendationQuizFactory = $ProductRecommendationQuizFactory;
        $this->attributeSetRepository = $attributeSetRepository;
        $this->attributeCollectionFactory = $attributeCollectionFactory;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->ProductRecommendationQuizCategoryFactory = $ProductRecommendationQuizCategoryFactory;
        $this->categoryFactory = $categoryFactory;
        $this->attributeGroupCollectionFactory = $attributeGroupCollectionFactory;
        $this->attributeFactory = $attributeFactory;
        $this->attributeRepository = $attributeRepository;
        $this->logger = $logger;
        parent::__construct($context, $data);
    }

    public function getCategoryAttributeValue($id)
    {
        $quizCategoryCollection = $this->ProductRecommendationQuizCategoryFactory->create()->getCollection();
        $quizCategoryCollection->addFieldToFilter('id', $id);
        if ($quizCategoryCollection->getSize() > 0) {
            $categoryItem = $quizCategoryCollection->getFirstItem();
            $categoryId = $categoryItem->getCategory(); 
        }

        $category = $this->categoryFactory->create()->load($categoryId);
        if ($category->getId()) {
            return $category->getData('choose_category_quiz');
        }

        return null; 
    }
 
    public function getQuestion($id)
    {
        $attributeValue = $this->getCategoryAttributeValue($id);

        $productCollection = $this->attributeGroupCollectionFactory->create();
        $productCollection->addFieldToFilter('attribute_set_id', $attributeValue);
        $productCollection->load();

        $attributeSet = $this->attributeSetRepository->get($attributeValue);
        $attributeSetName = $attributeSet->getAttributeSetName();

        $attributeGroupId = null;
        foreach ($productCollection as $product) {
            $productData = $product->getData();
            $attributeGroupName = $productData['attribute_group_name']; // Assuming this is the field name

            if ($attributeGroupName === $attributeSetName) {
                $attributeGroupId = $productData['attribute_group_id']; // Assuming this is the field name
                break;
            }
        }

        if ($attributeGroupId === null) {
            return []; // Handle the case where the attribute group was not found
        }

        $attributeSetAttributes = $this->attributeCollectionFactory->create()
            ->setAttributeSetFilter($attributeSet->getId())
            ->addFieldToFilter('attribute_group_id', $attributeGroupId)
            ->getItems();

        $options = [];
        foreach ($attributeSetAttributes as $attribute) {
            $options[] = [
                'value' => $attribute->getAttributeId(),
                'label' => $attribute->getDefaultFrontendLabel(),
            ];
        }

        return $options;
    }


    public function getProductData($id)
    {
        
        $collection = $this->ProductRecommendationQuizCategoryFactory->create()->getCollection();
        $collection->addFieldToFilter('id', $id);
        
        $collection->load(); 
        $categoryData = $collection->getFirstItem();
        $category_id = $categoryData->getCategory();

        $productCollectionFactory = $this->_productCollectionFactory->create()
        ->addAttributeToSelect('*')
        ->addCategoriesFilter(['in' => $category_id])
        ->load();
    
        return $productCollectionFactory;
    }

    public function getProductQuestionData($id)
    {
        $attributeValue = $this->getCategoryAttributeValue($id);

        $collection = $this->ProductRecommendationQuizFactory->create()->getCollection();
        $collection->addFieldToFilter('attribute_set_id',$attributeValue); 

        $formData = [];
        foreach ($collection as $item) {
            $formData[] = [
                'id' => $item->getId(),
                'attribute_set_id' => $attributeValue,
                'question_id' => $item->getQuestionId(),
                'sort_order_id' => $item->getSortOrderId(),
                'default_id' => $item->getDefaultId(),
                'condition_id' => $item->getConditionId(),
                'option_id' => $item->getOptionId(),
                'next_question_id' => $item->getNextQuestionId(),
                'product' => $item->getProduct(),
            ];

        }

        return $formData;
    }

    public function getOptionsByQuestionId($questionId)
    {
        $options = [];

        try {
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
            $this->logger->error("An error occurred: " . $e->getMessage());
        }

        return $options;
    }

    public function getMultipleType($questionId)
    {
        $type = '';
        try {
            $attributeModel = $this->attributeRepository->get($questionId);
            $type = $attributeModel->getFrontendInput();
        } catch (\Exception $e) {
            $this->logger->error("An error occurred: " . $e->getMessage());
        }
        return $type;
    }


}
