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
namespace Webkul\Recurring\Model\Plans;

use \Magento\Framework\Registry as ProductRegistry;
use \Magento\Framework\Pricing\Helper\Data as FormatPrice;

/**
 * Data provide (plans and terms)
 */
class DataProvider
{
    /**
     * @var ProductRegistry
     */
    protected $registry;

    /**
     * @var \Webkul\Recurring\Model\ResourceModel\Plans\Collection
     */
    private $plansCollection;

    /**
     * @var \Webkul\Recurring\Model\ResourceModel\RecurringTerms\Collection
     */
    private $terms;

    /**
     * @var FormatPrice
     */
    private $priceHelper;

    /**
     * @var FormatPrice
     */
    private $storeManager;

    /**
     * @param \Webkul\Recurring\Model\ResourceModel\RecurringProductPlans\CollectionFactory $plansCollection
     * @param \Webkul\Recurring\Model\RecurringTerms $terms
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param ProductRegistry $registry
     * @param FormatPrice $priceHelper
     */
    public function __construct(
        \Webkul\Recurring\Model\ResourceModel\RecurringProductPlans\CollectionFactory $plansCollection,
        \Webkul\Recurring\Model\RecurringTerms $terms,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        ProductRegistry $registry,
        FormatPrice $priceHelper
    ) {
        $this->plansCollection = $plansCollection;
        $this->registry        = $registry;
        $this->storeManager    = $storeManager;
        $this->priceHelper     = $priceHelper;
        $this->terms           = $terms;
    }

    /**
     * It returns array of plans and its terms.
     *
     * @return array
     */
    public function toArray()
    {
        $returnArray    = [];
        $storeId = $this->storeManager->getStore()->getId();
        $product = $this->registry->registry('current_product');
        $productId = $product->getId();
        $type = $product->getTypeId();
        
        if ($type == 'configurable') {
            $collection = $this->plansCollection->create()
                    ->addFieldToFilter('status', true)
                    ->addFieldToFilter('parent_product_id', $productId)
                    ->addFieldToFilter("store_id", ["eq" => $storeId])
                    ->setOrder("sort_order", "ASC");
            if ($collection->getSize() == 0) {
                $storeId = 0;
                $collection = $this->plansCollection->create()
                ->addFieldToFilter('status', true)
                ->addFieldToFilter('parent_product_id', $productId)
                ->addFieldToFilter("store_id", ["eq" => $storeId])
                ->setOrder("sort_order", "ASC");
            }
        } else {
            $collection = $this->plansCollection->create()
            ->addFieldToFilter('status', true)
            ->addFieldToFilter('product_id', $productId)
            ->addFieldToFilter("store_id", ["eq" => $storeId])
            ->setOrder("sort_order", "ASC");

            if ($collection->getSize() == 0) {
                $storeId = 0;
                $collection = $this->plansCollection->create()
                    ->addFieldToFilter('status', true)
                    ->addFieldToFilter('product_id', $productId)
                    ->addFieldToFilter("store_id", ["eq" => $storeId])
                    ->setOrder("sort_order", "ASC");
            }
        }
       
        $typeIds = [];
        foreach ($collection as $model) {
            $initialFee = $subscriptionCharge = '';
            if (!in_array($model->getType(), $typeIds)) {
                $typeIds[] = $model->getType();
                list(
                    $flag,
                    $durationTitle
                ) = $this->checkTermIsEnabled($model->getType());
                if ($flag) {
                    $initialFee = $model->getInitialFee();
                    $subscriptionCharge = $model->getSubscriptionCharge();
                    $data                           = $model->getData();
                    $formattedCharge                = $this->priceHelper->currency($subscriptionCharge, true, false);
                    $formattedFee                   = $this->priceHelper->currency($initialFee, true, false);
                    $data['durationTitle']          = $durationTitle;
                    $data['initial_fee']            = $initialFee;
                    $data['subscription_charge']    = $subscriptionCharge;
                    $data['initial_fee_formatted']  = $formattedFee;
                    $data['subscription_charge_formatted']  = $formattedCharge;
                    $returnArray[] = $data;
                }
            }
        }
        return $returnArray;
    }

    /**
     * This function is used to check the term is enabled or not.
     *
     * @param int $type
     * @return bool
     */
    private function checkTermIsEnabled($type)
    {
        $model = $this->terms->load($type);
        if ($model->getStatus()) {
            return [true, $model->getData("title")];
        }
            return [false, $model->getData("title")];
    }
}
