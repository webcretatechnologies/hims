<?php

namespace Webcreta\ProductRecommendationQuiz\Model\Config;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;

/**
 * Class CategoryList
 */
class CategoryList implements OptionSourceInterface
{
    /**
     * @var CategoryCollectionFactory
     */
    protected $categoryCollectionFactory;

    /**
     * CategoryList constructor.
     *
     * @param CategoryCollectionFactory $categoryCollectionFactory
     */
    public function __construct(CategoryCollectionFactory $categoryCollectionFactory)
    {
        $this->categoryCollectionFactory = $categoryCollectionFactory;
    }

    public function toOptionArray()
    {
        $options = [['label' => __('Please Select a Category'), 'value' => '']];
        $categoryCollection = $this->categoryCollectionFactory->create();
        $categoryCollection->addAttributeToSelect(['name', 'path']);
    
        foreach ($categoryCollection as $category) {
            $level = $category->getLevel();
            $indentation = str_repeat('--', $level);
    
            $options[] = [
                'label' => $indentation . $category->getName(),
                'value' => $category->getId(),
            ];
        }
    
        return $options;
    }
}
