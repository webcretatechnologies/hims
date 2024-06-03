<?php
/**
 * Class Edit
 *
 * PHP version 8.2
 *
 * @category Sparsh
 * @package  Sparsh_Faq
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
namespace Sparsh\Faq\Controller\Adminhtml\Faqbackend;

/**
 * Class Edit
 *
 * @category Sparsh
 * @package  Sparsh_Faq
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
class Edit extends \Magento\Backend\App\Action
{
    /**
     * Result Page Factory
     *
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * Faq Model
     *
     * @param \Sparsh\Faq\Model\FaqFactory
     */
    protected $model;

    /**
     * Faq ResourceModel
     *
     * @param \Sparsh\Faq\Model\ResourceModel\Faq
     */
    protected $faqResource;

    /**
     * Edit Constructor
     *
     * @param \Magento\Backend\App\Action\Context        $context           context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory ResultpageFactory
     * @param \Sparsh\Faq\Model\FaqFactory           $model             FaqModel
     * @param \Sparsh\Faq\Model\ResourceModel\Faq    $faqResource       faqResource
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Sparsh\Faq\Model\FaqFactory $model,
        \Sparsh\Faq\Model\ResourceModel\Faq $faqResource
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->model = $model;
        $this->faqResource = $faqResource;

        parent::__construct($context);
    }

    /**
     * Edit action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {

        $resultPage = $this->resultPageFactory->create();
        $id = $this->getRequest()->getParam('id');
        $faqModel = $this->model->create();
        $this->faqResource->load($faqModel, $id);
        $this->initPage($resultPage)->getConfig()->getTitle()->prepend(__('Edit FAQ'));
        $resultPage->setActiveMenu('Magento_Backend::faq_settings');
        return $resultPage;
    }

    /**
     * Init page
     *
     * @param \Magento\Backend\Model\View\Result\Page $resultPage resultpage
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function initPage($resultPage)
    {
         $resultPage->setActiveMenu('Sparsh_Faq::faq')
             ->addBreadcrumb(__('FAQ'), __('Manage FAQ'))
             ->addBreadcrumb(__('FAQ'), __('Manage FAQ'));

         return $resultPage;
    }

    /**
     * Check the permission to run it
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Sparsh_Faq::sparsh_faq');
    }
}
