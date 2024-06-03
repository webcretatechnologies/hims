<?php
/**
 * Class Delete
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

use Magento\Backend\App\Action;

/**
 * Class Delete
 *
 * @category Sparsh
 * @package  Sparsh_Faq
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
class Delete extends \Magento\Backend\App\Action
{
    /**
     * Admin Resource
     *
     * @param string
     */
    const ADMIN_RESOURCE = 'Sparsh_Faq::sparsh_faq';

    /**
     * Faq Model
     *
     * @param \Sparsh\Faq\Model\Faq
     */
    protected $model;

    /**
     * Faq ResourceModel
     *
     * @param \Sparsh\Faq\Model\ResourceModel\Faq
     */
    protected $faqResource;

    /**
     * Delete constructor
     *
     * @param Action\Context                          $context     context
     * @param \Sparsh\Faq\Model\Faq               $model       $model Faqmodel
     * @param \Sparsh\Faq\Model\ResourceModel\Faq $faqResource faqResource
     */
    public function __construct(
        Action\Context $context,
        \Sparsh\Faq\Model\FaqFactory $model,
        \Sparsh\Faq\Model\ResourceModel\Faq $faqResource
    ) {
        $this->model = $model;
        $this->faqResource = $faqResource;
        parent::__construct($context);
    }

    /**
     * Delete action
     *
     * @return $this
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($id) {
            try {
                $faqModel = $this->model->create();
                $this->faqResource->load($faqModel, $id);
                $this->faqResource->delete($faqModel);
                $this->messageManager->addSuccessMessage(
                    __('FAQ has been deleted.')
                );
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                return $resultRedirect->setPath('*/*/edit', ['id' => $id]);
            }
        }
        $this->messageManager->addErrorMessage(
            __('We can\'t find FAQ to delete.')
        );
        return $resultRedirect->setPath('*/*/');
    }
}
