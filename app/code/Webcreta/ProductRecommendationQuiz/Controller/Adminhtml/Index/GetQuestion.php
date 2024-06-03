<?php

namespace Webcreta\ProductRecommendationQuiz\Controller\Adminhtml\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Eav\Api\AttributeSetRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory;
use Psr\Log\LoggerInterface;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory as AttributeGroupCollectionFactory;

class GetQuestion extends Action
{
    protected $jsonFactory;
    protected $attributeSetRepository;
    protected $attributeCollectionFactory;
    protected $logger;
    protected $attributeGroupCollectionFactory;

    public function __construct(
        Context $context,
        JsonFactory $jsonFactory,
        AttributeSetRepositoryInterface $attributeSetRepository,
        CollectionFactory $attributeCollectionFactory,
        LoggerInterface $logger,
        AttributeGroupCollectionFactory $attributeGroupCollectionFactory,
    ) {
        $this->jsonFactory = $jsonFactory;
        $this->attributeSetRepository = $attributeSetRepository;
        $this->attributeCollectionFactory = $attributeCollectionFactory;
        $this->logger = $logger;
        $this->attributeGroupCollectionFactory = $attributeGroupCollectionFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        try {
            $om = \Magento\Framework\App\ObjectManager::	getInstance();
            $storeManager = $om->get('Psr\Log\LoggerInterface');
            $storeManager->log(100,print_r("i am execute",true));
            
            $groupId = 113;
            
            $attributeSetId = $this->getRequest()->getParam('custom_attribute_set');
            
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); 
            $productCollection = $objectManager->create('Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\Collection'); 
            $productCollection->addFieldToFilter('attribute_set_id',$attributeSetId); 
            $productCollection->load(); 
           


            $storeManager->log(100,print_r($attributeSetId,true));

            if (!$attributeSetId) {
                throw new \InvalidArgumentException("Attribute set ID is missing.");
            }
            
            $attributeSet = $this->attributeSetRepository->get($attributeSetId);
            
              // Retrieve attribute set name
              $attributeSetName = $attributeSet->getAttributeSetName();
              $storeManager->log(100,print_r($attributeSetName,true));
              foreach ($productCollection as $product) {
                $productData = $product->getData();
                $attributeGroupName = $productData['attribute_group_name']; // Assuming this is the field name
                $storeManager->log(100, "attributeGroupName Group ID: " . $attributeGroupName);

                if ($attributeGroupName === $attributeSetName) {
                    $attributeGroupId = $productData['attribute_group_id']; // Assuming this is the field name
                    $storeManager->log(100, "Matching Attribute Group ID: " . $attributeGroupId);
                   
                    break; 
                }
            }

            if (!$attributeSet->getId()) {
                throw new \InvalidArgumentException("Invalid attribute set ID: $attributeSetId.");
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
            return $this->jsonFactory->create()->setData($options);

        } catch (\InvalidArgumentException $e) {
            // Log the error
            $errorMessage = $e->getMessage();
            $this->logger->error($errorMessage);
            
            // Handle the error as needed, for example, return an empty JSON response
            $result = $this->jsonFactory->create();
            $result->setData([]);
            return $result;
        } catch (\Exception $e) {
            // Log any other unexpected exceptions
            $errorMessage = "Unexpected error: " . $e->getMessage();
            $this->logger->error($errorMessage);
            
            // Return a generic error message
            $result = $this->jsonFactory->create();
            $result->setData(['error' => 'An unexpected error occurred.']);
            return $result;
        }
    }
}
