<?php

namespace Webcreta\ProductRecommendationQuiz\Model\ResourceModel\ProductRecommendationQuiz;

use Webcreta\ProductRecommendationQuiz\Model\ProductRecommendationQuiz as ProductRecommendationQuizModel;
use Webcreta\ProductRecommendationQuiz\Model\ResourceModel\ProductRecommendationQuiz as ProductRecommendationQuizResourceModel;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            ProductRecommendationQuizModel::class,
            ProductRecommendationQuizResourceModel::class
        );
    }
    public function getCollection()
    {
        return $this->create()->getCollection();
    }
}
