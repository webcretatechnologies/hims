<?php

namespace Webcreta\ProductRecommendationQuiz\Model;

use Webcreta\ProductRecommendationQuiz\Model\ResourceModel\ProductRecommendationQuizCategory as ProductRecommendationQuizCategoryResourceModel;
use Magento\Framework\Model\AbstractModel;

class ProductRecommendationQuizCategory extends AbstractModel
{
    protected function _construct()
    {
        $this->_init(ProductRecommendationQuizCategoryResourceModel::class);
    }
}
