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
 * Recurring Subscriptions data interface.
 * @api
 */
interface RecurringSubscriptionsInterface
{
    /**
     * Constants for keys of data array.
     * Identical to the name of the getter in snake case
     */
    public const ENTITY_ID = 'entity_id';
    public const ORDER_ID = 'order_id';
    public const PRODUCT_ID = 'product_id';
    public const PRODUCT_NAME = 'product_name';
    public const CUSTOMER_ID = 'customer_id';
    public const CUSTOMER_NAME = 'customer_name';
    public const PLAN_ID = 'plan_id';
    public const REF_PROFILE_ID = 'ref_profile_id';
    public const STRIPE_CUSTOMER_ID = 'stripe_customer_id';
    public const SUBSCRIPTION_ITEM_ID = 'subscription_item_id';
    public const START_DATE = 'start_date';
    public const END_DATE = 'end_date';
    public const VALID_DATE = 'valid_till';
    public const PAYMENT_CODE = 'payment_code';
    public const STATUS = 'status';
    public const DISCOUNT_MANAGED = 'discount_managed';
    public const EXTRA = 'extra';
    public const CREATED_AT = 'created_at';
    public const CANCELLATION_REASON = 'cancellation_resason';

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
     * @return \Webkul\Recurring\Api\Data\RecurringSubscriptionsInterface
     */
    public function setId($id);

    /**
     * Get Order Id
     *
     * @return int|null
     */
    public function getOrderId();

    /**
     * Set Order Id
     *
     * @param int $orderId
     * @return \Webkul\Recurring\Api\Data\RecurringSubscriptionsInterface
     */
    public function setOrderId($orderId);

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
     * @return \Webkul\Recurring\Api\Data\RecurringSubscriptionsInterface
     */
    public function setProductId($productId);

    /**
     * Get Product Name
     *
     * @return string|null
     */
    public function getProductName();

    /**
     * Set Product Name
     *
     * @param string $productName
     * @return \Webkul\Recurring\Api\Data\RecurringSubscriptionsInterface
     */
    public function setProductName($productName);

    /**
     * Get Customer Id
     *
     * @return int|null
     */
    public function getCustomerId();

    /**
     * Set Customer Id
     *
     * @param int $customerId
     * @return \Webkul\Recurring\Api\Data\RecurringSubscriptionsInterface
     */
    public function setCustomerId($customerId);

    /**
     * Get Customer Name
     *
     * @return string|null
     */
    public function getCustomerName();

    /**
     * Set Customer Name
     *
     * @param string $customerName
     * @return \Webkul\Recurring\Api\Data\RecurringSubscriptionsInterface
     */
    public function setCustomerName($customerName);

    /**
     * Get Plan Id
     *
     * @return int|null
     */
    public function getPlanId();

    /**
     * Set Plan Id
     *
     * @param int $planId
     * @return \Webkul\Recurring\Api\Data\RecurringSubscriptionsInterface
     */
    public function setPlanId($planId);

    /**
     * Get Ref Profile Id
     *
     * @return int|null
     */
    public function getRefProfileId();

    /**
     * Set Ref Profile Id
     *
     * @param int $refProfileId
     * @return \Webkul\Recurring\Api\Data\RecurringSubscriptionsInterface
     */
    public function setRefProfileId($refProfileId);

    /**
     * Get Stripe Customer Id
     *
     * @return int|null
     */
    public function getStripeCustomerId();

    /**
     * Set Stripe Customer Id
     *
     * @param int $stripeCustomerId
     * @return \Webkul\Recurring\Api\Data\RecurringSubscriptionsInterface
     */
    public function setStripeCustomerId($stripeCustomerId);

    /**
     * Get Subscription Item Id
     *
     * @return int|null
     */
    public function getSubscriptionItemId();

    /**
     * Set Subscription Item Id
     *
     * @param int $subscriptionItemId
     * @return \Webkul\Recurring\Api\Data\RecurringSubscriptionsInterface
     */
    public function setSubscriptionItemId($subscriptionItemId);

    /**
     * Get Start Date
     *
     * @return string|null
     */
    public function getStartDate();

    /**
     * Set Start Date
     *
     * @param string $startDate
     * @return \Webkul\Recurring\Api\Data\RecurringSubscriptionsInterface
     */
    public function setStartDate($startDate);

    /**
     * Get end Date
     *
     * @return string|null
     */
    public function getEndDate();

    /**
     * Set end Date
     *
     * @param string $endDate
     * @return \Webkul\Recurring\Api\Data\RecurringSubscriptionsInterface
     */
    public function setEndDate($endDate);

    /**
     * Get valid till date of subscription
     *
     * @return string|null
     */
    public function getValidTill();

    /**
     * Set valid till date of subscription
     *
     * @param string $validTill
     * @return \Webkul\Recurring\Api\Data\RecurringSubscriptionsInterface
     */
    public function setValidTill($validTill);

    /**
     * Get payment code of subscription
     *
     * @return string|null
     */
    public function getPaymentCode();

    /**
     * Set payment code of subscription
     *
     * @param string $paymentCode
     * @return \Webkul\Recurring\Api\Data\RecurringSubscriptionsInterface
     */
    public function setPaymentCode($paymentCode);

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
     * @return \Webkul\Recurring\Api\Data\RecurringSubscriptionsInterface
     */
    public function setStatus($status);

    /**
     * Get Discount Managed
     *
     * @return int|null
     */
    public function getDiscountManaged();

    /**
     * Set Discount Managed
     *
     * @param int $discountManaged
     * @return \Webkul\Recurring\Api\Data\RecurringSubscriptionsInterface
     */
    public function setDiscountManaged($discountManaged);

    /**
     * Get Extra
     *
     * @return string|null
     */
    public function getExtra();

    /**
     * Set Extra
     *
     * @param string $extra
     * @return \Webkul\Recurring\Api\Data\RecurringSubscriptionsInterface
     */
    public function setExtra($extra);

    /**
     * Get Created At
     *
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * Set Created At
     *
     * @param string $createdAt
     * @return \Webkul\Recurring\Api\Data\RecurringSubscriptionsInterface
     */
    public function setCreatedAt($createdAt);

    /**
     * Get Cancellation reason
     *
     * @return string|null
     */
    public function getCancellationReason();

    /**
     * Set cancellation reason
     *
     * @param string $reason
     * @return \Webkul\Recurring\Api\Data\RecurringSubscriptionsInterface
     */
    public function setCancellationReason($reason);
}
