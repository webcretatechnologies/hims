<?php

namespace Webcreta\ProductRecommendationQuiz\Model;

use Webcreta\ProductRecommendationQuiz\Model\ResourceModel\ProductRecommendationQuiz as ProductRecommendationQuizResourceModel;
use Magento\Framework\Model\AbstractModel;

class ProductRecommendationQuiz extends AbstractModel
{
    protected function _construct()
    {
        $this->_init(ProductRecommendationQuizResourceModel::class);
    }
}
