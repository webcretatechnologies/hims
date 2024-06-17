<?php

namespace Webcreta\ProductRecommendationQuiz\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends Action
{
    protected $resultPageFactory;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $categoryId = $this->getRequest()->getParam('category_id');
                 $resultPage = $this->resultPageFactory->create();

//    print_r($categoryId);
//         $resultPage = $this->resultPageFactory->create();
        
//         $resultPage->getLayout()->getBlock('custom.block')->setCategoryId($categoryId);

        return $resultPage;
    }
}
