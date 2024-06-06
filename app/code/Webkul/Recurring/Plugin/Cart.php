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
namespace Webkul\Recurring\Plugin;

use Magento\Framework\Exception\LocalizedException;
use Webkul\Recurring\Model\RecurringProductPlansFactory as RecurringProductPlans;

class Cart
{
    /**
     * @var \Magento\Quote\Model\Quote
     */
    protected $quote;
    
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    private $jsonHelper;

    /**
     * @var \Webkul\Recurring\Helper\Data
     */
    private $helper;

    /**
     * @var \Webkul\Recurring\Model\RecurringProductPlans
     */
    private $plans;

    /**
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Webkul\Recurring\Helper\Data $helper
     * @param RecurringProductPlans $plans
     */
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Webkul\Recurring\Helper\Data $helper,
        RecurringProductPlans $plans
    ) {
        $this->quote = $checkoutSession->getQuote();
        $this->request = $request;
        $this->jsonHelper = $jsonHelper;
        $this->helper = $helper;
        $this->plans = $plans;
    }

    /**
     * Before add product
     *
     * @param \Magento\Checkout\Model\Cart $subject
     * @param mixed $productInfo
     * @param mixed $requestInfo
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function beforeAddProduct(
        \Magento\Checkout\Model\Cart $subject,
        $productInfo,
        $requestInfo = null
    ) {
        $params = $this->request->getParams();
        if (isset($params['subscription_qty']) && $params['subscription_qty']>0) {
            $requestInfo['qty'] = $params['subscription_qty'];
        }
        $quote = $this->quote;
        $cartData = $quote->getAllItems();
        $flag = 0;
        $productId = 0;
        if (array_key_exists('product', $params)) {
            $productId = $params['product'];
        }
        $multiSubscriptionEnabled = $this->helper->getConfig('general_settings/enable_multisubscription');
        if ($productId) {
            list(
                $subscriptionCount, $totalCount, $planId, $startDate, $flag
            ) = $this->getCartProductDetails($cartData, $flag);
            if ((isset($params['start_date']) && $params['start_date'] != "")
                                            &&
                (isset($params['plan_id']) && $params['plan_id'] != "")
            ) {
                $flag = 1;
                if ($multiSubscriptionEnabled && $totalCount == $subscriptionCount && $totalCount >= 1) {
                    $preTypeId = $this->plans->create()->load($planId)->getType();
                    $postTypeId = $this->plans->create()->load($params['plan_id'])->getType();
                    if ($preTypeId == $postTypeId && $startDate == $params['start_date']) {
                        return [$productInfo, $requestInfo];
                    } else {
                        throw new LocalizedException(
                            __('Start dates and Duration types of all added subscriptions must be same')
                        );
                    }
                }
            }
            if ($totalCount >= 1) {
                if ($flag == 1) {
                    if ($subscriptionCount >= 1) {
                        throw new LocalizedException(__('You can not add other product with subscription product'));
                    } else {
                        throw new LocalizedException(__('You can not add subscription product with other product'));
                    }
                }
            }
        }
        return [$productInfo, $requestInfo];
    }

    /**
     * Get details of cart added products
     *
     * @param array $cartData
     * @param Integer $flag
     * @return array
     */
    private function getCartProductDetails($cartData, $flag)
    {
        $subscriptionCount = 0;
        $totalCount = 0;
        $planId = "";
        $startDate = "";
        foreach ($cartData as $item) {
            $totalCount++;
            if ($customAdditionalOptionsQuote = $item->getOptionByCode('custom_additional_options')) {
                $flag = 1;
                $subscriptionCount++;
                $allOptions = $this->jsonHelper->jsonDecode($customAdditionalOptionsQuote->getValue(), true);
                foreach ($allOptions as $key => $option) {
                    if ($option['label'] == 'Plan Id') {
                        $planId = $option['value'];
                    }
                    if ($option['label'] == 'Start Date') {
                        $startDate = $option['value'];
                    }
                }
            }
        }
        return [$subscriptionCount, $totalCount, $planId, $startDate, $flag];
    }
}
