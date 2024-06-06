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
namespace Webkul\Recurring\Controller\Adminhtml\Subscriptions;

use Magento\Framework\Exception\LocalizedException;

/**
 * Webkul Recurring Landing page Index Controller.
 */
class Unsubscribe extends \Webkul\Recurring\Controller\Adminhtml\AbstractRecurring
{
    /**
     * Execute
     *
     * @return \Magento\Framework\Controller\Result\RedirectFactory
     * @throws LocalizedException
     */
    public function execute()
    {
        
        $postData       = $this->getRequest()->getParams();
        $id             = $postData['id'];
        $model          = $this->subscriptions;
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($id) {
            try {
                $model->load($id);
                if (!$model->getId()) {
                    $this->messageManager->addErrorMessage(__('This record no longer exists.'));
                } else {
                    $this->unsubscribe($model);
                }
            } catch (LocalizedException $e) {
                $this->logger->info('Unsubscribe'.$e->getMessage());
                $this->messageManager->addErrorMessage(__('Something went wrong.'));
                $isError = 1;
            } catch (\Exception $e) {
                $this->logger->info('Unsubscribe'.$e->getMessage());
                $this->messageManager->addErrorMessage(__('Something went wrong.'));
                $isError = 1;
            }
        }
        $resultRedirect->setPath("recurring/subscriptions/edit", ["id" => $id]);
        return $resultRedirect;
    }
    
    /**
     * Unsubscribe profile
     *
     * @param \Webkul\Recurring\Model\RecurringSubscriptions $model
     */
    private function unsubscribe($model)
    {
        $errorFlag = 1;
        if ($model->getRefProfileId() != "" && $model->getStripeCustomerId() != "") {
            if ($this->stripeHelper->cancelSubscriptions($model)) {
                $model->setStatus(false)->setId($model->getId())->save();
                $errorFlag = 0;
                $this->messageManager->addSuccessMessage(
                    __('This subscription is Unsubscribed Successfully.')
                );
                $this->sendEmailForCancellation($model);
            }
        }
        if ($model->getRefProfileId() == "") {
            $model->setStatus(false)->setId($model->getId())->save();
            $errorFlag = 0;
            $this->messageManager->addSuccessMessage(
                __('This subscription is Unsubscribed Successfully.')
            );
        } else {
            if ($this->paypalHelper->cancelSubscriptions($model)) {
                $model->setStatus(false)->setId($model->getId())->save();
                $errorFlag = 0;
                $this->messageManager->addSuccessMessage(
                    __('This subscription is Unsubscribed Successfully.')
                );
                $this->sendEmailForCancellation($model);
            }
        }
        if ($errorFlag) {
            $this->messageManager->addErrorMessage(
                __('Invalid profile status for cancel action.')
            );
            $this->messageManager->addNoticeMessage(
                __('Future Date Subscription(s).')
            );
        }
    }

    /**
     * Send mail for cancelled subscription function
     *
     * @param array $model
     */
    protected function sendEmailForCancellation($model)
    {
        $order = $this->order->load($model->getOrderId());
        $receiverInfo = [];
        $receiverInfo = [
            'name' => $order->getCustomerName(),
            'email' => $order->getCustomerEmail(),
        ];
        $emailTempVariables['productId'] = $model->getProductId();
        $emailTempVariables['productName'] = $model->getProductName();
        $emailTempVariables['refProfileId'] = $model->getRefProfileId();
        $emailTempVariables['customerName'] = $order->getCustomerName();

        $this->emailHelper->sendSubscriptionCancelEmail(
            $emailTempVariables,
            $receiverInfo
        );
    }
}
