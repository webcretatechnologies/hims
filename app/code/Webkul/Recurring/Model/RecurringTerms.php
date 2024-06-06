<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Recurring
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Recurring\Model;

use Webkul\Recurring\Api\Data\RecurringTermsInterface;
use Magento\Framework\DataObject\IdentityInterface;
use \Magento\Framework\Model\AbstractModel;

/**
 * Webkul Recurring Term Model
 */
class RecurringTerms extends AbstractModel implements RecurringTermsInterface, IdentityInterface
{
    /**
     * No route page id
     */
    public const NOROUTE_ENTITY_ID = 'no-route';

    /**
     * Term's Statuses
     */
    public const STATUS_ENABLED = 1;
    public const STATUS_DISABLED = 0;

    /**
     * Recurring Term cache tag
     */
    public const CACHE_TAG = 'recurring_term';

    /**
     * @var string
     */
    protected $_cacheTag = self::CACHE_TAG;

    /**
     * Initialize resource model
     */
    protected function _construct()
    {
        $this->_init(\Webkul\Recurring\Model\ResourceModel\RecurringTerms::class);
    }

    /**
     * Load object data
     *
     * @param int|null $id
     * @param string $field
     * @return $this
     */
    public function load($id, $field = null)
    {
        if ($id === null) {
            return $this->noRouteTerms();
        }
        return parent::load($id, $field);
    }

    /**
     * Load No-Route Term
     *
     * @return \Webkul\Recurring\Model\RecurringTerms
     */
    public function noRouteTerms()
    {
        return $this->load(self::NOROUTE_ENTITY_ID, $this->getIdFieldName());
    }

    /**
     * Prepare Term's statuses.
     *
     * @return array
     */
    public function getAvailableStatuses()
    {
        return [
            self::STATUS_ENABLED => __('Enabled'),
            self::STATUS_DISABLED => __('Disabled')
        ];
    }

    /**
     * Get identities
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * Get ID
     *
     * @return int
     */
    public function getId()
    {
        return parent::getData(self::ENTITY_ID);
    }

    /**
     * Set ID
     *
     * @param int $id
     * @return \Webkul\Recurring\Api\Data\RecurringTerms
     */
    public function setId($id)
    {
        return $this->setData(self::ENTITY_ID, $id);
    }

    /**
     * Get Title
     *
     * @return string
     */
    public function getTitle()
    {
        return parent::getData(self::TITLE);
    }

    /**
     * Set Title
     *
     * @param string $title
     * @return \Webkul\Recurring\Api\Data\RecurringTerms
     */
    public function setTitle($title)
    {
        return $this->setData(self::TITLE, $title);
    }

    /**
     * Get Duration
     *
     * @return int
     */
    public function getDuration()
    {
        return parent::getData(self::DURATION);
    }

    /**
     * Set Duration
     *
     * @param int $duration
     * @return \Webkul\Recurring\Api\Data\RecurringTerms
     */
    public function setDuration($duration)
    {
        return $this->setData(self::DURATION, $duration);
    }

    /**
     * Get Duration Type
     *
     * @return int
     */
    public function getDurationType()
    {
        return parent::getData(self::DURATION_TYPE);
    }

    /**
     * Set Duration Type
     *
     * @param int $durationType
     * @return \Webkul\Recurring\Api\Data\RecurringTerms
     */
    public function setDurationType($durationType)
    {
        return $this->setData(self::DURATION_TYPE, $durationType);
    }

    /**
     * Get Status
     *
     * @return int
     */
    public function getStatus()
    {
        return parent::getData(self::STATUS);
    }

    /**
     * Set Status
     *
     * @param int $status
     * @return \Webkul\Recurring\Api\Data\RecurringTerms
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * Get Sort Order
     *
     * @return int
     */
    public function getSortOrder()
    {
        return parent::getData(self::SORT_ORDER);
    }

    /**
     * Set Sort Order
     *
     * @param int $sortOrder
     * @return \Webkul\Recurring\Api\Data\RecurringTerms
     */
    public function setSortOrder($sortOrder)
    {
        return $this->setData(self::SORT_ORDER, $sortOrder);
    }

    /**
     * Get Created Time
     *
     * @return string
     */
    public function getCreatedTime()
    {
        return parent::getData(self::CREATED_TIME);
    }

    /**
     * Set Created Time
     *
     * @param string $createdTime
     * @return \Webkul\Recurring\Api\Data\RecurringTerms
     */
    public function setCreatedTime($createdTime)
    {
        return $this->setData(self::CREATED_TIME, $createdTime);
    }

    /**
     * Get Update Time
     *
     * @return string
     */
    public function getUpdateTime()
    {
        return parent::getData(self::UPDATE_TIME);
    }

    /**
     * Set Update Time
     *
     * @param string $updateTime
     * @return \Webkul\Recurring\Api\Data\RecurringTerms
     */
    public function setUpdateTime($updateTime)
    {
        return $this->setData(self::UPDATE_TIME, $updateTime);
    }

    /**
     * Get Initial fee
     *
     * @return int|null
     */
    public function getInitialFee()
    {
        return parent::getData(self::INITIAL_FEE);
    }

    /**
     * Set Initial fee
     *
     * @param int $initialFee
     * @return \Webkul\Recurring\Api\Data\RecurringTermsInterface
     */
    public function setInitialFee($initialFee)
    {
        return $this->setData(self::INITIAL_FEE, $initialFee);
    }

    /**
     * Get Initial fee status
     *
     * @return int|null
     */
    public function getInitialFeeStatus()
    {
        return parent::getData(self::INITIAL_FEE_STATUS);
    }

    /**
     * Set Initial fee status
     *
     * @param int $initialFeeStatus
     * @return \Webkul\Recurring\Api\Data\RecurringTermsInterface
     */
    public function setInitialFeeStatus($initialFeeStatus)
    {
        return $this->setData(self::INITIAL_FEE_STATUS, $initialFeeStatus);
    }

    /**
     * Get free trail status
     *
     * @return int|null
     */
    public function getFreeTrailStatus()
    {
        return parent::getData(self::TRAIL_STATUS);
    }

    /**
     * Set free trail status
     *
     * @param int $freeTrailStatus
     * @return \Webkul\Recurring\Api\Data\RecurringTermsInterface
     */
    public function setFreeTrailStatus($freeTrailStatus)
    {
        return $this->setData(self::TRAIL_STATUS, $freeTrailStatus);
    }

    /**
     * Get free trail days
     *
     * @return int|null
     */
    public function getFreeTrailDays()
    {
        return parent::getData(self::TRAIL_DAYS);
    }

    /**
     * Set free trail days
     *
     * @param int $freeTrailDays
     * @return \Webkul\Recurring\Api\Data\RecurringTermsInterface
     */
    public function setFreeTrailDays($freeTrailDays)
    {
        return $this->setData(self::TRAIL_DAYS, $freeTrailDays);
    }
}
