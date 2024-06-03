<?php

namespace Webcreta\ProductRecommendationQuiz\Ui\Component\Column;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\Listing\Columns\Column;

class ActionsCategory extends Column
{

    const URL_PATH_EDIT = 'productrecommendationquiz/jump/index';
    const URL_PATH_DELETE = 'productrecommendationquiz/category/deletecategory';

    protected $urlBuilder;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $SubscribersFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $SubscribersFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                if (isset($item['id'])) {
                    $item[$this->getData('name')] = [
                        'edit' => [
                            'href' => $this->urlBuilder->getUrl(
                                static::URL_PATH_EDIT,
                                [
                                    'id' => $item['id'],
                                ]
                            ),
                            'label' => __('Edit'),
                        ],
                        'delete' => [
                            'href' => $this->urlBuilder->getUrl(
                                static::URL_PATH_DELETE,
                                [
                                    'id' => $item['id'],
                                ]
                            ),
                            'label' => __('Delete'),
                            'confirm' => [
                                'title' => __('Delete Record ?'),
                                'message' => __('Are you sure you wan\'t to delete a record?'),
                            ],
                        ],
                    ];
                }
            }
        }

        return $dataSource;
    }
}
