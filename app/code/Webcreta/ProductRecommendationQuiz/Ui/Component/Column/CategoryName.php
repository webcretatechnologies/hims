<?php

namespace Webcreta\ProductRecommendationQuiz\Ui\Component\Column;

use Magento\Catalog\Model\CategoryFactory;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\Listing\Columns\Column;

class CategoryName extends Column
{
    protected $categoryFactory;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        CategoryFactory $categoryFactory,
        array $components = [],
        array $data = []
    ) {
        $this->categoryFactory = $categoryFactory;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $categoryId = $item[$this->getData('name')];
                $category = $this->categoryFactory->create()->load($categoryId);
                $categoryName = $category->getName();
                $item[$this->getData('name')] = $categoryName;
            }
        }
        return $dataSource;
    }
}
