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

use Webkul\Recurring\Api\Data\RecurringSubscriptionsMappingInterface;
use Magento\Framework\DataObject\IdentityInterface;
use \Magento\Framework\Model\AbstractModel;

/**
 * Webkul Recurring Mapping Model
 */
class RecurringSubscriptionsMapping extends AbstractModel implements
    RecurringSubscriptionsMappingInterface,
    IdentityInterface
{
    /**
     * No route page id
     */
    public const NOROUTE_ENTITY_ID = 'no-route';

    /**
     * Sliderimages's Statuses
     */
    public const STATUS_ENABLED = 1;
    public const STATUS_DISABLED = 0;

    /**
     * Recurring mapping cache tag
     */
    public const CACHE_TAG = 'recurring_mapping';

    /**
     * @var string
     */
    protected $_cacheTag = 'recurring_mapping';

    /**
     * Initialize resource model
     */
    protected function _construct()
    {
        $this->_init(\Webkul\Recurring\Model\ResourceModel\RecurringSubscriptionsMapping::class);
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
            return $this->noRouteMapping();
        }
        return parent::load($id, $field);
    }

    /**
     * Load No-Route Mapping
     *
     * @return \Webkul\Recurring\Model\RecurringSubscriptionsMapping
     */
    public function noRouteMapping()
    {
        return $this->load(self::NOROUTE_ENTITY_ID, $this->getIdFieldName());
    }

    /**
     * Prepare sliderimages's statuses.
     *
     * Available event agorae_sliderimages_get_available_statuses to customize statuses.
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
     * @return \Webkul\Recurring\Api\Data\RecurringSubscriptionsMappingInterface
     */
    public function setId($id)
    {
        return $this->setData(self::ENTITY_ID, $id);
    }

    /**
     * Get Subscription Id
     *
     * @return int
     */
    public function getSubscriptionId()
    {
        return parent::getData(self::SUBSCRIPTION_ID);
    }

    /**
     * Set Subscription Id
     *
     * @param int $subscriptionId
     * @return \Webkul\Recurring\Api\Data\RecurringSubscriptionsMappingInterface
     */
    public function setSubscriptionId($subscriptionId)
    {
        return $this->setData(self::SUBSCRIPTION_ID, $subscriptionId);
    }

    /**
     * Get Order Id
     *
     * @return int
     */
    public function getOrderId()
    {
        return parent::getData(self::ORDER_ID);
    }

    /**
     * Set Order Id
     *
     * @param int $orderId
     * @return \Webkul\Recurring\Api\Data\RecurringSubscriptionsMappingInterface
     */
    public function setOrderId($orderId)
    {
        return $this->setData(self::ORDER_ID, $orderId);
    }

    /**
     * Get Created At
     *
     * @return string
     */
    public function getCreatedAt()
    {
        return parent::getData(self::CREATED_AT);
    }

    /**
     * Set Created At
     *
     * @param string $createdAt
     * @return \Webkul\Recurring\Api\Data\RecurringSubscriptionsMappingInterface
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }
}
