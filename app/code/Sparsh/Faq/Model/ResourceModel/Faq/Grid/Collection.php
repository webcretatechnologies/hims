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
namespace Sparsh\Faq\Model\ResourceModel\Faq\Grid;

use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\Api\Search\AggregationInterface;
use Sparsh\Faq\Model\ResourceModel\Faq\Collection as FaqCollection;

/**
 * Class Collection
 *
 * @category Sparsh
 * @package  Sparsh_Faq
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
class Collection extends FaqCollection implements SearchResultInterface
{
    /**
     * AggregationInterface
     *
     * @var AggregationInterface
     */
    protected $aggregations;

    /**
     * Collection Class constructor
     *
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface    $entityFactory entityFactory
     * @param \Psr\Log\LoggerInterface                                     $logger        logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface                    $eventManager  eventManager
     * @param \Magento\Store\Model\StoreManagerInterface                   $storeManager  storeManager
     * @param \Magento\Framework\EntityManager\MetadataPool                $metadataPool  metadataPool
     * @param mixed|null                                                   $mainTable     mainTable
     * @param string                                                       $eventPrefix   eventPrefix
     * @param mixed                                                        $eventObject   eventObject
     * @param mixed                                                        $resourceModel resourceModel
     * @param string                                                       $model         model
     * @param null                                                         $connection    connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb|null    $resource      resource
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\EntityManager\MetadataPool $metadataPool,
        $mainTable,
        $eventPrefix,
        $eventObject,
        $resourceModel,
        $model = \Magento\Framework\View\Element\UiComponent\DataProvider\Document::class,
        $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $storeManager,
            $metadataPool,
            $connection,
            $resource
        );
        $this->_eventPrefix = $eventPrefix;
        $this->_eventObject = $eventObject;
        $this->_init($model, $resourceModel);
        $this->setMainTable($mainTable);
    }

    /**
     * AggregationInterface
     *
     * @return AggregationInterface
     */
    public function getAggregations()
    {
        return $this->aggregations;
    }

    /**
     * AggregationInterface
     *
     * @param AggregationInterface $aggregations aggregations
     *
     * @return $this
     */
    public function setAggregations($aggregations)
    {
        $this->aggregations = $aggregations;
    }

    /**
     * Get search criteria.
     *
     * @return \Magento\Framework\Api\SearchCriteriaInterface|null
     */
    public function getSearchCriteria()
    {
        return null;
    }

    /**
     * Set search criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria searchCriteria
     *
     * @return $this
     */
    public function setSearchCriteria(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria = null)
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
     * Set total count.
     *
     * @param int $totalCount totalcount
     *
     * @return $this
     */
    public function setTotalCount($totalCount)
    {
        return $this;
    }

    /**
     * Set items list.
     *
     * @param \Magento\Framework\Api\ExtensibleDataInterface[] $items items
     *
     * @return $this
     */
    public function setItems(array $items = null)
    {
        return $this;
    }
}
