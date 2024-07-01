<?php

namespace GemsMed\CategoryName\Block;

use Magento\Framework\View\Element\Template;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Catalog\Model\CategoryRepository;
use Magento\Framework\Registry;

class CategoryList extends Template
{
    protected $categoryCollectionFactory;

    protected $categoryRepository;

    protected $registry;

    public function __construct(
        Template\Context $context,
        CollectionFactory $categoryCollectionFactory,
        CategoryRepository $categoryRepository,
        Registry $registry,
        array $data = []
    ) {
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->categoryRepository = $categoryRepository;
        $this->registry = $registry;
        parent::__construct($context, $data);
    }

    public function getCategoryCollection()
    {
        $collection = $this->categoryCollectionFactory->create();
        $collection->addAttributeToSelect('*');
        $collection->addIsActiveFilter();
        $collection->addFieldToFilter('entity_id', ['neq' => 2]);
        $collection->addFieldToFilter('is_featured', ['eq' => 0]);
        return $collection;
    }

    public function getCategoryCollectionWithIsFeatrured()
    {
        $collection = $this->categoryCollectionFactory->create();
        $collection->addAttributeToSelect('*');
        $collection->addIsActiveFilter();
        $collection->addFieldToFilter('entity_id', ['neq' => 2]);
        $collection->addFieldToFilter('is_featured', ['eq' => 1]);
        return $collection;
    }

    public function getCategoryName()
    {
        $product = $this->registry->registry('current_product');
        $categoryIds = $product->getCategoryIds();
        if (!empty($categoryIds)) {
            $category = $this->categoryRepository->get($categoryIds[0]);
            return $category->getName();
        }
        return '';
    }
}
