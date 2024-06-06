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


namespace Webkul\Recurring\Api;

/**
 * RecurringSubscriptionsMappingRepository Repository Interface
 */
interface RecurringSubscriptionsMappingRepositoryInterface
{
    /**
     * Get by id
     *
     * @param int $id
     * @return \Webkul\Recurring\Model\RecurringSubscriptionsMapping
     */
    public function getById($id);
    /**
     * Save
     *
     * @param \Webkul\Recurring\Model\RecurringSubscriptionsMapping $subject
     * @return \Webkul\Recurring\Model\RecurringSubscriptionsMapping
     */
    public function save(\Webkul\Recurring\Model\RecurringSubscriptionsMapping $subject);
    /**
     * Get list
     *
     * @param Magento\Framework\Api\SearchCriteriaInterface $criteria
     * @return Magento\Framework\Api\SearchResults
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $criteria);
    /**
     * Delete
     *
     * @param \Webkul\Recurring\Model\RecurringSubscriptionsMapping $subject
     * @return boolean
     */
    public function delete(\Webkul\Recurring\Model\RecurringSubscriptionsMapping $subject);
    /**
     * Delete by id
     *
     * @param int $id
     * @return boolean
     */
    public function deleteById($id);
}
