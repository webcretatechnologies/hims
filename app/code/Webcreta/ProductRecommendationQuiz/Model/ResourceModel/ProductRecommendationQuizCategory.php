<?php

namespace Webcreta\ProductRecommendationQuiz\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class ProductRecommendationQuizCategory extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('webcreta_productrecommendationquizcategory', 'id');
    }
}
