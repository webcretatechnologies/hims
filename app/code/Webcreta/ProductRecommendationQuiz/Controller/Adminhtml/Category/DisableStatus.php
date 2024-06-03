<?php

namespace Webcreta\ProductRecommendationQuiz\Controller\Adminhtml\Category;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\RedirectFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\View\Result\PageFactory;
use Webcreta\ProductRecommendationQuiz\Model\ProductRecommendationQuizCategoryFactory;

class DisableStatus extends \Magento\Backend\App\Action
{
    protected $resultPageFactory;
    protected $resultJsonFactory;
    protected $productRecommendationCategoryFactory;
    protected $resultRedirectFactory;
    protected $messageManager;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        JsonFactory $resultJsonFactory,
        RedirectFactory $resultRedirectFactory,
        ProductRecommendationQuizCategoryFactory $productRecommendationCategoryFactory,
        ManagerInterface $messageManager
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->productRecommendationCategoryFactory = $productRecommendationCategoryFactory;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->messageManager = $messageManager;
        parent::__construct($context);
    }

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $ids = $this->getRequest()->getParam('selected', []);

        if (!is_array($ids) || empty($ids)) {
            $this->messageManager->addErrorMessage(__('Please select item(s).'));
            return $resultRedirect->setPath('*/*/index'); // Redirect to your listing page
        }

        try {
            foreach ($ids as $id) {
                $item = $this->productRecommendationCategoryFactory->create()->load($id);
                $item->setStatus(0); // Assuming 0 is the status for disabled
                $item->save();
            }
            $this->messageManager->addSuccessMessage(__('Total of %1 record(s) were disabled.', count($ids)));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        return $resultRedirect->setPath('*/*/index'); // Redirect to your listing page
    }
}
