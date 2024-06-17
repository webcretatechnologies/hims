<?php

namespace Webcreta\ProductRecommendationQuiz\Ui\Component\Column;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Eav\Api\AttributeRepositoryInterface;

class QuestionName extends Column
{
    protected $productRepository;
    protected $searchCriteriaBuilder;
    protected $attributeRepository;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        ProductRepositoryInterface $productRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        AttributeRepositoryInterface $attributeRepository,
        array $components = [],
        array $data = []
    ) {
        $this->productRepository = $productRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->attributeRepository = $attributeRepository;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
                foreach ($dataSource['data']['items'] as &$item) {
                    $questionSet = json_decode($item[$this->getData('name')], true);
                    $questionNames = [];
                    foreach ($questionSet as $attributeId => $optionIds) {
                        $attributeLabel = $this->getAttributeLabel($attributeId);
                        
                        // Check if attribute type is select or multiselect
                        $attributeType = $this->getAttributeType($attributeId);
                        if ($attributeType == "select" || $attributeType == "multiselect") {
                            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                            $attribute = $objectManager->create(\Magento\Catalog\Model\ResourceModel\Eav\Attribute::class)->load($attributeId);
                            
                            // Ensure $optionIds is an array
                            $optionIds = is_array($optionIds) ? $optionIds : [$optionIds];
                            
                            $optionLabels = [];
                            foreach ($optionIds as $optionId) {
                                $options = $attribute->getSource()->getOptionText($optionId);
                                $optionLabels[] = $options ? $options : ''; // Get option label if it exists, otherwise set it to empty string
                            }
                            $optionLabel = implode(', ', $optionLabels);
                        } else {
                            if (is_array($optionIds)) {
                                $optionLabel = json_encode($optionIds);
                            } else {
                                $optionLabel = (string) $optionIds;
                            }
                        }
                        $questionNames[] = "$attributeLabel : $optionLabel<br>";
                    }
                    $item[$this->getData('name')] = implode(' ', $questionNames);
                }
                
            
        }
        return $dataSource;
    }
    
    protected function getAttributeLabel($attributeId)
    {
        try {
            $attribute = $this->attributeRepository->get('catalog_product', $attributeId);
            return $attribute->getDefaultFrontendLabel();
        } catch (\Exception $e) {
            return "Error: " . $e->getMessage();
        }
    }
    
    protected function getAttributeType($attributeId)
    {
        try {
            $attribute = $this->attributeRepository->get('catalog_product', $attributeId);
            return $attribute->getFrontendInput();
        } catch (\Exception $e) {
            return null; // Handle error, maybe log it or return a default value
        }
    }
    
}

