<?php
/**
 * Class FaqCategoryActions
 *
 * PHP version 8.2
 *
 * @category Sparsh
 * @package  Sparsh_Faq
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
namespace Sparsh\Faq\Ui\Component\Listing\Column;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class FaqCategoryActions
 *
 * @category Sparsh
 * @package  Sparsh_Faq
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
class FaqCategoryActions extends Column
{

    const URL_PATH_EDIT = 'faq/faqcategorybackend/edit';
    const URL_PATH_DELETE = 'faq/faqcategorybackend/delete';

    /**
     * UrlBuilder
     *
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * Constructor
     *
     * @param ContextInterface   $context            Context
     * @param UiComponentFactory $uiComponentFactory UiComponentFactory
     * @param UrlInterface       $urlBuilder         UrlBuilder
     * @param array              $components         Components
     * @param array              $data               Data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource DataSource
     *
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item['faq_category_id'])) {
                    if ($item['faq_category_id'] == 1) {
                        $item[$this->getData('name')] = [
                            'edit' => [
                                'href' => $this->urlBuilder->getUrl(
                                    static::URL_PATH_EDIT,
                                    [
                                        'id' => $item['faq_category_id']
                                    ]
                                ),
                                'label' => __('Edit')
                            ]
                        ];
                    } else {
                        $item[$this->getData('name')] = [
                            'edit' => [
                                'href' => $this->urlBuilder->getUrl(
                                    static::URL_PATH_EDIT,
                                    [
                                        'id' => $item['faq_category_id']
                                    ]
                                ),
                                'label' => __('Edit')
                            ],
                            'delete' => [
                                'href' => $this->urlBuilder->getUrl(
                                    static::URL_PATH_DELETE,
                                    [
                                        'id' => $item['faq_category_id']
                                    ]
                                ),
                                'label' => __('Delete'),
                                'confirm' => [
                                    'title' => __('Delete "${ $.$data.faq_category_name }"'),
                                    'message' => __('Are you sure you want to delete a "${ $.$data.faq_category_name }" record?')
                                ]
                            ]
                        ];
                    }
                }
            }
        }
        return $dataSource;
    }
}