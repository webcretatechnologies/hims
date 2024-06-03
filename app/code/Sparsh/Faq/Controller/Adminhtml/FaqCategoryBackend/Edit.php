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
namespace Sparsh\Faq\Controller\Adminhtml\FaqCategoryBackend;

use Magento\Backend\App\Action;
use Magento\Framework\View\Result\PageFactory;
use Sparsh\Faq\Model\FaqCategoryFactory;
use Sparsh\Faq\Model\ResourceModel\FaqCategory as FaqCategoryResource;
use Magento\Backend\Model\Session;

/**
 * Class Edit
 *
 * @category Sparsh
 * @package  Sparsh_Faq
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
class Edit extends Action
{
    /**
     * Result Page Factory
     *
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * Faq category Model
     *
     * @var \Sparsh\Faq\Model\FaqCategoryFactory
     */
    protected $model;

    /**
     * Faq category ResourceModel
     *
     * @var FaqCategoryResource
     */
    protected $faqCategoryResource;

    /**
     * BackendSession
     *
     * @var \Magento\Backend\Model\Session
     */
    protected $session;

    /**
     * Edit constructor.
     *
     * @param Action\Context      $context             context
     * @param PageFactory         $resultPageFactory   resultPageFactory
     * @param FaqCategoryFactory  $model               model
     * @param FaqCategoryResource $faqCategoryResource faqCategoryResource
     * @param Session             $session             session
     */
    public function __construct(
        Action\Context $context,
        PageFactory $resultPageFactory,
        FaqCategoryFactory $model,
        FaqCategoryResource $faqCategoryResource,
        Session $session
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->model = $model;
        $this->faqCategoryResource = $faqCategoryResource;
        parent::__construct($context);
        $this->session = $session;
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
        $faqCategoryModel = $this->model->create();
        if ($id) {
            $this->faqCategoryResource->load($faqCategoryModel, $id);
            if (!$faqCategoryModel->getId()) {
                $this->messageManager->addErrorMessage(
                    __('This category is no longer exists.')
                );
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
        }
        $data = $this->session->getFormData(true);
        if (!empty($data)) {
            $faqCategoryModel->setData($data);
        }
        $resultPage = $this->initPage($resultPage);
        $resultPage->addBreadcrumb(
            $id ? __('Edit FAQ Category') : __('Add New FAQ Category'),
            $id ? __('Edit FAQ Category') : __('Add New FAQ Category')
        );
        $resultPage->getConfig()->getTitle()
            ->prepend(
                $faqCategoryModel->
                getId() ? $faqCategoryModel->getName() : __('Add New FAQ Category')
            );

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
        $resultPage->setActiveMenu('Sparsh_Faq::sparsh_faq_category')
            ->addBreadcrumb(__('Manage FAQ Category'), __('Manage FAQ Category'))
            ->addBreadcrumb(__('Manage FAQ Category'), __('Manage FAQ Category'));

         return $resultPage;
    }

    /**
     * Check the permission to run it
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Sparsh_Faq::sparsh_faq_category');
    }
}
