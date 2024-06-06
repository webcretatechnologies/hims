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
class ReturnAction extends PaypalAbstract
{
    /**
     * This function is used to get the quote data
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @return array
     */
    private function getQuoteData($quote)
    {
        $cartData            = $quote->getAllVisibleItems();
        $descriptionPlanInfo = $description =   '';
        $itemNameArray = [];
        $startDate          = date("Y-m-d H:i:s");
        $subscriptionsAmt   = $initialFee = $baseInitialFee = 0.0;
        $planId             = 0;
        foreach ($cartData as $item) {
            if ($additionalOptionsQuote =   $item->getOptionByCode('custom_additional_options')) {
                $itemNameArray[] = $item->getName();
                $allOptions = $this->jsonHelper->jsonDecode(
                    $additionalOptionsQuote->getValue()
                );
                foreach ($allOptions as $key => $option) {
                    if ($option['label'] == 'Start Date') {
                        $startDate = $option['value'];
                    }
                    if ($option['label'] == 'Subscription Charge') {
                        $subscriptionsAmt = round($item->getPriceInclTax() - $item->getDiscountAmount(), 2);
                    }
                    if ($option['label'] == 'Initial Fee') {
                        $initialFee = ((float)$initialFee) + $option['value'];
                    }
                    if ($option['label'] == 'Base Initial Fee') {
                        $baseInitialFee = ((float)$baseInitialFee) + $option['value'];
                    }
                    if ($option['label'] == 'Term Id') {
                        $planId = $option['value'];
                    }
                }
            }
        }
        $currentCurrencyCode = $this->storeManager->getStore()->getCurrentCurrency()->getCode();
        $initialFee = round($this->storeManager->getStore()->getBaseCurrency()
        ->convert($baseInitialFee, $currentCurrencyCode), 2);
        $description = implode(', ', $itemNameArray);
        $descriptionPlanInfo .= 'Start Date: '.$startDate.', Initial Fee: '.$initialFee
                                .', Subscription Charge: '.$subscriptionsAmt.', ';
        return [$description, $startDate, $subscriptionsAmt, $initialFee, $planId, $descriptionPlanInfo];
    }

    /**
     * Execute
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $paramsData         =  $this->getRequest()->getParams();
        $subscriptionId     = isset($paramsData['subscription_id']) ? $paramsData['subscription_id'] : '';
        $orderIncrementId   = isset($paramsData['orderId']) ? $paramsData['orderId'] : '';
        $responseData['PROFILEID'] = $subscriptionId;
        $detailsResponse = $responseData;
        $order =  $this->orderModel->loadByIncrementId($orderIncrementId);
        /** @var \Magento\Quote\Model\Quote  */
        $quote               = $this->quoteRepository->get($order->getQuoteId());
        $cartData            = $quote->getAllVisibleItems();
        $descriptionPlanInfo = '';
        $subscriptionsAmt    = $initialFee = 0.0;
        $grandTotal          = $order->getGrandTotal();
        list(
            $description, $startDate, $subscriptionsAmt, $initialFee, $planId, $descriptionPlanInfo
        ) = $this->getQuoteData($quote);

        $grandTotal         = number_format((float)$grandTotal, 2, ".", "");
        $initialFee         = number_format((float)$initialFee, 2, ".", "");
        $subscriptionsAmt   = $grandTotal - $initialFee;
        $shippingAmt        = number_format((float)$order->getShippingAmount(), 2, ".", "");
        $taxAmt             = number_format((float)$order->getTaxAmount(), 2, ".", "");

        if ($shippingAmt) {
            $subscriptionsAmt = $subscriptionsAmt - $shippingAmt;
            $descriptionPlanInfo .= 'Shipping: '.$shippingAmt;
        } else {
            $descriptionPlanInfo = rtrim($descriptionPlanInfo, ', ');
        }
        if ($taxAmt) {
            $subscriptionsAmt = $subscriptionsAmt - $taxAmt;
            $descriptionPlanInfo .= ', Tax: '.$taxAmt;
        }
        $discountAmount     = number_format((float)$order->getDiscountAmount(), 2, ".", "");
        if ($discountAmount < 0) {
            $discountAmount = -$discountAmount;
        }
        if ($discountAmount) {
            $descriptionPlanInfo .= ', Discount: '.$discountAmount;
        }
        $collection = $this->subscriptions->getCollection();
        $collection->addFieldToFilter('order_id', $order->getId());
        foreach ($collection as $model) {
            if ($model->getId()) {
                $this->saveRef($model, $subscriptionId, $planId);
            }
        }
        $order->setTotalPaid($order->getGrandTotal())
            ->setBaseTotalPaid($order->getBaseGrandTotal())
            ->save();
        $receiverInfo = [];
        $receiverInfo = [
            'name' => $order->getCustomerName(),
            'email' => $order->getCustomerEmail(),
        ];
        $orderItems = $order->getAllVisibleItems();
        $orderInfo = $this->dataHelper->getEmailTemplateVar($order, $orderItems);
        $emailTempVariables['orderItems'] = $orderInfo;
        $emailTempVariables['refProfileId'] = $subscriptionId;
        $emailTempVariables['customerName'] = $order->getCustomerName();

        $this->emailHelper->sendNewSubscriptionEmail(
            $emailTempVariables,
            $receiverInfo
        );
        $payerId = $responseData['PROFILEID'];
        $this->createInvoice($payerId, $order, $responseData, $detailsResponse);
        $resultRedirect = $this->resultRedirect->create(
            $this->resultRedirect::TYPE_REDIRECT
        );
        return $resultRedirect->setUrl(
            $this->urlBuilder->getUrl("checkout/onepage/success")
        );
    }

    /**
     * This function is used to save the paypal reference subscription id
     *
     * @param \Webkul\Recurring\Model\RecurringSubscriptions $model
     * @param integer $profileId
     * @param integer $planId
     */
    private function saveRef($model, $profileId, $planId)
    {
        $paymentCode = \Webkul\Recurring\Model\Paypal\PaymentMethod::CODE;
        $model->setData('ref_profile_id', $profileId);
        $model->setData('status', true);
        $model->setData('payment_code', $paymentCode);
        $model->setData('plan_id', $planId);
        $model->setId($model->getId());
        $model->save();
    }

    /**
     * This function is used to invoice the order
     *
     * @param integer $payerId
     * @param \Magento\Sales\Model\Order $order
     * @param array $responseData
     * @param array $detailsResponse
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function createInvoice($payerId, $order, $responseData, $detailsResponse)
    {
        $resultData = $this->convertValuesToJson(
            array_merge($responseData, $detailsResponse)
        );
        $payment = $order->getPayment();
        $payment->setTransactionId($payerId);
        $payment->setAdditionalInformation(
            [\Magento\Sales\Model\Order\Payment\Transaction::RAW_DETAILS => (array) $resultData]
        );
        $trans = $this->transactionBuilder;
        $transaction = $trans->setPayment($payment)
            ->setOrder($order)
            ->setTransactionId($payerId)
            ->setAdditionalInformation(
                [\Magento\Sales\Model\Order\Payment\Transaction::RAW_DETAILS => (array) $resultData]
            )
            ->setFailSafe(true)
            ->build(\Magento\Sales\Model\Order\Payment\Transaction::TYPE_CAPTURE);
        $payment->setParentTransactionId(null);
        $payment->save();
        $transaction->save();
       
        $order->setState(\Magento\Sales\Model\Order::STATE_PROCESSING)
                ->setStatus(\Magento\Sales\Model\Order::STATE_PROCESSING)
                ->save();
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
            $invoice->setTransactionId($payerId);
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
