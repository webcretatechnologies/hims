<?php

namespace Webcreta\ProductRecommendationQuiz\Controller\Adminhtml\Category;

use Webcreta\ProductRecommendationQuiz\Model\ProductRecommendationQuizCategoryFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\View\Result\PageFactory;

class SaveCategory extends Action
{
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        ProductRecommendationQuizCategoryFactory $ProductRecommendationQuizCategoryFactory,
        Validator $formKeyValidator,
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->ProductRecommendationQuizCategoryFactory = $ProductRecommendationQuizCategoryFactory;
        $this->formKeyValidator = $formKeyValidator;
        parent::__construct($context);
    }
    public function execute()
    {
        $resultPageFactory = $this->resultRedirectFactory->create();
        if (!$this->formKeyValidator->validate($this->getRequest())) {
            $this->messageManager->addErrorMessage(__("Form key is Invalidate"));
            return $resultPageFactory->setPath('*/category/index');
        }
        $data = $this->getRequest()->getPostValue();
      
        try {
            if ($data) {
               
                $model = $this->ProductRecommendationQuizCategoryFactory->create();
                $model->setData($data);
                $model->save();
                $this->messageManager->addSuccessMessage(__("Data Saved Successfully."));
                return $resultPageFactory->setPath('*/category/index');
                $buttondata = $this->getRequest()->getParam('back');
                if ($buttondata == 'add') {
                    return $resultPageFactory->setPath('*/category/addnewcategory');
                }
                if ($buttondata == 'close') {
                    return $resultPageFactory->setPath('*/category/index');
                }
                $id = $model->getId();
                return $resultPageFactory->setPath('*/category/addnewcategory', ['id' => $id]);
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e, __("We can't submit your request, Please try again."));
        }
        return $resultPageFactory->setPath('*/category/index');
    }

     
}
