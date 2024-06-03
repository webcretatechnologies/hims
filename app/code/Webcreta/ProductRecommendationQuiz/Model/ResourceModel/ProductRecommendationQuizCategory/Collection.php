<?php

namespace Webcreta\ProductRecommendationQuiz\Model\ResourceModel\ProductRecommendationQuizCategory;

use Webcreta\ProductRecommendationQuiz\Model\ProductRecommendationQuizCategory as ProductRecommendationQuizCategoryModel;
use Webcreta\ProductRecommendationQuiz\Model\ResourceModel\ProductRecommendationQuizCategory as ProductRecommendationQuizCategoryResourceModel;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            ProductRecommendationQuizCategoryModel::class,
            ProductRecommendationQuizCategoryResourceModel::class
        );
    }
    public function getCollection()
    {
        return $this->create()->getCollection();
    }
}
