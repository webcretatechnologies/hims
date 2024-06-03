<?php
/**
 * Class FaqCategoryName
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

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Sparsh\Faq\Model\FaqCategory;

/**
 * Class FaqCategoryName
 *
 * @category Sparsh
 * @package  Sparsh_Faq
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
class FaqCategoryName extends Column
{
    /**
     * @var FaqCategory
     */
    private $faqCategory;

    /**
     * Constructor
     *
     * @param ContextInterface $context Context
     * @param UiComponentFactory $uiComponentFactory UiComponentFactory
     * @param FaqCategory $faqCategory
     * @param array $components Components
     * @param array $data Data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        FaqCategory $faqCategory,
        array $components = [],
        array $data = []
    ) {
        $this->faqCategory = $faqCategory;
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
                if (isset($item['faq_id'])) {
                    $item['faq_category_id'] = $this->faqCategory->load($item['faq_category_id'])->getName();
                }
            }
        }

        return $dataSource;
    }
}
