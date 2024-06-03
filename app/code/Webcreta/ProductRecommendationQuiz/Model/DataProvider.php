<?php

namespace Webcreta\ProductRecommendationQuiz\Model;

use Webcreta\ProductRecommendationQuiz\Model\ResourceModel\ProductRecommendationQuizCategory\CollectionFactory;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Magento\Store\Model\StoreManagerInterface;

class DataProvider extends AbstractDataProvider
{
    protected $loadedData;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        StoreManagerInterface $storeManager,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collectionFactory->create();
        $this->storeManager = $storeManager;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $items = $this->collection->getItems();
        foreach ($items as $blog) {
            $this->loadedData[$blog->getId()] = $blog->getData();
        }
        return $this->loadedData;
    }
  
}
