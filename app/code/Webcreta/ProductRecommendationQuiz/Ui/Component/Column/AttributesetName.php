<?php

namespace Webcreta\ProductRecommendationQuiz\Ui\Component\Column;

use Magento\Catalog\Model\CategoryFactory;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Eav\Api\AttributeSetRepositoryInterface;

class AttributesetName extends Column
{
    protected $attributeSetRepository;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        AttributeSetRepositoryInterface $attributeSetRepository,
        array $components = [],
        array $data = []
    ) {
        $this->attributeSetRepository = $attributeSetRepository;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $attribuiteSetId = $item[$this->getData('name')];
                $attributeSet = $this->attributeSetRepository->get($attribuiteSetId);
                $attributesetName =  $attributeSet->getAttributeSetName();
                $item[$this->getData('name')] = $attributesetName;
            }
        }
        return $dataSource;
    }
}
