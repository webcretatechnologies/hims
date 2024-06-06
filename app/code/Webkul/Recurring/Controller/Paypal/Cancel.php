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
class Cancel extends PaypalAbstract
{
    /**
     * Execute
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        $paramsData = $this->getRequest()->getParams();
        $orderIncrementId = $paramsData['orderId'] ?? '';
        $order = $this->orderModel->loadByIncrementId($orderIncrementId);
        try {
            $order->cancel();
            $order->save();
            // send mail for failed subscription
            $receiverInfo = [];
            $orderInfo= '';
            $receiverInfo = [
                'name' => $order->getCustomerName(),
                'email' => $order->getCustomerEmail(),
            ];
            $orderItems = $order->getAllItems();
            $orderInfo = $this->dataHelper->getEmailTemplateVar($order, $orderItems);
            $emailTempVariables['orderItems'] = $orderInfo;
            $emailTempVariables['customerName'] = $order->getCustomerName();

            $this->emailHelper->sendSubscriptionFailedEmail(
                $emailTempVariables,
                $receiverInfo
            );
        } catch (\Exception $e) {
             throw new \Magento\Framework\Exception\LocalizedException(__($e));
        }
        $this->messageManager->addErrorMessage(__("Something went wrong with the payment."));
        
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirect->create(
            $this->resultRedirect::TYPE_REDIRECT
        );
        $resultRedirect->setUrl(
            $this->urlBuilder->getUrl("checkout/onepage/failure")
        );
        return $resultRedirect;
    }
}
