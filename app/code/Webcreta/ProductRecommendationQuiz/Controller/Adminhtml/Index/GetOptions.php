<?php

namespace Webcreta\ProductRecommendationQuiz\Controller\Adminhtml\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Catalog\Model\Product;

class GetOptions extends Action
{
    protected $jsonFactory;
    protected $attributeRepository;
    protected $logger;

    public function __construct(
        Context $context,
        JsonFactory $jsonFactory,
        Attribute $attributeRepository,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->jsonFactory = $jsonFactory;
        $this->attributeRepository = $attributeRepository;
        $this->logger = $logger;
        parent::__construct($context);
    }

    public function execute()
    {

        $attributeId = $this->getRequest()->getParam('attribute_id');
        $this->logger->info("Attribute ID: " . $attributeId);

        $result = ['options' => []];
        try {
            $attribute = $this->attributeRepository->load($attributeId);

            $inputType = $this->getAttributeType($attributeId);

            if ($attribute) {
                $options = $attribute->getSource()->getAllOptions();

                foreach ($options as $option) {
                    $result['options'][] = [
                        'value' => $option['value'],
                        'label' => $option['label'],
                        'type' => $inputType,
                    ];
                }
            }
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            $result['error'] = $e->getMessage();
        }
        return $this->jsonFactory->create()->setData($result);
    }

    public function getAttributeType($questionId)
    {
        try {
            $attribute = $this->attributeRepository->load($questionId);
            return $attribute->getFrontendInput();
        } catch (\Exception $e) {
            $this->logger->error("An error occurred while getting attribute label: " . $e->getMessage());
            return null;
        }
    }

}
