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
interface RecurringProductPlansInterface
{
    /**
     * Constants for keys of data array.
     * Identical to the name of the getter in snake case
     */
    public const ENTITY_ID = 'entity_id';
    public const NAME = 'name';
    public const DISCOUNT_TYPE = 'discount_type';
    public const TYPE = 'type';
    public const PRODUCT_ID = 'product_id';
    public const STORE_ID = 'store_id';
    public const WEBSITE_ID = 'website_id';
    public const STATUS = 'status';
    public const INITIAL_FEE = 'initial_fee';
    public const SUBSCRIPTION_CHARGE = 'subscription_charge';
    public const SORT_ORDER = 'sort_order';
    public const CREATED_TIME = 'created_time';
    public const UPDATE_TIME = 'update_time';
    public const ONLY_FOR_SUBSCRIPTION = 'only_for_subscription';
    public const PARENT_PRODUCT_ID = 'parent_product_id';
   
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
     * @return \Webkul\Recurring\Api\Data\RecurringProductPlansInterface
     */
    public function setId($id);

    /**
     * Get Name
     *
     * @return string|null
     */
    public function getName();

    /**
     * Set Name
     *
     * @param string $name
     * @return \Webkul\Recurring\Api\Data\RecurringProductPlansInterface
     */
    public function setName($name);

    /**
     * Get discount type
     *
     * @return string|null
     */
    public function getDiscountType();

    /**
     * Set discount type
     *
     * @param string $discountType
     * @return \Webkul\Recurring\Api\Data\RecurringProductPlansInterface
     */
    public function setDiscountType($discountType);

    /**
     * Get Type
     *
     * @return string|null
     */
    public function getType();

    /**
     * Set Type
     *
     * @param string $type
     * @return \Webkul\Recurring\Api\Data\RecurringProductPlansInterface
     */
    public function setType($type);

    /**
     * Get Product Id
     *
     * @return int|null
     */
    public function getProductId();

    /**
     * Set Product Id
     *
     * @param int $productId
     * @return \Webkul\Recurring\Api\Data\RecurringProductPlansInterface
     */
    public function setProductId($productId);

    /**
     * Get Store Id
     *
     * @return int|null
     */
    public function getStoreId();

    /**
     * Set Store Id
     *
     * @param int $storeId
     * @return \Webkul\Recurring\Api\Data\RecurringProductPlansInterface
     */
    public function setStoreId($storeId);

    /**
     * Get Website Id
     *
     * @return int|null
     */
    public function getWebsiteId();

    /**
     * Set Website Id
     *
     * @param int $websiteId
     * @return \Webkul\Recurring\Api\Data\RecurringProductPlansInterface
     */
    public function setWebsiteId($websiteId);

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
     * @return \Webkul\Recurring\Api\Data\RecurringProductPlansInterface
     */
    public function setStatus($status);

    /**
     * Get Initial Fee
     *
     * @return float|null
     */
    public function getInitialFee();

    /**
     * Set Initial Fee
     *
     * @param float $initialFee
     * @return \Webkul\Recurring\Api\Data\RecurringProductPlansInterface
     */
    public function setInitialFee($initialFee);

    /**
     * Get Subscription Charge
     *
     * @return float|null
     */
    public function getSubscriptionCharge();

    /**
     * Set Subscription Charge
     *
     * @param float $subscriptionCharge
     * @return \Webkul\Recurring\Api\Data\RecurringProductPlansInterface
     */
    public function setSubscriptionCharge($subscriptionCharge);

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
     * @return \Webkul\Recurring\Api\Data\RecurringProductPlansInterface
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
     * @return \Webkul\Recurring\Api\Data\RecurringProductPlansInterface
     */
    public function setCreatedTime($createdTime);

    /**
     * Get Update Time
     *
     * @return string|null
     */
    public function getUpdateTime();

    /**
     * Set Update Time
     *
     * @param string $updateTime
     * @return \Webkul\Recurring\Api\Data\RecurringProductPlansInterface
     */
    public function setUpdateTime($updateTime);

    /**
     * Get only for subcription
     *
     * @return string|null
     */
    public function getOnlyForSubscription();

    /**
     * Set only for subcription
     *
     * @param string $onlyForSubscription
     * @return \Webkul\Recurring\Api\Data\RecurringProductPlansInterface
     */
    public function setOnlyForSubscription($onlyForSubscription);

    /**
     * Get parent product id for configurable product
     *
     * @return string|null
     */
    public function getParentProductId();

    /**
     * Set parent product id for configurable product
     *
     * @param string $parentProductId
     * @return \Webkul\Recurring\Api\Data\RecurringProductPlansInterface
     */
    public function setParentProductId($parentProductId);
}
