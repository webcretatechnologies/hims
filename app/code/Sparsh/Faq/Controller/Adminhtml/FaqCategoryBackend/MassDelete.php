<?php
/**
 * Class MassDelete
 *
 * PHP version 8.2
 *
 * @category Sparsh
 * @package  Sparsh_Faq
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
namespace Sparsh\Faq\Controller\Adminhtml\FaqCategoryBackend;

use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Sparsh\Faq\Model\ResourceModel\FaqCategory\CollectionFactory;
use Sparsh\Faq\Model\ResourceModel\Faq\CollectionFactory as FaqCollectionFactory;

/**
 * Class MassDelete
 *
 * @category Sparsh
 * @package  Sparsh_Faq
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
class MassDelete extends \Magento\Backend\App\Action
{
    /**
     * Admin Resource
     *
     * @param string
     */
    const ADMIN_RESOURCE = 'Sparsh_Faq::sparsh_faq_category';
    
    /**
     * Filter
     *
     * @var Filter
     */
    protected $filter;

    /**
     * FaqcategoryCollectionFactory
     *
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * FaqCollectionFactory
     *
     * @var FaqCollectionFactory
     */
    protected $faqFactory;

    /**
     * MassDelete constructor.
     *
     * @param Context              $context           context
     * @param Filter               $filter            filter
     * @param FaqCollectionFactory $faqFactory        faqFactory
     * @param CollectionFactory    $collectionFactory collectionFactory
     */
    public function __construct(
        Context $context,
        Filter $filter,
        FaqCollectionFactory $faqFactory,
        CollectionFactory $collectionFactory
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->faqFactory = $faqFactory;
        parent::__construct($context);
    }

    /**
     * Execute action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     *
     * @throws \Magento\Framework\Exception\LocalizedException|\Exception
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $collectionSize = $collection->getSize();
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        foreach ($collection as $item) {
            if ($item ['faq_category_id'] == 1) {
                $this->messageManager->addErrorMessage(
                    __('Default FAQ Category cannot be deleted.')
                );
                return $resultRedirect->setPath('*/*/');
            }
        }

        foreach ($collection as $item) {
            $id = $item->getId();
            $faqCollection = $this->faqFactory->create()
                ->addFieldToFilter('faq_category_id', $id);
            if (!empty($faqCollection)) {
                $faqCollection->setDataToAll('faq_category_id', null);
                $faqCollection->save();
            }
            $item->delete();
        }
        $this->messageManager->addSuccessMessage(
            __('A total of %1 record(s) have been deleted.', $collectionSize)
        );
        return $resultRedirect->setPath('*/*/');
    }
}
