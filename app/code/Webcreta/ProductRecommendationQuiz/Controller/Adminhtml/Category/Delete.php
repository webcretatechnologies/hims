<?php

namespace Webcreta\ProductRecommendationQuiz\Controller\Adminhtml\Category;

use Webcreta\ProductRecommendationQuiz\Model\ProductRecommendationQuizCategoryFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Webcreta\ProductRecommendationQuiz\Model\ProductRecommendationQuizFactory;

class Delete extends Action
{
    /**
     *
     * @var PageFactory
     */
    protected $resultPageFactory;
    /**
     *
     * @var ProductRecommendationQuizCategoryFactory
     */
    protected $ProductRecommendationQuizCategoryFactory;
      /**
     *
     * @var ProductRecommendationQuizFactory
     */
    protected $ProductRecommendationQuizFactory;

    /**
     * @param Context                  $context
     * @param PageFactory              $resultPageFactory
     * @param ProductRecommendationQuizCategoryFactory $ProductRecommendationQuizCategoryFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        ProductRecommendationQuizCategoryFactory $ProductRecommendationQuizCategoryFactory,
        ProductRecommendationQuizFactory $ProductRecommendationQuizFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->ProductRecommendationQuizCategoryFactory = $ProductRecommendationQuizCategoryFactory;        
        $this->ProductRecommendationQuizFactory = $ProductRecommendationQuizFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $resultRedirectFactory = $this->resultRedirectFactory->create();
        try {
            $id = $this->getRequest()->getParam('id');

            if ($id) {
                $model = $this->ProductRecommendationQuizCategoryFactory->create()->load($id);
                if ($model->getId()) {
                    $productCollection = $this->customDataFactory->create()->getCollection()
                    ->addFieldToFilter('set_id', $id);
                    
                    foreach ($productCollection as $productItem) {
                        $productItem->delete();
                    }

                    $model->delete();
                    $this->messageManager->addSuccessMessage(__("Record Delete Successfully."));
                } else {
                    $this->messageManager->addErrorMessage(__("Something went wrong, Please try again."));
                }
            } else {
                $this->messageManager->addErrorMessage(__("Something went wrong, Please try again."));
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e, __("We can't delete record, Please try again."));
        }
        return $resultRedirectFactory->setPath('*/category/index');
    }
}
