<?php

namespace GemsMed\Productslidercmspage\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\App\Request\Http;
use Magento\Framework\UrlInterface;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;

class Productslidercms extends Template
{
    protected $productCollectionFactory;
    protected $categoryRepository;
    protected $categoryCollectionFactory;
    protected $urlBuilder;


    public function __construct(
        Template\Context $context,
        UrlInterface $urlBuilder,
        CategoryRepositoryInterface $categoryRepository,
        CategoryCollectionFactory $categoryCollectionFactory,
        ProductCollectionFactory $productCollectionFactory,
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->categoryRepository = $categoryRepository;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->productCollectionFactory = $productCollectionFactory;
        parent::__construct($context, $data);
    }

    public function getCurrentUrl()
    {
        $segments = explode("/", $this->urlBuilder->getCurrentUrl());
        $last_segment = end($segments);
        return $last_segment;
    }

    public function getProductCollectionByCategoryId($categoryId)
    {
        $collection = $this->productCollectionFactory->create();
        $collection->addAttributeToSelect('*');
        $collection->addCategoriesFilter(['in' => $categoryId]);
        $collection->addAttributeToFilter('status', ['eq' => \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED]);
        $collection->addAttributeToFilter('visibility', ['neq' => \Magento\Catalog\Model\Product\Visibility::VISIBILITY_NOT_VISIBLE]);
        return $collection;
    }

    public function getCategoryByUrlKey($urlKey)
    {
        $categoryCollection = $this->categoryCollectionFactory->create();
        $categoryCollection->addAttributeToFilter('url_key', $urlKey);
        $category = $categoryCollection->getFirstItem();
        if ($category && $category->getId()) {
            return $this->categoryRepository->get($category->getId());
        }
        return false;
    }
}