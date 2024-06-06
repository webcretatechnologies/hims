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
interface RecurringSubscriptionsMappingInterface
{
    /**
     * Constants for keys of data array.
     * Identical to the name of the getter in snake case
     */
    public const ENTITY_ID = 'entity_id';
    public const SUBSCRIPTION_ID = 'subscription_id';
    public const ORDER_ID = 'order_id';
    public const CREATED_AT = 'created_at';

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
     * @return \Webkul\Recurring\Api\Data\RecurringSubscriptionsMappingInterface
     */
    public function setId($id);

    /**
     * Get Subscription Id
     *
     * @return int|null
     */
    public function getSubscriptionId();

    /**
     * Set Subscription Id
     *
     * @param int $subscriptionId
     * @return \Webkul\Recurring\Api\Data\RecurringSubscriptionsMappingInterface
     */
    public function setSubscriptionId($subscriptionId);

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
     * @return \Webkul\Recurring\Api\Data\RecurringSubscriptionsMappingInterface
     */
    public function setOrderId($orderId);

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
     * @return \Webkul\Recurring\Api\Data\RecurringSubscriptionsMappingInterface
     */
    public function setCreatedAt($createdAt);
}
