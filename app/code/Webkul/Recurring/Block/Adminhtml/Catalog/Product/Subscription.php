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
namespace Webkul\Recurring\Block\Adminhtml\Catalog\Product;

use \Magento\Backend\Block\Template as SubscriptionTemplate;
use \Webkul\Recurring\Model\RecurringProductPlansFactory;
use \Webkul\Recurring\Model\RecurringTerms;
use \Magento\Directory\Model\CurrencyFactory;
use Webkul\Recurring\Helper\Data as RecurringHelper;

class Subscription extends SubscriptionTemplate
{
    /**
     * @var string
     */
    protected $_template = "Webkul_Recurring::catalog/product/subscription.phtml";

    /**
     * @var RecurringProductPlansFactory
     */
    protected $plansFactory;

    /**
     * @var RecurringTerms
     */
    protected $durations;

    /**
     * @var CurrencyFactory
     */
    protected $currencyFactory;
      
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    public $request;

    /**
     * @var RecurringHelper
     */
    protected $recurringHelper;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $json;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * Construct function
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param RecurringProductPlansFactory $plansFactory
     * @param RecurringTerms $durations
     * @param CurrencyFactory $currencyFactory
     * @param RecurringHelper $recurringHelper
     * @param \Magento\Framework\Serialize\Serializer\Json $json
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        RecurringProductPlansFactory $plansFactory,
        RecurringTerms $durations,
        CurrencyFactory $currencyFactory,
        RecurringHelper $recurringHelper,
        \Magento\Framework\Serialize\Serializer\Json $json,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        array $data = []
    ) {
        $this->plansFactory     =  $plansFactory;
        $this->durations        =  $durations;
        $this->currencyFactory  =  $currencyFactory;
        $this->request          = $context->getRequest();
        $this->recurringHelper  = $recurringHelper;
        $this->json             = $json;
        $this->_productFactory  = $productFactory;
        parent::__construct($context, $data);
    }

    /**
     * Get duration collection
     *
     * @return array
     */
    public function getDurationColl()
    {
        $durationCollection = $this->durations->getCollection();
        $durationCollection->addFieldToFilter('status', ['eq' => true])
                           ->setOrder("sort_order", "ASC");
        return $durationCollection;
    }

    /**
     * Get active plans data
     *
     * @return array
     */
    public function getPlansData()
    {
        $storeId = (int)$this->getRequest()->getParam('store', 0);
        $durationCollection = $this->getDurationColl();
        $returnArray = [];
        $productId = $this->request->getParam('id');
        $count = 0;
        $symbol = $this->currencyFactory->create()->getCurrencySymbol();
        $availableEngines =  $this->getAvailableEngines();

        foreach ($durationCollection as $durationModel) {
            $collection = $this->plansFactory->create()->getCollection();
            $collection->addFieldToFilter('type', ['eq' => $durationModel->getId() ]);
            $collection->addFieldToFilter('store_id', ['eq' => $storeId ]);
            $returnArray[$count] = [
               'durationId'             => $durationModel->getId(),
               'durationTitle'          => $durationModel->getTitle(),
               'duration'               => $durationModel->getDuration(),
               'store_id'               => 0,
               'entity_id'              => '',
               'name'                   => '',
               'product_id'             => '',
               'initial_fee'            => $durationModel->getInitialFee(),
               'discount_type'          => '',
               'subscription_charge'    => '',
               'status'                 => false,
               'symbol'                 => $symbol,
               'availableEngines'       => $availableEngines,
               'selectedEngine'         => ''
            ];
            
            foreach ($collection as $model) {
                $initialFee = '';
                if ($productId == $model->getProductId()) {
                    $initialFee          = ($model->getInitialFee() >= 0) ? $model->getInitialFee() :  "";
                    $subscriptionCharge  = ($model->getSubscriptionCharge() != 0) ?
                                            $model->getSubscriptionCharge() :  "";
                    
                    $returnArray[$count]['entity_id']             = $model->getId();
                    $returnArray[$count]['name']                  = $model->getName();
                    $returnArray[$count]['product_id']            = $model->getProductId();
                    $returnArray[$count]['store_id']              = ($model->getStoreId())? $model->getStoreId() : 0;
                    $returnArray[$count]['initial_fee']           = $initialFee;
                    $returnArray[$count]['discount_type']         = $model->getDiscountType();
                    $returnArray[$count]['subscription_charge']   = $subscriptionCharge;
                    $returnArray[$count]['symbol']                = $symbol;
                    $returnArray[$count]['status']                = ($model->getStatus()) ? true :false;
                    $returnArray[$count]['availableEngines']      = $availableEngines;
                    $returnArray[$count]['selectedEngine']        = $model->getEngine();
                }
            }
            $count ++;
        }
        return $returnArray;
    }

    /**
     * Get configurable product plans
     *
     * @return array
     */
    public function getConfigProductPlanData()
    {
        $storeId = (int)$this->getRequest()->getParam('store', 0);
        $durationCollection = $this->getDurationColl();
        $planListArr = [];
        $configProductArr = [];
        $productId = $this->request->getParam('id');
        $count = 0;
        $symbol = $this->currencyFactory->create()->getCurrencySymbol();
        $availableEngines =  $this->getAvailableEngines();

        foreach ($durationCollection as $durationModel) {
            $collection = $this->plansFactory->create()->getCollection();
            $collection->addFieldToFilter('type', ['eq' => $durationModel->getId() ]);
            $collection->addFieldToFilter('store_id', ['eq' => $storeId ]);
            $planListArr[$count] = [
               'durationId'             => $durationModel->getId(),
               'durationTitle'          => $durationModel->getTitle(),
               'duration'               => $durationModel->getDuration(),
               'store_id'               => 0,
               'entity_id'              => '',
               'name'                   => '',
               'product_id'             => '',
               'initial_fee'            => $durationModel->getInitialFee(),
               'discount_type'          => '',
               'subscription_charge'    => '',
               'status'                 => false,
               'symbol'                 => $symbol,
               'availableEngines'       => $availableEngines,
               'selectedEngine'         => ''
            ];
           
            foreach ($collection as $model) {
                $initialFee = '';
                if ($productId == $model->getParentProductId()) {
                   
                    $initialFee  = ($model->getInitialFee() >= 0) ? $model->getInitialFee() :  "";
                    $subscriptionCharge  = ($model->getSubscriptionCharge() != 0) ?
                                            $model->getSubscriptionCharge() :  "";
                    $productSku = $this->_productFactory->create()->load($model->getProductId())->getSku();
                    $configProductArr[$productSku][$count] = [
                        'durationId'             => $durationModel->getId(),
                        'durationTitle'          => $durationModel->getTitle(),
                        'duration'               => $durationModel->getDuration(),
                        'store_id'               => ($model->getStoreId())? $model->getStoreId() : 0,
                        'entity_id'              =>  $model->getId(),
                        'name'                   => $model->getName(),
                        'product_id'             => $model->getProductId(),
                        'initial_fee'            => $initialFee,
                        'discount_type'          => $model->getDiscountType(),
                        'subscription_charge'    => $subscriptionCharge,
                        'status'                 => ($model->getStatus()) ? true :false,
                        'symbol'                 => $symbol,
                        'availableEngines'       => $availableEngines,
                        'selectedEngine'         => $model->getEngine()
                    ];
                }
            }
            $count ++;
        }
        return [$configProductArr, $planListArr];
    }

    /**
     * Get payment methods
     *
     * @return array
     */
    private function getAvailableEngines()
    {
        return [
            [
               'id' => 'paypal',
               'name' => __("PayPal Express")
            ]
        ];
    }

    /**
     * Get Recurring Helper
     *
     * @return \Webkul\Recurring\Helper\Data
     */
    public function getRecurringHelper()
    {
        return $this->recurringHelper;
    }

    /**
     * Get Json Serialize
     *
     * @param array $value
     * @return string
     */
    public function getJsonSerialize($value)
    {
        return $this->json->serialize($value);
    }

    /**
     * Get product
     *
     * @param int $id
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct($id)
    {
        return $this->_productFactory->create()->load($id);
    }
}
