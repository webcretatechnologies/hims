<?php
namespace Webcreta\ProductRecommendationQuiz\Controller\Adminhtml\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Webcreta\ProductRecommendationQuiz\Model\ProductRecommendationQuizFactory;

class Delete extends Action
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
    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    $logger = $objectManager->get('Psr\Log\LoggerInterface');
    $logger->log(100, "I am ready");
    
    $result = $this->jsonFactory->create();
    $postData = $this->getRequest()->getPostValue();
    $logger->log(100, print_r($postData, true));
    
    $id = $postData['id'];
    $logger->log(100, print_r($id, true));


    try {
        $existingEntry = $this->customDataFactory->create()->load($id);
        $logger->log(100, print_r($existingEntry->getData(), true));

        if ($existingEntry->getId()) {
            $questionSet = json_decode($existingEntry->getData('question_set'), true);
            $logger->info('Loaded question set: {questionSet}', ['questionSet' => json_encode($questionSet)]);

            $questionIdToRemove = $postData['question_id'][0];
            $optionIdToRemove = $postData['option_id'][0];
            $conditionIdToRemove = $postData['condition_id'][0];
            $next_question_id = 166;

            $logger->log(100, print_r($questionIdToRemove, true));
            $logger->log(100, print_r($optionIdToRemove, true));
            $logger->log(100, print_r($conditionIdToRemove, true));
            $logger->log(100, print_r($next_question_id, true));
            foreach ($questionSet as $key => $entry) {
                $logger->info('entery suucess ');

                $entryOptionId = is_array($entry['option_id']) ? $entry['option_id'] : explode(',', $entry['option_id']);
                $questionId = $entry['question_id'];
                $logger->log(100, print_r($questionId, true));

            if ( $entry['question_id'] == $questionIdToRemove &&
             $entry['option_id'] == $optionIdToRemove &&
             $entry['condition_id'] == $conditionIdToRemove ) {
                $logger->log(100, print_r("success", true));
            
                unset($questionSet[$key]);
            }
        }

            $existingEntry->setData('question_set', json_encode(array_values($questionSet)));
            $existingEntry->save();

            $result = $this->jsonFactory->create();
            $result->setData(['success' => true]);
        } else {
            $result = $this->jsonFactory->create();
            $result->setData(['success' => false, 'message' => 'Entry not found.']);
        }
    } catch (\Exception $e) {
        $logger->error('Error executing action: {message}', ['message' => $e->getMessage()]);
        $result = $this->jsonFactory->create();
        $result->setData(['success' => false, 'message' => 'An error occurred.']);
    }

    return $result;

}
}
