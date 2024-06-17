<?php

namespace Webcreta\ProductRecommendationQuiz\Model;

use Webcreta\ProductRecommendationQuiz\Model\ResourceModel\ProductRecommendationQuizData as ProductRecommendationQuizDataResourceModel;
use Magento\Framework\Model\AbstractModel;

class ProductRecommendationQuizData extends AbstractModel
{
    protected function _construct()
    {
        $this->_init(ProductRecommendationQuizDataResourceModel::class);
    }
}
