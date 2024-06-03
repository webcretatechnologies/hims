<?php
/**
 * Class Collection
 *
 * PHP version 8.2
 *
 * @category Sparsh
 * @package  Sparsh_Faq
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
namespace Sparsh\Faq\Model\ResourceModel\FaqCategory;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection
 *
 * @category Sparsh
 * @package  Sparsh_Faq
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
class Collection extends AbstractCollection
{
    protected $_idFieldName = 'faq_category_id';
    protected $_eventPrefix = 'sparsh_faq_category_collection';
    protected $_eventObject = 'faq_category_collection';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            'Sparsh\Faq\Model\FaqCategory',
            'Sparsh\Faq\Model\ResourceModel\FaqCategory'
        );
    }
}
