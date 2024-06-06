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
 * RecurringTermsRepository Repository Interface
 */
interface RecurringTermsRepositoryInterface
{
    /**
     * Get by id
     *
     * @param int $id
     * @return \Webkul\Recurring\Model\RecurringTerms
     */
    public function getById($id);
    /**
     * Save
     *
     * @param \Webkul\Recurring\Model\RecurringTerms $subject
     * @return \Webkul\Recurring\Model\RecurringTerms
     */
    public function save(\Webkul\Recurring\Model\RecurringTerms $subject);
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
     * @param \Webkul\Recurring\Model\RecurringTerms $subject
     * @return boolean
     */
    public function delete(\Webkul\Recurring\Model\RecurringTerms $subject);
    /**
     * Delete by id
     *
     * @param int $id
     * @return boolean
     */
    public function deleteById($id);
}
