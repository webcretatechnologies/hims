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
namespace Webkul\Recurring\Model\ResourceModel\SubscriptionReports\Grid;

use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\Search\AggregationInterface;
use Webkul\Recurring\Model\ResourceModel\RecurringSubscriptions\Collection as SubscriptionsCollection;

/**
 * Collection for displaying grid of Recurring Subscriptions
 */
class Collection extends SubscriptionsCollection implements SearchResultInterface
{
    /**
     * @var AggregationInterface
     */
    protected $aggregations;

    /**
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface $entity
     * @param \Psr\Log\LoggerInterface $logger
     * @param  FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $event
     * @param mixed|null $mainTable
     * @param AbstractDb $eventPrefix
     * @param mixed $eventObject
     * @param mixed $resourceModel
     * @param string $model
     * @param mixed $connection
     * @param AbstractDb|null $resource
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entity,
        \Psr\Log\LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $event,
        $mainTable,
        $eventPrefix,
        $eventObject,
        $resourceModel,
        $model = \Magento\Framework\View\Element\UiComponent\DataProvider\Document::class,
        $connection = null,
        AbstractDb $resource = null
    ) {
        parent::__construct($entity, $logger, $fetchStrategy, $event, $connection, $resource);
        $this->_eventPrefix = $eventPrefix;
        $this->_eventObject = $eventObject;
        $this->_init($model, $resourceModel);
        $this->setMainTable($mainTable);
    }

    /**
     * Get search criteria.
     *
     * @return \Magento\Framework\Api\SearchCriteriaInterface|null
     */
    public function getSearchCriteria()
    {
        return '';
    }

    /**
     * Get AggregationInterface
     *
     * @return \Magento\Framework\Search\AggregationInterface
     */
    public function getAggregations()
    {
        return $this->aggregations;
    }
    
    /**
     * Set items list.
     *
     * @param \Magento\Framework\Api\ExtensibleDataInterface[] $items
     *
     * @return $this
     */
    public function setItems(array $items = null)
    {
        return $this;
    }

    /**
     * Set Aggregations
     *
     * @param AggregationInterface $aggregations
     * @return $this
     */
    public function setAggregations($aggregations)
    {
        $this->aggregations = $aggregations;
        return $this;
    }
    
    /**
     * Retrieve all ids for collection
     *
     * Backward compatibility with EAV collection.
     *
     * @param int $limit
     * @param int $offset
     *
     * @return array
     */
    public function getAllIds($limit = null, $offset = null)
    {
        $ids = $this->_getAllIdsSelect($limit, $offset);
        return $this->getConnection()->fetchCol($ids, $this->_bindParams);
    }

    /**
     * Set search criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $search
     *
     * @return $this
     */
    public function setSearchCriteria(SearchCriteriaInterface $search = null)
    {
        return $this;
    }

    /**
     * Set total count.
     *
     * @param int $totalCount
     *
     * @return $this
     */
    public function setTotalCount($totalCount)
    {
        return $this;
    }

    /**
     * Get total count.
     *
     * @return int
     */
    public function getTotalCount()
    {
        return $this->getSize();
    }

    /**
     * Mapping status of subscription table to grid filter
     */
    protected function _initSelect()
    {
        $this->addFilterToMap("status", "main_table.status");
        parent::_initSelect();
    }

    /**
     * Adding customer name and increment id column
     */
    protected function _renderFiltersBefore()
    {
        $this->getSelect()->joinLeft(
            ['so' => $this->getTable('sales_order')],
            'main_table.order_id = so.entity_id',
            [
                'SUM(so.grand_total) as total_revenue',
                'COUNT(DISTINCT main_table.order_id) as total_active_subscription',
            ]
        );
        $this->getSelect()->joinLeft(
            ['cpe' => $this->getTable('catalog_product_entity')],
            'main_table.product_id = cpe.entity_id',
            ['cpe.sku']
        );
        $this->getSelect()->joinLeft(
            ['rt' => $this->getTable('recurring_terms')],
            'main_table.plan_id = rt.entity_id',
            ['rt.title']
        );
        $this->getSelect()->joinLeft(
            ['rsm' => $this->getTable('recurring_subscriptions_mapping')],
            'main_table.entity_id = rsm.subscription_id',
            [
                'COUNT(DISTINCT rsm.order_id) as new_subscription',
            ]
        );
        
        $this->getSelect()->group('product_id');
        $this->getSelect()->group('plan_id');
        $this->getSelect()->where('main_table.status', ['eq'=> 1]);
        parent::_renderFiltersBefore();
    }
}
