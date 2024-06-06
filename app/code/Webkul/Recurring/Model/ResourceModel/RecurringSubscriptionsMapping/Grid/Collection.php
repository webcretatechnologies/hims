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
namespace Webkul\Recurring\Model\ResourceModel\RecurringSubscriptionsMapping\Grid;

use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\Search\AggregationInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Webkul\Recurring\Model\ResourceModel\RecurringSubscriptionsMapping\Collection as MappingCollection;

/**
 * Collection for displaying grid of Recurring Mapping
 */
class Collection extends MappingCollection implements SearchResultInterface
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $session;

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
     * @param \Magento\Customer\Model\Session $session
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
        \Magento\Customer\Model\Session $session,
        $model = \Magento\Framework\View\Element\UiComponent\DataProvider\Document::class,
        $connection = null,
        AbstractDb $resource = null
    ) {
        parent::__construct($entity, $logger, $fetchStrategy, $event, $connection, $resource);
        $this->_eventPrefix = $eventPrefix;
        $this->_eventObject = $eventObject;
        $this->_init($model, $resourceModel);
        $this->session = $session;
        $this->setMainTable($mainTable);
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
     * Get search criteria.
     *
     * @return \Magento\Framework\Api\SearchCriteriaInterface|null
     */
    public function getSearchCriteria()
    {
        return '';
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
     * Filter data for current subscription id
     */
    protected function _renderFiltersBefore()
    {
        $id = $this->session->getSubscriptionId();
        $this->getSelect()->where("main_table.subscription_id = '". $id ."'");
        parent::_renderFiltersBefore();
    }
}
