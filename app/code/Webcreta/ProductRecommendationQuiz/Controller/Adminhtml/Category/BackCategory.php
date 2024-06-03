<?php

namespace Webcreta\ProductRecommendationQuiz\Controller\Adminhtml\Category;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;

class BackCategory extends Action
{
    protected $resultFactory;

    public function __construct(
        Context $context,
        ResultFactory $resultFactory
    ) {
        parent::__construct($context);
        $this->resultFactory = $resultFactory;
    }

    public function execute()
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->getUrl('*/*/index'));
        return $resultRedirect;
    }
}
