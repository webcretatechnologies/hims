<?php

namespace GemsMed\CategoryName\Block;

use Magento\Framework\View\Element\Template;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;

class CategoryList extends Template
{
    protected $categoryCollectionFactory;

    public function __construct(
        Template\Context $context,
        CollectionFactory $categoryCollectionFactory,
        array $data = []
    ) {
        $this->categoryCollectionFactory = $categoryCollectionFactory;
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
}
