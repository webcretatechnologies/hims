<?php

namespace Webcreta\ProductRecommendationQuiz\Controller\Adminhtml\Category;

use Webcreta\ProductRecommendationQuiz\Model\ProductRecommendationQuizCategoryFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Webcreta\ProductRecommendationQuiz\Model\ProductRecommendationQuizFactory;

class DeleteCategory extends Action
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
            $id = (int) $this->getRequest()->getParam('id'); // Ensure $id is an integer
    
            if ($id) {
                $model = $this->ProductRecommendationQuizCategoryFactory->create()->load($id);
                if ($model->getId()) {
                    $model->delete();
    
                    $productCollection = $this->ProductRecommendationQuizFactory->create()->getCollection()
                    ->addFieldToFilter('set_id', $id);
    
                    foreach ($productCollection as $productItem) {
                        if ($productItem->getId()) {
                            $productItem->delete();
                        }
                    }
    
                    $this->messageManager->addSuccessMessage(__("Record Delete Successfully."));
                } else {
                    $this->messageManager->addErrorMessage(__("Invalid ID provided or record not found."));
                }
            } else {
                $this->messageManager->addErrorMessage(__("ID parameter is missing or invalid."));
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e, __("We can't delete record, Please try again."));
        }
        return $resultRedirectFactory->setPath('*/category/index');
    }
    
}
