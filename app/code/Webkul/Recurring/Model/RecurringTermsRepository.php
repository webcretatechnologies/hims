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

/**
 * RecurringTermsRepository Repo Class
 */
class RecurringTermsRepository implements \Webkul\Recurring\Api\RecurringTermsRepositoryInterface
{
    /**
     * @var \Webkul\Recurring\Model\RecurringTermsFactory
     */
    protected $modelFactory = null;

    /**
     * @var \Webkul\Recurring\Model\ResourceModel\RecurringTerms\CollectionFactory
     */
    protected $collectionFactory = null;

    /**
     * Initialize
     *
     * @param \Webkul\Recurring\Model\RecurringTermsFactory $modelFactory
     * @param \Webkul\Recurring\Model\ResourceModel\RecurringTerms\CollectionFactory $collectionFactory
     */
    public function __construct(
        \Webkul\Recurring\Model\RecurringTermsFactory $modelFactory,
        \Webkul\Recurring\Model\ResourceModel\RecurringTerms\CollectionFactory $collectionFactory
    ) {
        $this->modelFactory = $modelFactory;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Get by id
     *
     * @param int $id
     * @return \Webkul\Recurring\Model\RecurringTerms
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($id)
    {
        $model = $this->modelFactory->create()->load($id);
        if (!$model->getId()) {
            throw new \Magento\Framework\Exception\NoSuchEntityException(
                __('The data with the "%1" ID doesn\'t exist.', $id)
            );
        }
        return $model;
    }

    /**
     * Save
     *
     * @param \Webkul\Recurring\Model\RecurringTerms $subject
     * @return \Webkul\Recurring\Model\RecurringTerms
     */
    public function save(\Webkul\Recurring\Model\RecurringTerms $subject)
    {
        try {
            $subject->save();
        } catch (\Exception $exception) {
             throw new \Magento\Framework\Exception\CouldNotSaveException(__($exception->getMessage()));
        }
        return $subject;
    }

    /**
     * Get list
     *
     * @param Magento\Framework\Api\SearchCriteriaInterface $criteria
     * @return Magento\Framework\Api\SearchResults
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $criteria)
    {
        $collection = $this->collectionFactory->create();
        return $collection;
    }

    /**
     * Delete
     *
     * @param \Webkul\Recurring\Model\RecurringTerms $subject
     * @return boolean
     */
    public function delete(\Webkul\Recurring\Model\RecurringTerms $subject)
    {
        try {
            $subject->delete();
        } catch (\Exception $exception) {
            throw new \Magento\Framework\Exception\CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * Delete by id
     *
     * @param int $id
     * @return boolean
     */
    public function deleteById($id)
    {
        return $this->delete($this->getById($id));
    }
}
