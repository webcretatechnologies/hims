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
use Magento\Sales\Model\OrderFactory;
use Magento\Quote\Model\QuoteRepository;

class SalesOrderPlaceAfter implements ObserverInterface
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var Magento\Sales\Model\OrderFactory;
     */
    protected $orderModel;

    /**
     * @var QuoteRepository
     */
    protected $quoteRepository;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $jsonHelper;

    /**
     * @var \Webkul\Recurring\Helper\Data
     */
    private $helper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param OrderFactory $orderModel
     * @param \Webkul\Recurring\Helper\Data $helper
     * @param \Magento\Framework\Serialize\Serializer\Json $jsonHelper
     * @param QuoteRepository $quoteRepository
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        OrderFactory $orderModel,
        \Webkul\Recurring\Helper\Data $helper,
        \Magento\Framework\Serialize\Serializer\Json $jsonHelper,
        QuoteRepository $quoteRepository,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->jsonHelper       = $jsonHelper;
        $this->helper           = $helper;
        $this->checkoutSession  = $checkoutSession;
        $this->orderModel       = $orderModel;
        $this->quoteRepository  = $quoteRepository;
        $this->storeManager     = $storeManager;
    }

    /**
     * Observer action for Sales order place after.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            $quoteId = $this->checkoutSession->getLastQuoteId();
            $quote = $this->quoteRepository->get($quoteId);
            $baseInitialFee = '';
            foreach ($quote->getAllVisibleItems() as $item) {
                if ($additionalOptionsQuote = $item->getOptionByCode('custom_additional_options')) {
                    $allOptions = $this->jsonHelper->unserialize(
                        $additionalOptionsQuote->getValue()
                    );
                    foreach ($allOptions as $key => $option) {
                        if ($option['label'] == 'Base Initial Fee') {
                            $baseInitialFee = ((float)$baseInitialFee) + $option['value'];
                        }
                    }
                }
            }

            $currentCurrencyCode = $this->storeManager->getStore()->getCurrentCurrency()->getCode();
            $initialFee = round($this->storeManager->getStore()->getBaseCurrency()
            ->convert($baseInitialFee, $currentCurrencyCode), 2);
            if ($initialFee != "") {
                $orderList = $observer->getOrders();
                $orderList = $orderList ? $orderList : [$observer->getOrder()];
                foreach ($orderList as $order) {
                    $orderId = $order->getId();

                    $order = $this->orderModel->create()->load($orderId);
                    $order->setInitialFee($initialFee)->save();
                    if ($order->getPayment()
                    ->getMethodInstance()
                    ->getCode() == \Webkul\Recurring\Model\Stripe\PaymentMethod::CODE ||
                    $order->getPayment()
                    ->getMethodInstance()
                    ->getCode() == \Webkul\Recurring\Model\Paypal\PaymentMethod::CODE) {
                        $order->setTotalPaid($order->getGrandTotal())
                            ->setBaseTotalPaid($order->getBaseGrandTotal())
                            ->save();
                    }
                }
            }
        } catch (\Exception $e) {
            $this->helper->logDataInLogger(
                "Observer_orderplace execute : ".$e->getMessage()
            );
        }
    }
}
