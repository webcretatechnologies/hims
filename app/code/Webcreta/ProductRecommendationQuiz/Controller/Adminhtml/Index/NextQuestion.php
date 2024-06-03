<?php

namespace Webcreta\ProductRecommendationQuiz\Controller\Adminhtml\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Eav\Api\AttributeSetRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory;

class NextQuestion extends Action
{
    protected $jsonFactory;
    protected $attributeSetRepository;
    protected $attributeCollectionFactory;

    public function __construct(
        Context $context,
        JsonFactory $jsonFactory,
        AttributeSetRepositoryInterface $attributeSetRepository,
        CollectionFactory $attributeCollectionFactory
    ) {
        $this->jsonFactory = $jsonFactory;
        $this->attributeSetRepository = $attributeSetRepository;
        $this->attributeCollectionFactory = $attributeCollectionFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $groupId = 125; 

        $attributeSetId = $this->getRequest()->getParam('custom_attribute_set');

        $attributeSet = $this->attributeSetRepository->get($attributeSetId);

        $om = \Magento\Framework\App\ObjectManager::	getInstance();
        $storeManager = $om->get('Psr\Log\LoggerInterface');
        $storeManager->log(100,print_r("i am ready",true));
        $storeManager->log(100,print_r($attributeSet->getData(),true));

        $attributeSetAttributes = $this->attributeCollectionFactory->create()
        ->setAttributeSetFilter($attributeSet->getId())
        ->addFieldToFilter('attribute_group_id', $groupId)
        ->getItems();

        $options = [];
        foreach ($attributeSetAttributes as $attribute) {
            $options[] = [
                'value' => $attribute->getAttributeId(),
                'label' => $attribute->getDefaultFrontendLabel(),
                'type' => $attribute->getFrontendInput(),
            ];
        }

        $result = $this->jsonFactory->create();
        $result->setData(['options' => $options]);
        return $result;
    }
}
