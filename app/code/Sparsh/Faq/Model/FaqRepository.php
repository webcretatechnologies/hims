<?php
/**
 * Class FaqRepository
 *
 * PHP version 8.2
 *
 * @category Sparsh
 * @package  Sparsh_Faq
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
namespace Sparsh\Faq\Model;

use Sparsh\Faq\Api\FaqRepositoryInterface;
use Sparsh\Faq\Api\Data\FaqInterface;
use Sparsh\Faq\Model\FaqFactory;
use Sparsh\Faq\Model\ResourceModel\Faq\CollectionFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Api\SearchResultsInterfaceFactory;
use Sparsh\Faq\Model\ResourceModel\Faq;

/**
 * Class FaqRepository
 *
 * @category Sparsh
 * @package  Sparsh_Faq
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
class FaqRepository implements FaqRepositoryInterface
{
    /**
     * FaqFactory
     *
     * @var \Sparsh\Faq\Model\FaqFactory
     */
    protected $faqFactory;

    /**
     * DataPageFactory
     *
     * @var \Sparsh\Faq\Api\Data\FaqInterfaceFactory
     */
    protected $dataPageFactory;

    /**
     * DataObjectHelper
     *
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * DataObjectProcessor
     *
     * @var \Magento\Framework\Reflection\DataObjectProcessor
     */
    protected $dataObjectProcessor;

    /**
     * CollectionFactory
     *
     * @var \Sparsh\Faq\Model\ResourceModel\Faq\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * FaqResourceModel
     *
     * @var SearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * FaqResourceModel
     *
     * @var \Sparsh\Faq\Model\ResourceModel\Faq
     */
    protected $faqResource;

    /**
     * Constructor FaqRepository
     *
     * @param FaqFactory                    $faqFactory           faqFactory
     * @param CollectionFactory             $collectionFactory    collectionFactory
     * @param DataObjectHelper              $dataObjectHelper     dataObjectHelper
     * @param DataObjectProcessor           $dataObjectProcessor  dataObjectProcessor
     * @param \Sparsh\Faq\Api\Data\FaqInterfaceFactory           $dataPageFactory      dataPageFactory
     * @param SearchResultsInterfaceFactory $searchResultsFactory searchResultsFactory
     * @param Faq                           $faqResource          faqResource
     */
    public function __construct(
        FaqFactory $faqFactory,
        CollectionFactory $collectionFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        \Sparsh\Faq\Api\Data\FaqInterfaceFactory $dataPageFactory,
        SearchResultsInterfaceFactory $searchResultsFactory,
        Faq $faqResource
    ) {
        $this->faqFactory        = $faqFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataPageFactory = $dataPageFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->collectionFactory    = $collectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->faqResource = $faqResource;
    }

    /**
     * Save FaqData
     *
     * @param object $object \Sparsh\Faq\Api\Data\FaqInterface
     *
     * @return object
     * @throws Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(FaqInterface $object)
    {
        if (empty($object->getStoreId())) {
            $storeId = $this->storeManager->getStore()->getId();
            $object->setStoreId($storeId);
        }
        try {
            $this->resource->save($object);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(
                __(
                    'Could not save the FAQ: %1',
                    $e->getMessage()
                )
            );
        }
        return $object;
    }

    /**
     * Get Faq By Id
     *
     * @param int $id faqid
     *
     * @return object
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($id)
    {
        $faqModel = $this->faqFactory->create();
        $this->faqResource->load($faqModel, $id);
        if (!$faqModel->getId()) {
            throw new NoSuchEntityException(
                __('Object with id "%1" does not exist.', $id)
            );
        }
        return $faqModel;
    }

    /**
     * Delete Faq
     *
     * @param object $object \Sparsh\Faq\Api\Data\FaqInterface
     *
     * @return boolean
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(FaqInterface $object)
    {
        try {
            $object->delete();
        } catch (\Exception $e) {
            throw new CouldNotDeleteException(__($e->getMessage()));
        }
        return true;
    }
     
    /**
     * Delete Faq By Id
     *
     * @param int $id faqid
     *
     * @return void
     */
    public function deleteById($id)
    {
        return $this->delete($this->getById($id));
    }
}
