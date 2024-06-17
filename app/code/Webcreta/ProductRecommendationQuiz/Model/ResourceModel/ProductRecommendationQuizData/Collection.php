<?php

namespace Webcreta\ProductRecommendationQuiz\Model\ResourceModel\ProductRecommendationQuizData;

use Webcreta\ProductRecommendationQuiz\Model\ProductRecommendationQuizData as ProductRecommendationQuizDataModel;
use Webcreta\ProductRecommendationQuiz\Model\ResourceModel\ProductRecommendationQuizData as ProductRecommendationQuizDataResourceModel;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            ProductRecommendationQuizDataModel::class,
            ProductRecommendationQuizDataResourceModel::class
        );
    }
    public function getCollection()
    {
        return $this->create()->getCollection();
    }
}
