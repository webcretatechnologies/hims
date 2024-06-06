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

namespace Webkul\Recurring\Observer;

use Magento\Framework\Event\ObserverInterface;
use Webkul\Recurring\Model\RecurringSubscriptions;

class SalesOrderCancelAfter implements ObserverInterface
{
    /**
     * @var RecurringSubscriptions
     */
    private $subscriptions;
    
    /**
     * @param Subscriptions $subscriptions
     */
    public function __construct(
        RecurringSubscriptions $subscriptions
    ) {
        $this->subscriptions    = $subscriptions;
    }

    /**
     * Observer action for Sales order cancel after.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $orderId = $observer->getOrder()->getId();
        $subscriptionsCol = $this->subscriptions->getCollection();
        $subscriptionsCol->addFieldToFilter('order_id', $orderId);
        foreach ($subscriptionsCol as $model) {
            $this->setStatus($model, $model->getId());
        }
    }

    /**
     * Updates the status of the subscription
     *
     * @param \Webkul\Recurring\Model\RecurringSubscriptions $model
     * @param integer $id
     */
    private function setStatus($model, $id)
    {
        $model->setStatus(false);
        $model->setId($id);
        $model->save();
    }
}
