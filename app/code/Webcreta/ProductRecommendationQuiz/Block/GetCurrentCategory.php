<?php

namespace Webcreta\ProductRecommendationQuiz\Block;

use Magento\Framework\View\Element\Template;
use Webcreta\ProductRecommendationQuiz\Helper\Data as HelperData;

class GetCurrentCategory extends Template
{
    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * YourBlock constructor.
     *
     * @param Template\Context $context
     * @param HelperData $helperData
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        HelperData $helperData,
        array $data = []
    ) {
        $this->helperData = $helperData;
        parent::__construct($context, $data);
    }

    /**
     * Get category status by ID.
     *
     * @param int $categoryId
     * @return array|null
     */
    public function getCategoryStatus($categoryId)
    {
        return $this->helperData->getCategoryStatus($categoryId);
    }

    /**
     * Get choose category quiz value by ID.
     *
     * @param int $categoryId
     * @return mixed|null
     */
    public function getChooseCategoryQuizValue($categoryId)
    {
        return $this->helperData->getChooseCategoryQuizValue($categoryId);
    }

    /**
     * Get question data by attribute set ID.
     *
     * @param int $attributeSetId
     * @return array|null
     */
    public function getQuestionData($attributeSetId)
    {
        return $this->helperData->getQuestionData($attributeSetId);
    }
}
