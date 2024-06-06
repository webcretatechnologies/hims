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
namespace Webkul\Recurring\Observer;
 
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;
use Webkul\Recurring\Model\RecurringProductPlans;
use Webkul\Recurring\Model\RecurringTerms;
use Magento\Framework\Pricing\Helper\Data as FormatPrice;
use Magento\Checkout\Model\Session as CheckoutSession;
use Webkul\Recurring\Api\Data\RecurringSubscriptionsInterface;
use Webkul\Recurring\Api\Data\RecurringProductPlansInterface;
use Webkul\Recurring\Api\Data\RecurringTermsInterface;
 
class SetAdditionalOptions implements ObserverInterface
{
    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    private $jsonHelper;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var RecurringProductPlans
     */
    protected $plans;

    /**
     * @var RecurringTerms
     */
    protected $term;
    
    /**
     * @var FormatPrice
     */
    protected $priceHelper;

    /**
     * @var CheckoutSession
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Webkul\Recurring\Helper\Data
     */
    private $helper;
    
    /**
     * @param \Magento\Customer\Model\Session $customerSession
     * @param RequestInterface $request
     * @param RecurringProductPlans $plans
     * @param RecurringTerms $term
     * @param FormatPrice $priceHelper
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param CheckoutSession $checkoutSession
     * @param \Webkul\Recurring\Helper\Data $helper
     */
    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        RequestInterface $request,
        RecurringProductPlans $plans,
        RecurringTerms $term,
        FormatPrice $priceHelper,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        CheckoutSession $checkoutSession,
        \Webkul\Recurring\Helper\Data $helper
    ) {
        $this->request          = $request;
        $this->customerSession  = $customerSession;
        $this->jsonHelper       = $jsonHelper;
        $this->priceHelper      = $priceHelper;
        $this->checkoutSession  = $checkoutSession;
        $this->plans            = $plans;
        $this->term             = $term;
        $this->helper           = $helper;
    }
 
    /**
     * Get additional options
     *
     * @param array $data
     * @return array
     */
    private function getAdditionalOption($data)
    {
        $additionalOptions = [];
        foreach ($data as $key => $value) {
            switch ($key) {
                case RecurringSubscriptionsInterface::PLAN_ID:
                    $customArray = $this->getCustomValues($key, $value, $data);
                    if (is_array($customArray)) {
                        $additionalOptions[] = $customArray;
                    }
                    break;
                case RecurringProductPlansInterface::INITIAL_FEE:
                    $customArray = $this->getCustomValues($key, $value, $data);
                    if (is_array($customArray)) {
                        $additionalOptions[] = $customArray;
                    }
                    break;
                case RecurringSubscriptionsInterface::START_DATE:
                    $customArray = $this->getCustomValues($key, $value, $data);
                    if (is_array($customArray)) {
                        $additionalOptions[] = $customArray;
                    }
                    break;
                case RecurringTermsInterface::TRAIL_STATUS:
                    $customArray = $this->getCustomValues($key, $value, $data);
                    if (is_array($customArray)) {
                        $additionalOptions[] = $customArray;
                    }
                    break;
                case RecurringTermsInterface::TRAIL_DAYS:
                    $customArray = $this->getCustomValues($key, $value, $data);
                    if (is_array($customArray)) {
                        $additionalOptions[] = $customArray;
                    }
                    break;
            }
        }
        return $additionalOptions;
    }
    /**
     * Execute
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            $fullActionName = $this->request->getFullActionName();
            $actionArray = [
                "checkout_cart_updateItemOptions",
                'checkout_cart_add'
            ];
            if (in_array($fullActionName, $actionArray) && $this->customerSession->isLoggedIn()) {
                $product = $observer->getProduct();
                $data = $this->request->getParams();
                if ($this->validate($data, RecurringSubscriptionsInterface::PLAN_ID)) {
                    if ($this->validate($data, RecurringSubscriptionsInterface::START_DATE)) {
                        $additionalOptions = $this->getAdditionalOption($data);
                        $observer->getProduct()->addCustomOption(
                            'additional_options',
                            $this->jsonHelper->jsonEncode($additionalOptions)
                        );
                    }
                } else {
                    $this->checkoutSession->setInitialFee('');
                }
            }
        } catch (\Exception $e) {
            $this->helper->logDataInLogger("Observer_SetAdditionalOptions_execute: ".$e->getMessage());
        }
    }

    /**
     * This function is used to validate the params data for subscriptions
     *
     * @param array $data
     * @param string $key
     * @return bool
     */
    private function validate($data, $key)
    {
        if (isset($data[$key]) && $data[$key] != '') {
            return true;
        }
        return false;
    }

    /**
     * This function return the custom options for selected plans terms and date
     *
     * @param string $key
     * @param mixed $value
     * @param array $data
     * @return array
     */
    private function getCustomValues($key, $value, $data)
    {
        $currencySymbol = $this->helper->getCurrencySymbol();
        if ($key == RecurringSubscriptionsInterface::PLAN_ID) {
            $model = $this->plans->load($value);
            if (!$model->getId()) {
                return '';
            }
            return [
                'label' => __("Subscription"),
                'value' => $model->getName()
            ];
        } elseif ($key == RecurringProductPlansInterface::INITIAL_FEE) {
            if (!$value) {
                $this->checkoutSession->setInitialFee('');
                return '';
            }
                $this->checkoutSession->setInitialFee($value);
            return [
                'label' => __("Initial fee"),
                'value' => $currencySymbol.$value
            ];
        } elseif ($key == RecurringTermsInterface::TERM_ID) {
            $model = $this->term->load($value);
            if (!$model->getId()) {
                return '';
            }
            return [
                'label' => $model->getTitle(),
                'value' => $model->getTitle() .'_'.$model->getId()
            ];
        } elseif ($key == RecurringSubscriptionsInterface::START_DATE) {
            if (!$value) {
                return '';
            }
            return [
                'label' => __("Start Date"),
                'value' => $value
            ];
        } elseif ($key == RecurringTermsInterface::TRAIL_STATUS) {
            if (!$value) {
                return '';
            }
            return [
                'label' => __("Free Trails"),
                'value' => 'Yes'
            ];
        } elseif ($key == RecurringTermsInterface::TRAIL_DAYS) {
            if (!$value) {
                return '';
            }
            return [
                'label' => __("Number of Trail Days"),
                'value' => $value
            ];
        }
        return [];
    }
}
