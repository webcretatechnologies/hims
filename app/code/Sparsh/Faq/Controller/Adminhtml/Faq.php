<?php
/**
 * Class Faq
 *
 * PHP version 8.2
 *
 * @category Sparsh
 * @package  Sparsh_Faq
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
namespace Sparsh\Faq\Controller\Adminhtml;

/**
 * Class Faq
 *
 * @category Sparsh
 * @package  Sparsh_Faq
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
abstract class Faq extends \Magento\Backend\App\Action
{
    /**
     * Faq Model
     *
     * @param \Sparsh\Faq\Model\Faq
     */
    protected $model;

    /**
     * Faq constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Sparsh\Faq\Model\FaqFactory $model
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Sparsh\Faq\Model\FaqFactory $model
    ) {
        $this->model = $model;
        parent::__construct($context);
    }

    /**
     * Init page
     *
     * @param \Magento\Backend\Model\View\Result\Page $resultPage Resultpage
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function initPage($resultPage)
    {
        $resultPage->setActiveMenu('Sparsh_Faq::sparsh_faq')
            ->addBreadcrumb(__('FAQ'), __('FAQ'))
            ->addBreadcrumb(__('Items'), __(''));
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

    /**
     * InitAttachment
     *
     * @return mixed Attachnebtobject
     */
    protected function initFaqData()
    {
        $faqId = (int)$this->getRequest()->getParam('id');

        $faqId = $faqId ? $faqId : (int)$this->getRequest()->getParam('faq_id');

        $faqModel = $this->model->create();

        if ($faqId) {
            $faqModel->load($faqId);
        }

        return $faqModel;
    }
}
