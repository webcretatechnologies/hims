<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Recurring
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Recurring\Controller\Plan;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;

class GetPlanData extends \Magento\Framework\App\Action\Action
{
    
    /**
     * @var \Webkul\Recurring\Model\ResourceModel\RecurringProductPlans\CollectionFactory
     */
    protected $plansCollection;

    /**
     * @var \Webkul\Recurring\Logger\Logger
     */
    private $logger;
     
    /**
     * @var \Webkul\Recurring\Model\RecurringTermsFactory
     */
    private $terms;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;
    
    /**
     * Constructor function
     *
     * @param Context $context
     * @param \Webkul\Recurring\Model\ResourceModel\RecurringProductPlans\CollectionFactory $plansCollection
     * @param \Webkul\Recurring\Model\RecurringTerms $terms
     * @param \Webkul\Recurring\Logger\Logger $logger
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        \Webkul\Recurring\Model\ResourceModel\RecurringProductPlans\CollectionFactory $plansCollection,
        \Webkul\Recurring\Model\RecurringTerms $terms,
        \Webkul\Recurring\Logger\Logger $logger,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->plansCollection = $plansCollection;
        $this->logger          = $logger;
        $this->terms           = $terms;
        $this->storeManager    = $storeManager;
        parent::__construct($context);
    }

    /**
     * Get product plan data
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $result = [];
        $planName = [];
        try {
            $storeId = $this->storeManager->getStore()->getId();
            $productId = $this->getRequest()->getParam("productId");
            $collection = $this->plansCollection->create()
                        ->addFieldToFilter('status', true)
                        ->addFieldToFilter("store_id", $storeId)
                        ->addFieldToFilter('product_id', ['in' => $productId]);
            foreach ($collection->getData() as $coll) {
                $termId = $coll['type'];
                $model = $this->terms->load($termId);
                if ($model->getStatus()) {
                    $planName[] = [
                        $coll['type'].".".$coll['entity_id']  => $coll['name']
                    ];
                   
                }
            }
            $result = [
                'success' => true,
                'planName' => $planName
            ];
        } catch (\Exception $e) {
            $result = [
                'error' => true
            ];
            $this->logger->info('GetPlanData controller '. $e->getMessage());
        }
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($result);
        return $resultJson;
    }
}
