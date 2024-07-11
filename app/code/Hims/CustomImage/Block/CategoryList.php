<?php

namespace Hims\CustomImage\Block;

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
        $collection->addAttributeToSelect(['is_featured', 'cms_page_identifier', 'image', 'name','description','custom_image','url_key']); // Select specific attributes
        $collection->addIsActiveFilter();
        $collection->addFieldToFilter('entity_id', ['neq' => 2]);
        $collection->addFieldToFilter('is_featured', ['eq' => 0]);

        $categoriesData = [];
        foreach ($collection as $category) {
            $categoriesData[] = [
                'category_id' => $category->getId(),
                'name' => $category->getName(),
                'image' => $category->getImage(),
                'description' => $category->getDescription(),
                'is_featured' => $category->getData('is_featured'),
                'cms_page_identifier' => $category->getData('cms_page_identifier'),
                'custom_image' => $category->getData('custom_image'),
                'url_key' => $category->getData('url_key')
            ];
        }
    
        return $categoriesData;
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
