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
namespace Sparsh\Faq\Model\ResourceModel\Faq;

use Sparsh\Faq\Api\Data\FaqInterface;
use Sparsh\Faq\Model\ResourceModel\AbstractCollection;

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
    /**
     * Primary fieldname
     *
     * @var string
     */
    protected $_idFieldName = 'faq_id';

    /**
     * Load data for preview flag
     *
     * @var bool
     */
    protected $_previewFlag;

    /**
     * Collection Class constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            'Sparsh\Faq\Model\Faq',
            'Sparsh\Faq\Model\ResourceModel\Faq'
        );
        $this->_map['fields']['faq_id'] = 'main_table.faq_id';
        $this->_map['fields']['store'] = 'store_table.store_id';
    }

    /**
     * Add filter by store
     *
     * @param int|array|\Magento\Store\Model\Store $store     storeid
     * @param bool                                 $withAdmin isadmin
     *
     * @return $this
     */
    public function addStoreFilter($store, $withAdmin = true)
    {
        if (!$this->getFlag('store_filter_added')) {
            $this->performAddStoreFilter($store, $withAdmin);
        }
        return $this;
    }

    /**
     * Set first store flag
     *
     * @param bool $flag flag
     *
     * @return $this
     */
    public function setFirstStoreFlag($flag = false)
    {
        $this->_previewFlag = $flag;
        return $this;
    }

    /**
     * Perform operations after collection load
     *
     * @return $this
     */
    protected function _afterLoad()
    {
        $entityMetadata = $this->metadataPool->getMetadata(FaqInterface::class);
        $this->performAfterLoad('sparsh_faq_store', $entityMetadata->getLinkField());
        $this->performAfterLoadFaqCategory('sparsh_faq_category', "faq_category_id");
        $this->_previewFlag = false;

        return parent::_afterLoad();
    }

    /**
     * Perform operations before rendering filters
     *
     * @return void
     */
    protected function _renderFiltersBefore()
    {
        $entityMetadata = $this->metadataPool->getMetadata(FaqInterface::class);
        $this->joinStoreRelationTable('sparsh_faq_store', $entityMetadata->getLinkField());
    }
}
