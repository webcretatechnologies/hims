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
namespace Webkul\Recurring\Api\Data;

/**
 * Recurring Plans interface.
 * @api
 */
interface RecurringTermsInterface
{
    /**
     * Constants for keys of data array.
     * Identical to the name of the getter in snake case
     */
    public const ENTITY_ID = 'entity_id';
    public const TITLE = 'title';
    public const DURATION = 'duration';
    public const DURATION_TYPE = 'duration_type';
    public const STATUS = 'status';
    public const SORT_ORDER = 'sort_order';
    public const TERM_ID = 'term_id';
    public const CREATED_TIME = 'created_time';
    public const UPDATE_TIME = 'update_time';
    public const INITIAL_FEE = 'initial_fee';
    public const INITIAL_FEE_STATUS = 'initial_fee_status';
    public const TRAIL_STATUS = 'free_trail_status';
    public const TRAIL_DAYS = 'free_trail_days';

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId();

    /**
     * Set ID
     *
     * @param int $id
     * @return \Webkul\Recurring\Api\Data\RecurringTermsInterface
     */
    public function setId($id);

    /**
     * Get Title
     *
     * @return string|null
     */
    public function getTitle();

    /**
     * Set Title
     *
     * @param string $title
     * @return \Webkul\Recurring\Api\Data\RecurringTermsInterface
     */
    public function setTitle($title);

    /**
     * Get Duration
     *
     * @return int|null
     */
    public function getDuration();

    /**
     * Set Duration
     *
     * @param int $duration
     * @return \Webkul\Recurring\Api\Data\RecurringTermsInterface
     */
    public function setDuration($duration);

    /**
     * Get Duration Type
     *
     * @return int|null
     */
    public function getDurationType();

    /**
     * Set Duration Type
     *
     * @param int $durationType
     * @return \Webkul\Recurring\Api\Data\RecurringTermsInterface
     */
    public function setDurationType($durationType);

    /**
     * Get Status
     *
     * @return int|null
     */
    public function getStatus();

    /**
     * Set Status
     *
     * @param int $status
     * @return \Webkul\Recurring\Api\Data\RecurringTermsInterface
     */
    public function setStatus($status);

    /**
     * Get Sort Order
     *
     * @return int|null
     */
    public function getSortOrder();

    /**
     * Set Sort Order
     *
     * @param int $sortOrder
     * @return \Webkul\Recurring\Api\Data\RecurringTermsInterface
     */
    public function setSortOrder($sortOrder);

    /**
     * Get Created Time
     *
     * @return string|null
     */
    public function getCreatedTime();

    /**
     * Set Created Time
     *
     * @param string $createdTime
     * @return \Webkul\Recurring\Api\Data\RecurringTermsInterface
     */
    public function setCreatedTime($createdTime);

    /**
     * Get Update Time
     *
     * @return int|null
     */
    public function getUpdateTime();

    /**
     * Set Update Time
     *
     * @param int $updateTime
     * @return \Webkul\Recurring\Api\Data\RecurringTermsInterface
     */
    public function setUpdateTime($updateTime);

    /**
     * Get Initial fee
     *
     * @return int|null
     */
    public function getInitialFee();

    /**
     * Set Initial fee
     *
     * @param int $initialFee
     * @return \Webkul\Recurring\Api\Data\RecurringTermsInterface
     */
    public function setInitialFee($initialFee);

    /**
     * Get Initial fee status
     *
     * @return int|null
     */
    public function getInitialFeeStatus();

    /**
     * Set Initial fee status
     *
     * @param int $initialFeeStatus
     * @return \Webkul\Recurring\Api\Data\RecurringTermsInterface
     */
    public function setInitialFeeStatus($initialFeeStatus);

    /**
     * Get free trail status
     *
     * @return int|null
     */
    public function getFreeTrailStatus();

    /**
     * Set free trail status
     *
     * @param int $freeTrailStatus
     * @return \Webkul\Recurring\Api\Data\RecurringTermsInterface
     */
    public function setFreeTrailStatus($freeTrailStatus);

    /**
     * Get free trail days
     *
     * @return int|null
     */
    public function getFreeTrailDays();

    /**
     * Set free trail days
     *
     * @param int $freeTrailDays
     * @return \Webkul\Recurring\Api\Data\RecurringTermsInterface
     */
    public function setFreeTrailDays($freeTrailDays);
}
