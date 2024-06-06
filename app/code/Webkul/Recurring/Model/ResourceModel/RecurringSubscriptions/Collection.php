<?php
/**
 * Webkul Software.
 *
 * @category   Webkul
 * @package    Webkul_Recurring
 * @author     Webkul Software Private Limited
 * @copyright  Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\Recurring\Model\ResourceModel\RecurringSubscriptions;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Webkul Agorae ResourceModel sliderimages collection
 */
class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'entity_id';
    
    /**
     * @var string
     */
    protected $sales_order_table;

    /**
     * Define resource model.
     */
    protected function _construct()
    {
        $this->_init(
            \Webkul\Recurring\Model\RecurringSubscriptions::class,
            \Webkul\Recurring\Model\ResourceModel\RecurringSubscriptions::class
        );
        $this->_map['fields']['entity_id'] = 'main_table.entity_id';
        $this->_map['fields']['created_at'] = 'main_table.created_at';
        $this->_map['fields']['wkincrement_id'] = 'so.increment_id';
        $this->_map['fields']['plan_type'] = 'plans.name';
    }

    /**
     * Add filter to order
     *
     * @param string $customerId
     * @return $this
     */
    public function filterOrder($customerId)
    {
        $mainTable = "main_table";
        $this->sales_order_table = $this->getTable("sales_order");
        $this->getSelect()
            ->join(
                ['order' => $this->sales_order_table],
                $mainTable . '.order_id= order.entity_id',
                ['customer_id' => 'order.customer_id'
                ]
            );
        $this->addFieldToFilter('main_table.customer_id', $customerId);
        return $this;
    }
    /**
     * Create all ids retrieving select with limitation
     *
     * Backward compatibility with EAV collection
     *
     * @param int $limit
     * @param int $offset
     * @return \Magento\Framework\DB\Select
     */
    protected function _getAllIdsSelect($limit = null, $offset = null)
    {
        $idsSelect = clone $this->getSelect();
        $idsSelect->reset(\Magento\Framework\DB\Select::ORDER);
        $idsSelect->reset(\Magento\Framework\DB\Select::LIMIT_COUNT);
        $idsSelect->reset(\Magento\Framework\DB\Select::LIMIT_OFFSET);
        $idsSelect->reset(\Magento\Framework\DB\Select::COLUMNS);
        $idsSelect->columns($this->getResource()->getIdFieldName(), 'main_table');
        $idsSelect->limit($limit, $offset);
        return $idsSelect;
    }
}
