<?php
/**
 * Webkul Software.
 *
 * @category   Webkul
 * @package    Webkul_Recurring
 * @author     Webkul Software Private Limited
 * @copyright  Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\Recurring\Observer;

use Magento\Store\Model\StoreRepository;
use Magento\Framework\Event\ObserverInterface;

class ProductSaveAfter implements ObserverInterface
{
    public const PRODUCT_PRICE = 1;
    /**
     * @var StoreRepository
     */
    protected $storeRepository;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Webkul\Recurring\Model\RecurringProductPlansFactory
     */
    protected $planTypeFactory;

    /**
     * @var \Webkul\Recurring\Model\RecurringTermsFactory
     */
    protected $termsFactory;

    /**
     * @var \Webkul\Recurring\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;
    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    private $jsonHelper;
    /**
     * Construct function
     *
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Webkul\Recurring\Model\RecurringProductPlansFactory $planTypeFactory
     * @param \Webkul\Recurring\Model\RecurringTermsFactory $termsFactory
     * @param \Webkul\Recurring\Helper\Data $helper
     * @param StoreRepository $storeRepository
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     */
    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        \Webkul\Recurring\Model\RecurringProductPlansFactory $planTypeFactory,
        \Webkul\Recurring\Model\RecurringTermsFactory $termsFactory,
        \Webkul\Recurring\Helper\Data $helper,
        StoreRepository $storeRepository,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Framework\Json\Helper\Data $jsonHelper
    ) {
        $this->storeRepository = $storeRepository;
        $this->termsFactory    = $termsFactory;
        $this->helper          = $helper;
        $this->request         = $request;
        $this->planTypeFactory = $planTypeFactory;
        $this->productFactory = $productFactory;
        $this->jsonHelper = $jsonHelper;
    }

    /**
     * This function returns the array of website id and store id
     *
     * @return array
     */
    private function getStoreList()
    {
        $stores     = $this->storeRepository->getList();
        $storeList  = [];
        foreach ($stores as $store) {
            $websiteId = $store["website_id"];
            $storeId = $store["store_id"];
            $storeList[$websiteId][] = $storeId;
        }
        return $storeList;
    }

    /**
     * This function will return the current website Id
     *
     * @param int $currentStoreId
     * @return int
     */
    private function getCurrentWebsiteId($currentStoreId)
    {
        $stores     = $this->storeRepository->getList();
        foreach ($stores as $store) {
            if ($currentStoreId == $store["store_id"]) {
                return $store["website_id"];
            }
        }
        return 0;
    }

    /**
     * Values verification
     *
     * @param array $value
     * @return boolean
     */
    private function verifyRequest($value)
    {
        return $value['name'] && $value['discount_type']
        && $value['initial_fee'] != "" && $value['subscription_charge'] != "" ;
    }

    /**
     * Add quote item handler.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $planData    = $this->request->getParams();
        $product     = $observer->getProduct();
        $productId   = $product->getId();
        $storeId = 0;
       
        try {
            if (isset($planData["product"]["current_store_id"])) {
                $storeId     = $planData["product"]["current_store_id"];
            }
            if (isset($planData["product"]["subscriptionOnlyProduct"])) {
                $onlyForSubscription = $planData["product"]["subscriptionOnlyProduct"];
            }
            if ($product->getTypeId() == 'configurable') {
                if (!empty($planData['config-subscription-data'])) {
                    $configData = $this->jsonHelper->jsonDecode($planData['config-subscription-data']);
                    foreach ($configData as $sku => $configProduct) {
                        $product = $this->productFactory->create();
                        $product->load($product->getIdBySku($sku));
                        $childProductId = $product->getId();
                        $this->saveConfigurableProduct(
                            $configProduct,
                            $childProductId,
                            $storeId,
                            $productId,
                            $onlyForSubscription
                        );
                    
                    }
                }
            } else {
                if (isset($planData['plans'])) {
                    foreach ($planData['plans'] as $key => $value) {
                        if ($value['discount_type'] == self::PRODUCT_PRICE) {
                            $value['subscription_charge'] = 0;
                        } else {
                            $value['subscription_charge'] = $value['subscription_charge'] ?? ' ';
                        }
                        if ($storeId != 0) {
                            $value['initial_fee'] = $value['initial_fee'] ?? ' ';
                            $value['discount_type'] = $value['discount_type'] ?? ' ';
                        }
                        $requestFlag = $this->verifyRequest($value);
                        $this->saveData($requestFlag, $storeId, $key, $value, $productId, $onlyForSubscription);
                    }
                }
            }
            
        } catch (\Exception $e) {
            $this->helper->logDataInLogger(
                "Observer_ProductSave execute : ".$e->getMessage()
            );
        }
    }

    /**
     * Save Data
     *
     * @param bool $requestFlag
     * @param int $storeId
     * @param int $key
     * @param int $value
     * @param int $productId
     * @param bool $onlyForSubscription
     */
    protected function saveData($requestFlag, $storeId, $key, $value, $productId, $onlyForSubscription)
    {
        $recurrScope = $this->helper->getConfig('general_settings/price_scope');
        if ($requestFlag) {
            if ($storeId == 0) {
                $this->savePriceStoreWise($key, $value, $recurrScope, $productId, $onlyForSubscription);
            } else {
                $data = [
                    'id'                  => $value['id'],
                    'canUpdate'           => 1,
                    'name'                => strip_tags($value['name']),
                    'type'                => $key,
                    'selected'            => $value['selected'],
                    'product_id'          => $productId,
                    'status'              => ($value['selected']) ? 1 : 0,
                    'initial_fee'         => $value['initial_fee'],
                    'discount_type'       => $value['discount_type'],
                    'store_id'            => $storeId,
                    'website_id'          => $this->getCurrentWebsiteId($storeId),
                    'subscription_charge' => $value['subscription_charge'],
                    'engine'              => isset($value['engine']) ? $value['engine'] : "",
                    'only_for_subcription' => $onlyForSubscription,
                    'parent_product_id' => $productId
                ];
                $this->saveValues($productId, $data, $recurrScope);
            }
        }
    }

    /**
     * Save data for configurable product
     *
     * @param array $configProduct
     * @param int $childProductId
     * @param int $storeId
     * @param int $productId
     * @param bool $onlyForSubscription
     */
    protected function saveConfigurableProduct(
        $configProduct,
        $childProductId,
        $storeId,
        $productId,
        $onlyForSubscription
    ) {
        $recurrScope = $this->helper->getConfig('general_settings/price_scope');
        foreach ($configProduct as $key => $product) {
            if ($product['discount_type'] == self::PRODUCT_PRICE) {
                $product['subscription_charge'] = 0;
            } else {
                $product['subscription_charge'] = $product['subscription_charge'] ?? ' ';
            }
            if ($storeId != 0) {
                $product['initial_fee'] = $product['initial_fee'] ?? ' ';
                $product['discount_type'] = $product['discount_type'] ?? ' ';
            }
            $product['selected'] = 1;
            $product['parent_product_id'] = $productId;
            $requestFlag = $this->verifyRequest($product);
            if ($requestFlag) {
                if ($storeId == 0) {
                    $this->savePriceStoreWise(
                        $product['plan_id'],
                        $product,
                        $recurrScope,
                        $childProductId,
                        $onlyForSubscription
                    );
                } else {
                    $data = [
                        'id'                  => $product['id'],
                        'canUpdate'           => 1,
                        'name'                => strip_tags($product['name']),
                        'type'                => $product['plan_id'],
                        'selected'            => $product['selected'],
                        'product_id'          => $childProductId,
                        'status'              => ($product['selected']) ? 1 : 0,
                        'initial_fee'         => $product['initial_fee'],
                        'discount_type'       => $product['discount_type'],
                        'store_id'            => $storeId,
                        'website_id'          => $this->getCurrentWebsiteId($storeId),
                        'subscription_charge' => $product['subscription_charge'],
                        'only_for_subcription' => $onlyForSubscription,
                        'parent_product_id' => $productId
                    ];
                    $this->saveValues($childProductId, $data, $recurrScope);
                }
            }
        }
    }

    /**
     * This function is used to set the price values for website store and global
     *
     * @param integer $type
     * @param array $planParam
     * @param int $scope
     * @param integer $productId
     * @param bool $onlyForSubscription
     */
    private function savePriceStoreWise($type, $planParam, $scope, $productId, $onlyForSubscription)
    {
        $storeList = $this->getStoreList();
        $currentStoreId = $this->request->getParam('store', 0);
        $currentWebsiteId =  $this->getCurrentWebsiteId($currentStoreId);
        sort($storeList);
        $parentProductId = 0;
        if (isset($planParam['parent_product_id'])) {
            $parentProductId = $planParam['parent_product_id'];
        }
        foreach ($storeList as $websiteId => $stores) {
            if ($scope != 1 || $currentWebsiteId == $websiteId) {
                foreach ($stores as $storeId) {
                    $data = [
                            'id'                  => $planParam['id'],
                            'name'                => strip_tags($planParam['name']),
                            'type'                => $type,
                            'selected'            => $planParam['selected'],
                            'product_id'          => $productId,
                            'status'              => ($planParam['selected']) ? 1 : 0,
                            'initial_fee'         => $planParam['initial_fee'],
                            'discount_type'       => $planParam['discount_type'],
                            'store_id'            => $storeId,
                            'website_id'          => $websiteId,
                            'subscription_charge' => $planParam['subscription_charge'],
                            'only_for_subcription' => $onlyForSubscription,
                            'parent_product_id' => $parentProductId
                    ];
                    $this->saveValues($productId, $data, $scope);
                }
            }
        }
    }

    /**
     * This function is use to save the subscription types of the product
     *
     * @param integer $productId
     * @param array $data
     * @param integer $scopeFlag
     */
    private function saveValues($productId, $data, $scopeFlag)
    {
        $model = $this->planTypeFactory->create();
        $time = date('Y-m-d H:i:s');
        $returnValue = $this->validatePlans($productId, $data['type'], $data['id']);
        if ($data['initial_fee'] == 0) {
            $data['initial_fee'] = $data['initial_fee']."";
        }
        if ($returnValue["flag"]) {
            $data['id'] = $this->getExistingId($productId, $data);
            
            if ($data['id'] != "") {
                $model = $model->load($data['id']);
                if ($data['store_id'] != 0) {
                    if (($scopeFlag == 0 && isset($data['canUpdate']))
                        || ($scopeFlag == 1 && !isset($data['canUpdate']))
                    ) {
                        unset($data['initial_fee']);
                        unset($data['subscription_charge']);
                    }
                    if (!isset($data['canUpdate'])) {
                        unset($data['name']);
                    }
                }
                $data['update_time'] = $time;
                $data['sort_order'] = $returnValue["sort_order"];
                $model->setData($data);
                $model->setId($data['id']);
                $model->save();
            } else {
                $data['update_time'] = $time;
                $data['sort_order'] = $returnValue["sort_order"];
                $data['created_time'] = $time;
                $model->setData($data);
                $model->save();
            }
        }
    }

    /**
     * Existing plan Id
     *
     * @param integer $productId
     * @param array $data
     * @return string
     */
    private function getExistingId($productId, $data)
    {
        $collection = $this->planTypeFactory->create()->getCollection();
        $collection->addFieldToFilter("type", $data["type"]);
        $collection->addFieldToFilter("product_id", $productId);
        $collection->addFieldToFilter("store_id", $data["store_id"]);
        $collection->addFieldToFilter("website_id", $data["website_id"]);
        
        foreach ($collection as $model) {
            return $model->getId();
        }
        return '';
    }

    /**
     * This function is used to validate the plan and return the sort order accordingly
     *
     * @param integer $productId
     * @param integer $type
     * @param integer $id
     * @return array
     */
    private function validatePlans($productId, $type, $id)
    {
        $collection = $this->planTypeFactory->create()->getCollection();
        $collection->addFieldToFilter("product_id", $productId);
        $collection->addFieldToFilter("type", $type);
        if ($id != "") {
            $collection->addFieldToFilter("entity_id", $id);
        }
        $sortOrder = $this->termsFactory->create()->load($type)->getSortOrder();
        if ($collection->getSize()) {
            return [
                "sort_order" => $sortOrder,
                "flag" => true
            ];
        }
         return [
            "sort_order" => $sortOrder,
            "flag" => true
         ];
    }
}
