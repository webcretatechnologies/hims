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

use Webkul\Recurring\Api\Data\RecurringSubscriptionsInterface;
use Magento\Framework\DataObject\IdentityInterface;
use \Magento\Framework\Model\AbstractModel;

/**
 * Webkul Recurring Plans Model
 */
class RecurringSubscriptions extends AbstractModel implements RecurringSubscriptionsInterface, IdentityInterface
{
    /**
     * No route page id
     */
    public const NOROUTE_ENTITY_ID = 'no-route';

    /**
     * Recurring plans cache tag
     */
    public const CACHE_TAG = 'recurring_subscriptions';

    /**
     * @var string
     */
    protected $_cacheTag = 'recurring_subscriptions';

    /**
     * Initialize resource model
     */
    protected function _construct()
    {
        $this->_init(\Webkul\Recurring\Model\ResourceModel\RecurringSubscriptions::class);
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
            return $this->noRouteSubscriptions();
        }
        return parent::load($id, $field);
    }

    /**
     * Load No-Route
     *
     * @return \Webkul\Recurring\Model\RecurringSubscriptions
     */
    public function noRouteSubscriptions()
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
     * @return \Webkul\Recurring\Api\Data\RecurringSubscriptionsInterface
     */
    public function setId($id)
    {
        return $this->setData(self::ENTITY_ID, $id);
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
     * @return \Webkul\Recurring\Api\Data\RecurringSubscriptionsInterface
     */
    public function setOrderId($orderId)
    {
        return $this->setData(self::ORDER_ID, $orderId);
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
     * @return \Webkul\Recurring\Api\Data\RecurringSubscriptionsInterface
     */
    public function setProductId($productId)
    {
        return $this->setData(self::PRODUCT_ID, $productId);
    }

    /**
     * Get Product Name
     *
     * @return string
     */
    public function getProductName()
    {
        return parent::getData(self::PRODUCT_NAME);
    }

    /**
     * Set Product Name
     *
     * @param string $productName
     * @return \Webkul\Recurring\Api\Data\RecurringSubscriptionsInterface
     */
    public function setProductName($productName)
    {
        return $this->setData(self::PRODUCT_NAME, $productName);
    }

    /**
     * Get Customer Id
     *
     * @return int
     */
    public function getCustomerId()
    {
        return parent::getData(self::CUSTOMER_ID);
    }

    /**
     * Set Customer Id
     *
     * @param int $customerId
     * @return \Webkul\Recurring\Api\Data\RecurringSubscriptionsInterface
     */
    public function setCustomerId($customerId)
    {
        return $this->setData(self::CUSTOMER_ID, $customerId);
    }

    /**
     * Get Customer Name
     *
     * @return string
     */
    public function getCustomerName()
    {
        return parent::getData(self::CUSTOMER_NAME);
    }

    /**
     * Set Customer Name
     *
     * @param string $customerName
     * @return \Webkul\Recurring\Api\Data\RecurringSubscriptionsInterface
     */
    public function setCustomerName($customerName)
    {
        return $this->setData(self::CUSTOMER_NAME, $customerName);
    }

    /**
     * Get Plan Id
     *
     * @return int
     */
    public function getPlanId()
    {
        return parent::getData(self::PLAN_ID);
    }

    /**
     * Set Plan Id
     *
     * @param int $planId
     * @return \Webkul\Recurring\Api\Data\RecurringSubscriptionsInterface
     */
    public function setPlanId($planId)
    {
        return $this->setData(self::PLAN_ID, $planId);
    }

    /**
     * Get Ref Profile Id
     *
     * @return int
     */
    public function getRefProfileId()
    {
        return parent::getData(self::REF_PROFILE_ID);
    }

    /**
     * Set Ref Profile Id
     *
     * @param int $refProfileId
     * @return \Webkul\Recurring\Api\Data\RecurringSubscriptionsInterface
     */
    public function setRefProfileId($refProfileId)
    {
        return $this->setData(self::REF_PROFILE_ID, $refProfileId);
    }

    /**
     * Get Stripe Customer Id
     *
     * @return int
     */
    public function getStripeCustomerId()
    {
        return parent::getData(self::STRIPE_CUSTOMER_ID);
    }

    /**
     * Set Stripe Customer Id
     *
     * @param int $stripeCustomerId
     * @return \Webkul\Recurring\Api\Data\RecurringSubscriptionsInterface
     */
    public function setStripeCustomerId($stripeCustomerId)
    {
        return $this->setData(self::STRIPE_CUSTOMER_ID, $stripeCustomerId);
    }

    /**
     * Get Subscription Item Id
     *
     * @return int
     */
    public function getSubscriptionItemId()
    {
        return parent::getData(self::SUBSCRIPTION_ITEM_ID);
    }

    /**
     * Set Subscription Item Id
     *
     * @param int $subscriptionItemId
     * @return \Webkul\Recurring\Api\Data\RecurringSubscriptionsInterface
     */
    public function setSubscriptionItemId($subscriptionItemId)
    {
        return $this->setData(self::SUBSCRIPTION_ITEM_ID, $subscriptionItemId);
    }

    /**
     * Get Start Date
     *
     * @return string
     */
    public function getStartDate()
    {
        return parent::getData(self::START_DATE);
    }

    /**
     * Set Start Date
     *
     * @param string $startDate
     * @return \Webkul\Recurring\Api\Data\RecurringSubscriptionsInterface
     */
    public function setStartDate($startDate)
    {
        return $this->setData(self::START_DATE, $startDate);
    }

    /**
     * Get End Date
     *
     * @return string
     */
    public function getEndDate()
    {
        return parent::getData(self::END_DATE);
    }

    /**
     * Set End Date
     *
     * @param string $endDate
     * @return \Webkul\Recurring\Api\Data\RecurringSubscriptionsInterface
     */
    public function setEndDate($endDate)
    {
        return $this->setData(self::END_DATE, $endDate);
    }

    /**
     * Get valid till date of subscription
     *
     * @return string
     */
    public function getValidTill()
    {
        return parent::getData(self::VALID_DATE);
    }

    /**
     * Set valid till date of subscription
     *
     * @param string $validTill
     * @return \Webkul\Recurring\Api\Data\RecurringSubscriptionsInterface
     */
    public function setValidTill($validTill)
    {
        return $this->setData(self::VALID_DATE, $validTill);
    }

    /**
     * Get payment method code of subscription
     *
     * @return string
     */
    public function getPaymentCode()
    {
        return parent::getData(self::PAYMENT_CODE);
    }

    /**
     * Set payment method code of subscription
     *
     * @param string $paymentCode
     * @return \Webkul\Recurring\Api\Data\RecurringSubscriptionsInterface
     */
    public function setPaymentCode($paymentCode)
    {
        return $this->setData(self::PAYMENT_CODE, $paymentCode);
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
     * @return \Webkul\Recurring\Api\Data\RecurringSubscriptionsInterface
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * Get Discount Managed
     *
     * @return int
     */
    public function getDiscountManaged()
    {
        return parent::getData(self::DISCOUNT_MANAGED);
    }

    /**
     * Set Discount Managed
     *
     * @param int $discountManaged
     * @return \Webkul\Recurring\Api\Data\RecurringSubscriptionsInterface
     */
    public function setDiscountManaged($discountManaged)
    {
        return $this->setData(self::DISCOUNT_MANAGED, $discountManaged);
    }

    /**
     * Get Extra
     *
     * @return string
     */
    public function getExtra()
    {
        return parent::getData(self::EXTRA);
    }

    /**
     * Set Extra
     *
     * @param string $extra
     * @return \Webkul\Recurring\Api\Data\RecurringSubscriptionsInterface
     */
    public function setExtra($extra)
    {
        return $this->setData(self::EXTRA, $extra);
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
     * @return \Webkul\Recurring\Api\Data\RecurringSubscriptionsInterface
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * Get Cancellation reason
     *
     * @return string|null
     */
    public function getCancellationReason()
    {
        return parent::getData(self::CANCELLATION_REASON);
    }

    /**
     * Set cancellation reason
     *
     * @param string $reason
     * @return \Webkul\Recurring\Api\Data\RecurringSubscriptionsInterface
     */
    public function setCancellationReason($reason)
    {
        return $this->setData(self::CANCELLATION_REASON, $reason);
    }
}
