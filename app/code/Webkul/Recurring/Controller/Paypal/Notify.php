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
namespace Webkul\Recurring\Controller\Paypal;

/**
 * Webkul Recurring Landing page Index Controller.
 */
class Notify extends PaypalAbstract
{
    /**
     * Execute
     */
    public function execute()
    {
        try {
            $postValue = $this->getRequest()->getParams();
            $isSandBox = $this->helper->getConfig(parent::SANDBOX);

            $transactionType = $postValue['txn_type'] ?? '';
            if ($transactionType == 'recurring_payment') {
                $postData = $postValue;
                
                $recurringPaymentId = $postValue['recurring_payment_id'] ?? '';
                $paymentStatus = $postValue['payment_status'];
                $txnId         = $postValue['txn_id'];
                $actionUrl     = $this->getActionUrl($isSandBox).'?cmd=_notify-validate';

                $this->curl->post($actionUrl, $postData);
                $response = $this->curl->getBody();
                
                $responseData = urldecode($response);
                if (strcmp($responseData, 'VERIFIED') == 0 && ($paymentStatus == "Completed")) {
                    $subscriptionsCollection = $this->subscriptions->getCollection();
                    $subscriptionsCollection->addFieldToFilter('ref_profile_id', $recurringPaymentId);
                    $todayDate = date('Y-m-d');
                    $orderId = $planId = $subscriptionId = 0;
                    foreach ($subscriptionsCollection as $subscription) {
                        $subscriptionId = $subscription->getId();
                        $planId         = $subscription->getPlanId();
                        $orderId        = $subscription->getOrderId();
                        $createdAt      = $subscription->getCreatedAt();
                    }
                    if ($planId && strpos($createdAt, $todayDate) === false) {
                        $order = $this->orderModel->load($orderId);
                        $this->createOrder($planId, $order, $subscriptionId, $txnId);
                    }
                }
            }
        } catch (\Exception $e) {
            $this->logger->info("Controller_Paypal_Notify execute : ".$e->getMessage());
        }
    }

    /**
     * Order for recurring subscription
     *
     * @param integer $planId
     * @param \Magento\Sales\Model\Order $order
     * @param integer $subscriptionId
     * @param string $txnId
     */
    private function createOrder($planId, $order, $subscriptionId, $txnId)
    {
        try {
            $plan = $this->cron->getRecurringProductPlans($planId);
            $result = $this->orderHelper->createMageOrder($order, $plan['title']);
            
            if (isset($result['error']) && $result['error'] == 0) {
                $this->cron->saveMapping($result['id'], $subscriptionId);
                $this->createInvoice($result['id'], $txnId);
            }
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
        }
    }

    /**
     * This function is used to invoice the order
     *
     * @param integer $id
     * @param string $txnId
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function createInvoice($id, $txnId)
    {
        $responseData = $this->getRequest()->getParams();
        $order = $this->orderModel->load($id);
        $resultData = $this->convertValuesToJson(
            $responseData
        );
        $payment = $order->getPayment();
        $payment->setTransactionId($txnId);
        $payment->setAdditionalInformation(
            [\Magento\Sales\Model\Order\Payment\Transaction::RAW_DETAILS => (array) $resultData]
        );
        $trans = $this->transactionBuilder;
        $transaction = $trans->setPayment($payment)
            ->setOrder($order)
            ->setTransactionId($txnId)
            ->setAdditionalInformation(
                [\Magento\Sales\Model\Order\Payment\Transaction::RAW_DETAILS => (array) $resultData]
            )
            ->setFailSafe(true)
            ->build(\Magento\Sales\Model\Order\Payment\Transaction::TYPE_CAPTURE);
        $payment->setParentTransactionId(null);
        $payment->save();
        $transaction->save();
        if ($order->getState() != null) {
            $order->setState($order->getState())
            ->setStatus($order->getState())
            ->save();
        } else {
            $order->setState(\Magento\Sales\Model\Order::STATE_PROCESSING)
            ->setStatus(\Magento\Sales\Model\Order::STATE_PROCESSING)
            ->save();
        }
        $history = $order->addStatusHistoryComment(
            $this->jsonHelper->jsonEncode($responseData)
        );
        $history->setIsCustomerNotified(true);
        try {
            if (!$order->canInvoice()) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Cannot create an invoice.')
                );
            }
            $invoice = $this->invoiceService->prepareInvoice($order);
            if (!$invoice->getTotalQty()) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Cannot create an invoice without products.')
                );
            }
            $invoice->setTransactionId($txnId);
            $invoice->setRequestedCaptureCase(
                \Magento\Sales\Model\Order\Invoice::CAPTURE_ONLINE
            );
            $invoice->register();
            $invoice->save();
            $transactionSave = $this->transaction
                ->addObject($invoice)
                ->addObject($invoice->getOrder());
            $transactionSave->save();
            $this->invoiceSender->send($invoice);
            $order->setState(\Magento\Sales\Model\Order::STATE_PROCESSING)
                ->setStatus(\Magento\Sales\Model\Order::STATE_PROCESSING)
                ->save();
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(__($e));
        }
    }

    /**
     * Convert values of array to json
     *
     * @param array $responseDataArray
     * @return array
     */
    public function convertValuesToJson($responseDataArray)
    {
        foreach ($responseDataArray as $key => $value) {
            $responseDataArray[$key] = $this->jsonHelper->jsonEncode($value);
        }
        return $responseDataArray;
    }
}
