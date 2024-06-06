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

use Webkul\Recurring\Api\Data\RecurringProductPlansInterface;
use Magento\Framework\DataObject\IdentityInterface;

/**
 * Webkul Recurring Plans Model
 */
class RecurringProductPlans extends \Magento\Framework\Model\AbstractModel implements
    RecurringProductPlansInterface,
    IdentityInterface
{
    /**
     * No route page id
     */
    public const NOROUTE_ENTITY_ID = 'no-route';

    /**
     * Recurring plans cache tag
     */
    public const CACHE_TAG = 'recurring_product_plans';

    /**
     * @var string
     */
    protected $_cacheTag = 'recurring_product_plans';

    /**
     * Initialize resource model
     */
    protected function _construct()
    {
        $this->_init(\Webkul\Recurring\Model\ResourceModel\RecurringProductPlans::class);
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
            return $this->noRoutePlans();
        }
        return parent::load($id, $field);
    }

    /**
     * Load No-Route
     *
     * @return \Webkul\Recurring\Model\Plans
     */
    public function noRoutePlans()
    {
        return $this->load(self::NOROUTE_ENTITY_ID, $this->getIdFieldName());
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
     * @return \Webkul\Recurring\Api\Data\RecurringProductPlansInterface
     */
    public function setId($id)
    {
        return $this->setData(self::ENTITY_ID, $id);
    }

    /**
     * Get Name
     *
     * @return string
     */
    public function getName()
    {
        return parent::getData(self::NAME);
    }

    /**
     * Set Name
     *
     * @param string $name
     * @return \Webkul\Recurring\Api\Data\RecurringProductPlansInterface
     */
    public function setName($name)
    {
        return $this->setData(self::NAME, $name);
    }

    /**
     * Get discount type
     *
     * @return string
     */
    public function getDiscountType()
    {
        return parent::getData(self::DISCOUNT_TYPE);
    }

    /**
     * Set discount type
     *
     * @param string $discountType
     * @return \Webkul\Recurring\Api\Data\RecurringProductPlansInterface
     */
    public function setDiscountType($discountType)
    {
        return $this->setData(self::DISCOUNT_TYPE, $discountType);
    }

    /**
     * Get Type
     *
     * @return int
     */
    public function getType()
    {
        return parent::getData(self::TYPE);
    }

    /**
     * Set Type
     *
     * @param int $type
     * @return \Webkul\Recurring\Api\Data\RecurringProductPlansInterface
     */
    public function setType($type)
    {
        return $this->setData(self::TYPE, $type);
    }

    /**
     * Get Product Id
     *
     * @return int
     */
    public function getProductId()
    {
        return parent::getData(self::PRODUCT_ID);
    }

    /**
     * Set Product Id
     *
     * @param int $productId
     * @return \Webkul\Recurring\Api\Data\RecurringProductPlansInterface
     */
    public function setProductId($productId)
    {
        return $this->setData(self::PRODUCT_ID, $productId);
    }

    /**
     * Get Store Id
     *
     * @return int
     */
    public function getStoreId()
    {
        return parent::getData(self::STORE_ID);
    }

    /**
     * Set Store Id
     *
     * @param int $storeId
     * @return \Webkul\Recurring\Api\Data\RecurringProductPlansInterface
     */
    public function setStoreId($storeId)
    {
        return $this->setData(self::STORE_ID, $storeId);
    }

    /**
     * Get Website Id
     *
     * @return int
     */
    public function getWebsiteId()
    {
        return parent::getData(self::WEBSITE_ID);
    }

    /**
     * Set Website Id
     *
     * @param int $websiteId
     * @return \Webkul\Recurring\Api\Data\RecurringProductPlansInterface
     */
    public function setWebsiteId($websiteId)
    {
        return $this->setData(self::WEBSITE_ID, $websiteId);
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
     * @return \Webkul\Recurring\Api\Data\RecurringProductPlansInterface
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * Get InitialFee
     *
     * @return float
     */
    public function getInitialFee()
    {
        return parent::getData(self::INITIAL_FEE);
    }

    /**
     * Set InitialFee
     *
     * @param float $initialFee
     * @return \Webkul\Recurring\Api\Data\RecurringProductPlansInterface
     */
    public function setInitialFee($initialFee)
    {
        return $this->setData(self::INITIAL_FEE, $initialFee);
    }

    /**
     * Get Subscription Charge
     *
     * @return float
     */
    public function getSubscriptionCharge()
    {
        return parent::getData(self::SUBSCRIPTION_CHARGE);
    }

    /**
     * Set Subscription Charge
     *
     * @param float $subscriptionCharge
     * @return \Webkul\Recurring\Api\Data\RecurringProductPlansInterface
     */
    public function setSubscriptionCharge($subscriptionCharge)
    {
        return $this->setData(self::SUBSCRIPTION_CHARGE, $subscriptionCharge);
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
     * @return \Webkul\Recurring\Api\Data\RecurringProductPlansInterface
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
     * @return \Webkul\Recurring\Api\Data\RecurringProductPlansInterface
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
     * @return \Webkul\Recurring\Api\Data\RecurringProductPlansInterface
     */
    public function setUpdateTime($updateTime)
    {
        return $this->setData(self::UPDATE_TIME, $updateTime);
    }

    /**
     * Get only for subcription
     *
     * @return string|null
     */
    public function getOnlyForSubscription()
    {
        return parent::getData(self::ONLY_FOR_SUBSCRIPTION);
    }
    /**
     * Set only for subcription
     *
     * @param string $onlyForSubscription
     * @return \Webkul\Recurring\Api\Data\RecurringProductPlansInterface
     */
    public function setOnlyForSubscription($onlyForSubscription)
    {
        return $this->setData(self::ONLY_FOR_SUBSCRIPTION, $onlyForSubscription);
    }

    /**
     * Get parent product id for configurable product
     *
     * @return string|null
     */
    public function getParentProductId()
    {
        return parent::getData(self::PARENT_PRODUCT_ID);
    }

    /**
     * Set parent product id for configurable product
     *
     * @param string $parentProductId
     * @return \Webkul\Recurring\Api\Data\RecurringProductPlansInterface
     */
    public function setParentProductId($parentProductId)
    {
        return $this->setData(self::PARENT_PRODUCT_ID, $parentProductId);
    }
}
