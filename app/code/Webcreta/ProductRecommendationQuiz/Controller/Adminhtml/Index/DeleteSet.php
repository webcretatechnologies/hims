<?php
namespace Webcreta\ProductRecommendationQuiz\Controller\Adminhtml\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Webcreta\ProductRecommendationQuiz\Model\ProductRecommendationQuizFactory;

class DeleteSet extends Action
{
    protected $jsonFactory;
    protected $customDataFactory;

    public function __construct(
        Context $context,
        JsonFactory $jsonFactory,
        ProductRecommendationQuizFactory $customDataFactory
    ) {
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
        $this->customDataFactory = $customDataFactory;
    }

    public function execute()
    {
        $result = $this->jsonFactory->create();
        $data = $this->getRequest()->getPostValue();
        if (!empty($data['set_id'])) {
            $id = (int)$data['set_id'];
            try {
                $collection = $this->customDataFactory->create()->getCollection()
                    ->addFieldToFilter('set_id', $id);
    
                if ($collection->getSize() > 0) {
                    $item = $collection->getFirstItem();
                    $item->delete();
                    $result->setData(['success' => true]);
                } else {
                    $result->setData(['success' => false, 'message' => 'Entry not found.']);
                }
            } catch (\Exception $e) {
                $result->setData(['success' => false, 'message' => $e->getMessage()]);
            }
        } else {
            $result->setData(['success' => false, 'message' => 'ID not provided.']);
        }
        return $result;
    }
    
}
