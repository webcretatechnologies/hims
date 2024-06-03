<?php
namespace Webcreta\ProductRecommendationQuiz\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Eav\Model\Entity\Attribute\SetFactory;
use Webcreta\ProductRecommendationQuiz\Model\ProductRecommendationQuizFactory;
use Magento\Eav\Api\AttributeSetRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Webcreta\ProductRecommendationQuiz\Model\ProductRecommendationQuizCategoryFactory;

class GetAttribute extends Template
{
    protected $attributeSetFactory;
    protected $ProductRecommendationQuizFactory;
    protected $ProductRecommendationQuizCategoryFactory;
    protected $attributeSetRepository;
    protected $attributeCollectionFactory;
    protected $_productCollectionFactory;

    public function __construct(
        Context $context,
        SetFactory $attributeSetFactory,
        ProductRecommendationQuizFactory $ProductRecommendationQuizFactory,
        AttributeSetRepositoryInterface $attributeSetRepository,
        CollectionFactory $attributeCollectionFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        ProductRecommendationQuizCategoryFactory $ProductRecommendationQuizCategoryFactory,
        array $data = []
    ) {
        $this->attributeSetFactory = $attributeSetFactory;
        $this->ProductRecommendationQuizFactory = $ProductRecommendationQuizFactory;
        $this->attributeSetRepository = $attributeSetRepository;
        $this->attributeCollectionFactory = $attributeCollectionFactory;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->ProductRecommendationQuizCategoryFactory = $ProductRecommendationQuizCategoryFactory;
        parent::__construct($context, $data);
    }

    public function getCustomAttributeSets()
    {
        $attributeSets = $this->attributeSetFactory->create()->getCollection();
        $options = [];
        foreach ($attributeSets as $attributeSet) {
            $options[$attributeSet->getId()] = $attributeSet->getAttributeSetName();
        }
        return $options;
    }

    public function getProductQuestionData($id)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $logger = $objectManager->get(\Psr\Log\LoggerInterface::class);
        $logger->log(100, 'getProductQuestionData() function is being called');
        $logger->log(100, 'id: ' . $id);

        $collection = $this->ProductRecommendationQuizFactory->create()->getCollection();
        $collection->addFieldToFilter('id', $id);
        
        $collection->load(); 


        $formData = [];

        $logger->log(100, 'Number of items in collection: ' . $collection->getSize());

        foreach ($collection as $item) {
            $logger->log(100, 'questionSetArray: ');
            $productName = $item->getProduct();
            $attributeSetId = $item->getAttributeSetId();
        
            $questionSet = $item->getQuestionSet();
            $logger->log(100, 'questionSet: ' . print_r($questionSet, true));


            $questionSetArray = json_decode($questionSet, true);
            $logger->log(100, 'questionSetArray: ' . print_r($questionSetArray, true));

            $logger->log(100, 'Product Name for Item ID ' . $item->getId() . ': ' . $productName);
            $logger->log(100, 'Attribute Set ID for Item ID ' . $item->getId() . ': ' . $attributeSetId);
        
            $formData[] = [
                'id' => $item->getId(),
                'product' => $productName,
                'set_id' => $item->getSetId(),
                'attributeSetId' => $attributeSetId,
                'questionSet' => $questionSetArray
            ];
        }

        $logger->log(100, 'Product Question Data: ' . print_r($formData, true));
        
        return $formData;
    }

    

    public function  getQuestion($attributeSetId)
    {
    
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); 
        $productCollection = $objectManager->create('Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\Collection'); 
        $productCollection->addFieldToFilter('attribute_set_id',$attributeSetId); 
        $productCollection->load(); 

       
        $attributeSet = $this->attributeSetRepository->get($attributeSetId);
        $attributeSetName = $attributeSet->getAttributeSetName();

        foreach ($productCollection as $product) {
            $productData = $product->getData();
            $attributeGroupName = $productData['attribute_group_name']; // Assuming this is the field name

            if ($attributeGroupName === $attributeSetName) {
                $attributeGroupId = $productData['attribute_group_id']; // Assuming this is the field name
               
                break; 
            }
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
        $om = \Magento\Framework\App\ObjectManager::	getInstance();
        $storeManager = $om->get('Psr\Log\LoggerInterface');
        $storeManager->log(100,print_r("i am getProductData",true));
        $storeManager->log(100,print_r($id,true));

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



    public function getOptionsByQuestionId($questionId)
    {
        $options = [];

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        $logger = $objectManager->get(\Psr\Log\LoggerInterface::class);

        try {
            $attributeModel = $objectManager->create(\Magento\Catalog\Model\ResourceModel\Eav\Attribute::class)->load($questionId);


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
}
