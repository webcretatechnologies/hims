<?php

namespace Webcreta\ProductRecommendationQuiz\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\Controller\Result\JsonFactory;

class RedirectUrl extends Action
{
    protected $productRepository;
    protected $resultJsonFactory;

    public function __construct(
        Context $context,
        ProductRepository $productRepository,
        JsonFactory $resultJsonFactory
    ) {
        $this->productRepository = $productRepository;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $productId = $this->getRequest()->getParam('productId');
        $productUrl = '';
        if ($productId) {
            $product = $this->productRepository->getById($productId);
            if ($product->getId()) {
                $productUrl = $product->getProductUrl();
            }
        }
        
        $result = $this->resultJsonFactory->create();
        return $result->setData(['productUrl' => $productUrl]);
    }
}
