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

use Magento\Framework\Event\ObserverInterface;
use Webkul\Recurring\Api\Data\RecurringProductPlansInterface;

class SalesQuoteAddItem implements ObserverInterface
{
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $jsonHelper;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Webkul\Recurring\Helper\Data
     */
    private $recurringHelper;

    /**
     * @var \Magento\Directory\Model\CurrencyFactory
     */
    protected $currencyFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Framework\Serialize\Serializer\Json $jsonHelper
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Webkul\Recurring\Helper\Data $recurringHelper
     */
    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Serialize\Serializer\Json $jsonHelper,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Webkul\Recurring\Helper\Data $recurringHelper
    ) {
        $this->customerSession = $customerSession;
        $this->request         = $request;
        $this->jsonHelper      = $jsonHelper;
        $this->checkoutSession = $checkoutSession;
        $this->currencyFactory = $currencyFactory;
        $this->storeManager    = $storeManager;
        $this->recurringHelper = $recurringHelper;
    }
    
    /**
     * Add quote item handler.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            if ($this->customerSession->isLoggedIn()) {
                $quoteItem = $observer->getQuoteItem();
                $quoteItemId = $quoteItem->getId();
                $additionalOptions = [];
                $count = 0;
                $planData = $this->request->getParams();
                
                if (isset($planData['plan_id']) && $planData['plan_id'] != '') {
                    $additionalOptions[] = [
                                'label' => 'Plan Id',// do not change
                                'value' => $planData['plan_id']
                            ];
                    $count ++;
                }
                if (isset($planData['term_id']) && $planData['term_id'] != '') {
                    $additionalOptions[] = [
                                'label' => 'Term Id',// do not change
                                'value' => $planData['term_id']
                            ];
                    $count ++;
                }
                if (isset($planData['start_date']) && $planData['start_date'] != '') {
                    $additionalOptions[] = [
                                'label' => 'Start Date',// do not change
                                'value' => $planData['start_date']
                            ];
                    $count ++;
                }
                if (isset($planData['end_date']) && $planData['end_date'] != '') {
                    $additionalOptions[] = [
                                'label' => 'End Date',// do not change
                                'value' => $planData['end_date']
                            ];
                    $count ++;
                }
                if (isset($planData['initial_fee']) && $planData['initial_fee'] != '') {
                    $additionalOptions[] = [
                                'label' => 'Initial Fee',// do not change
                                'value' => $planData['initial_fee']
                            ];
                    $baseInitialFee = $this->convertFromCurrentToBaseCurrency($planData['initial_fee']);
                    $additionalOptions[] = [
                                'label' => 'Base Initial Fee',// do not change
                                'value' => $baseInitialFee
                            ];
                    $count ++;
                }
                $customPrice = 0.0;
                if (isset($planData[RecurringProductPlansInterface::SUBSCRIPTION_CHARGE]) &&
                $planData[RecurringProductPlansInterface::SUBSCRIPTION_CHARGE] != '') {
                    $additionalOptions[] = [
                                'label' => 'Subscription Charge',// do not change
                                'value' => $planData[RecurringProductPlansInterface::SUBSCRIPTION_CHARGE]
                            ];
                    $customPrice = $planData[RecurringProductPlansInterface::SUBSCRIPTION_CHARGE];
                    $count ++;
                }
                $isFreeTrail = false;
                if (isset($planData['free_trail_status']) && $planData['free_trail_status'] != '') {
                    $additionalOptions[] = [
                                'label' => 'Free Trails',// do not change
                                'value' => 'Yes'
                            ];
                    $isFreeTrail = true;
                    $count ++;
                }
                if (isset($planData['free_trail_days']) && $planData['free_trail_days'] != '') {
                    $additionalOptions[] = [
                                'label' => 'Number of Trail Days',// do not change
                                'value' => $planData['free_trail_days']
                            ];
                    $count ++;
                }
                $this->addCustomAdditionalOptions($additionalOptions, $customPrice, $quoteItemId, $count, $isFreeTrail);
            }
        } catch (\Exception $e) {
            $this->recurringHelper->logDataInLogger(
                'Observer_SalesQuoteAddItem execute : Notice: '.$e->getMessage()
            );
        }
    }

    /**
     * Add Custom Additional Options to Subscription Products
     *
     * @param array $additionalOptions
     * @param float $customPrice
     * @param integer $quoteItemId
     * @param integer $count
     * @param bool $isFreeTrail
     */
    private function addCustomAdditionalOptions($additionalOptions, $customPrice, $quoteItemId, $count, $isFreeTrail)
    {
        /** @var \Magento\Quote\Model\Quote  */
        $quote = $this->checkoutSession->getQuote();
        if ($quote && $count > 1) {
            $cartData = $quote->getAllVisibleItems();
            foreach ($cartData as $item) {
                $itemId = $item->getId();
                if ($quoteItemId == $itemId) {
                    $item->addOption(
                        [
                            'product_id' => $item->getProductId(),
                            'code' => 'custom_additional_options',
                            'value' => $this->jsonHelper->serialize($additionalOptions)
                        ]
                    );
                    if ($customPrice) {
                        $item = ( $item->getParentItem() ? $item->getParentItem() : $item );
                        $price = $customPrice; //set your price here
                        $item->setCustomPrice($price);
                        $item->getProduct()->setIsSuperMode(true);
                    }
                }
            }
        }
    }

    /**
     * Convert an amount from current currency to base currency
     *
     * @param  float $amount
     * @return float
     */
    public function convertFromCurrentToBaseCurrency($amount)
    {
        $currencyCodeTo = $this->storeManager->getStore()->getCurrentCurrency()->getCode();
        $currencyToRate = $this->storeManager->getStore()->getBaseCurrency()->getRate($currencyCodeTo);
        $rate = 1/$currencyToRate;
        return $amount * $rate;
    }
}
