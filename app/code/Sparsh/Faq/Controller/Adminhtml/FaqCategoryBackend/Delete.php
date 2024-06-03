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
namespace Sparsh\Faq\Controller\Adminhtml\FaqCategoryBackend;

use Magento\Backend\App\Action;
use Sparsh\Faq\Model\ResourceModel\Faq\CollectionFactory as FaqCollectionFactory;
use Sparsh\Faq\Model\ResourceModel\FaqCategory as FaqCategoryResource;
use Sparsh\Faq\Model\FaqCategoryFactory;

/**
 * Class Delete
 *
 * @category Sparsh
 * @package  Sparsh_Faq
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
class Delete extends Action
{
    /**
     * Admin Resource
     *
     * @param string
     */
    const ADMIN_RESOURCE = 'Sparsh_Faq::sparsh_faq_category';

    /**
     * Faq category Model
     *
     * @param \Sparsh\Faq\Model\FaqCategoryFactory
     */
    protected $model;

    /**
     * FaqModel
     *
     * @var FaqFactory
     */
    protected $faqFactory;

    /**
     * FaqCategory ResourceModel
     *
     * @var FaqCategoryResource
     */
    protected $faqCategoryResource;

    /**
     * Delete constructor.
     *
     * @param Action\Context       $context             context
     * @param FaqCategory          $model               model
     * @param FaqCollectionFactory $faqFactory          faqFactory
     * @param FaqCategoryResource  $faqCategoryResource faqCategoryResource
     */
    public function __construct(
        Action\Context $context,
        FaqCategoryFactory $model,
        FaqCollectionFactory $faqFactory,
        FaqCategoryResource $faqCategoryResource
    ) {
        $this->model = $model;
        $this->faqFactory = $faqFactory;
        $this->faqCategoryResource = $faqCategoryResource;
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
            if ($id == 1) {
                $this->messageManager->addErrorMessage(
                    __('Default FAQ Category cannot be deleted.')
                );
                return $resultRedirect->setPath('*/*/');
            } else {
                try {
                    $faqCategoryModel = $this->model->create();
                    $this->faqCategoryResource->load($faqCategoryModel, $id);
                    $faqCollection = $this->faqFactory->create()
                        ->addFieldToFilter('faq_category_id', $id);
                    if (!empty($faqCollection)) {
                        $faqCollection->setDataToAll('faq_category_id', null);
                        $faqCollection->save();
                    }
                    $this->faqCategoryResource->delete($faqCategoryModel);
                    $this->messageManager->addSuccessMessage(
                        __('FAQ Category is deleted successfully.')
                    );
                    return $resultRedirect->setPath('*/*/');
                } catch (\Exception $e) {
                    $this->messageManager->addErrorMessage($e->getMessage());
                    return $resultRedirect->setPath('*/*/edit', ['id' => $id]);
                }
            }
        }
        $this->messageManager->addErrorMessage(
            __('We can\'t find FAQ Category to delete.')
        );
        return $resultRedirect->setPath('*/*/');
    }
}
