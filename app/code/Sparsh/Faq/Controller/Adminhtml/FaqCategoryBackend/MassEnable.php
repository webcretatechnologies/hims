<?php
/**
 * Class MassEnable
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

/**
 * Class MassEnable
 *
 * @category Sparsh
 * @package  Sparsh_Faq
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
class MassEnable extends \Magento\Backend\App\Action
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
     * CollectionFactory
     *
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;

    /**
     * MassEnable constructor.
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $date
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->date = $date;
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

        foreach ($collection as $item) {
            $item->setIsActive(true);
            $item->setUpdateTime($this->date->gmtDate());
            $item->save();
        }

        $this->messageManager->addSuccessMessage(
            __('A total of %1 record(s) have been enabled.', $collection->getSize())
        );

        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/');
    }
}
