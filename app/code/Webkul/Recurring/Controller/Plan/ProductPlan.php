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

class ProductPlan extends \Magento\Framework\App\Action\Action
{
    public const PRODUCT_PRICE = 1;
    public const FIXED_AMOUNT = 2;
    public const PERCENTAGE_DISCOUNT = 3;
    public const DAY = 'day';
    
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
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $priceCurrency;
    
    /**
     * Constructor function
     *
     * @param Context $context
     * @param \Webkul\Recurring\Model\ResourceModel\RecurringProductPlans\CollectionFactory $plansCollection
     * @param \Webkul\Recurring\Model\RecurringTerms $terms
     * @param \Webkul\Recurring\Logger\Logger $logger
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     */
    public function __construct(
        Context $context,
        \Webkul\Recurring\Model\ResourceModel\RecurringProductPlans\CollectionFactory $plansCollection,
        \Webkul\Recurring\Model\RecurringTerms $terms,
        \Webkul\Recurring\Logger\Logger $logger,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
    ) {
        $this->plansCollection = $plansCollection;
        $this->logger          = $logger;
        $this->terms           = $terms;
        $this->storeManager    = $storeManager;
        $this->productFactory  = $productFactory;
        $this->priceCurrency  = $priceCurrency;
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
        $subscriptionCharge = 0;
        $enableEndDate = false;
        $trailDays = 0;
        $trailStatus = false;
        try {
            $storeId = $this->storeManager->getStore()->getId();
            $planId = trim($this->getRequest()->getParam("planId", ""));
            $productId = $this->getRequest()->getParam("productId");
            $productPrice = $this->getRequest()->getParam("productPrice");
            if ($productPrice == '') {
                $product = $this->productFactory->create();
                $productPrice = $product->load($productId)->getPrice();
            }
            $collection = $this->plansCollection->create()
                        ->addFieldToFilter('status', true)
                        ->addFieldToFilter('product_id', ['in' =>$productId])
                        ->addFieldToFilter("store_id", $storeId)
                        ->addFieldToFilter("type", ["eq" => $planId]);
                       
            foreach ($collection->getData() as $coll) {
                $termId = $coll['type'];
                $model = $this->terms->load($termId);
                
                if ($model->getDurationType() == self::DAY) {
                    $enableEndDate = true;
                }
                $durationType = $model->getDurationType();
                if ($model->getFreeTrailStatus()) {
                    $trailStatus = true;
                    $trailDays = $model->getFreeTrailDays();
                }
                $discountType = $coll['discount_type'];
                if ($discountType == self::PRODUCT_PRICE) {
                    $subscriptionCharge = $productPrice;
                } elseif ($discountType == self::FIXED_AMOUNT) {
                    $subscriptionCharge = ($productPrice - $coll['subscription_charge']);
                } elseif ($discountType == self::PERCENTAGE_DISCOUNT) {
                    $percent = $coll['subscription_charge'];
                    $charge = ($productPrice*$coll['subscription_charge'])/100;
                    $subscriptionCharge = ($productPrice - $charge);
                }
                $subscriptionCharge = $this->priceCurrency->convert($subscriptionCharge, $storeId);
                $initialFee = $this->priceCurrency->convert($coll['initial_fee'], $storeId);
                $result = [
                    'success' => true,
                    'initialFee' => $initialFee,
                    'subscriptionCharge' => $subscriptionCharge,
                    'trailDay' => $trailDays,
                    'trailStatus' => $trailStatus,
                    'enableEndDate' => $enableEndDate,
                    'durationType' => $durationType
                ];
            }
        } catch (\Exception $e) {
            $result = [
                'error' => true
            ];
            $this->logger->info('ProductPlan controller '. $e->getMessage());
        }
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($result);
        return $resultJson;
    }
}
