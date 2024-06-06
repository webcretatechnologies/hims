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
namespace Webkul\Recurring\Controller\Subscription;

use Magento\Framework\Exception\LocalizedException;

/**
 * Webkul Recurring Landing page Index Controller.
 */
class Unsubscribe extends SubscriptionAbstract
{
    public const HOLD = 'hold';
    public const RESUME = 'resume';
    public const CANCELED = 'canceled';
    /**
     * Get session
     *
     * @return \Webkul\Recurring\Controller\Subscription\sessionManager
     */
    protected function _getSession()
    {
        return $this->sessionManager;
    }

    /**
     * Execute
     *
     * @return \Magento\Framework\Controller\Result\RedirectFactory
     * @throws LocalizedException
     */
    public function execute()
    {
        $postData = $this->getRequest()->getParams();
        $from           = isset($postData['from']) ? $postData['from'] : '';
        $id             = $postData['id'];
        $model          = $this->subscription;
        $resultRedirect = $this->resultRedirectFactory->create();
        $isError = 0;
        if ($id) {
            try {
                $model->load($id);
                if ($model->getCustomerId() != $this->customerSession->getCustomer()->getId()) {
                    $isError = 1;
                    $this->messageManager->addErrorMessage(__('Illegal Access.'));
                } elseif (!$model->getId()) {
                    $this->messageManager->addErrorMessage(__('This record no longer exists.'));
                } else {
                     $this->unsubscribe($model, $postData);
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
        
        if ($isError || $from == "manage") {
            $resultRedirect->setPath("recurring/subscription/manage");
            return $resultRedirect;
        }
        $resultRedirect->setPath("recurring/subscription/view", ["id" => $id]);
        return $resultRedirect;
    }
    
    /**
     * Unsubscribe profile
     *
     * @param \Webkul\Recurring\Controller\Subscription\Subscription $model
     * @param array $postData
     */
    private function unsubscribe($model, $postData)
    {
        try {
            $result = [];
            $cancellationReason = isset($postData['reason']) ? $postData['reason'] : '';
            $cancellationReason = $cancellationReason && $postData['reason'] == 'other_value' ?
            $postData['flag_other_reason'] : $cancellationReason;
            $actionType = isset($postData['type']) ? $postData['type'] : '';
            $errorFlag = 0;
            $holdSubscription = 0;
            $resumeSubscription = 0;
            $status = true;
            if ($model->getRefProfileId() != "" && $model->getStripeCustomerId() != "") {
                if ($actionType == self::HOLD && $this->stripeHelper->holdSubscriptions($model)) {
                    $cancellationReason = self::HOLD;
                    $holdSubscription = $this->saveModelData($model, $cancellationReason, $status);
                    $this->messageManager->addSuccessMessage(
                        __('This record is in Hold Successfully.')
                    );
                } elseif ($actionType == self::RESUME && $this->stripeHelper->resumeSubscriptions($model)) {
                    $cancellationReason = self::RESUME;
                    $resumeSubscription = $this->saveModelData($model, $cancellationReason, $status);
                    $this->messageManager->addSuccessMessage(
                        __('This record is Resumed Successfully.')
                    );
                } else {
                    if ($this->stripeHelper->cancelSubscriptions($model)) {
                        $status = false;
                        $errorFlag =  $this->saveModelData($model, $cancellationReason, $status);
                        $this->sendEmailForCancellation($model);
                        $this->messageManager->addSuccessMessage(
                            __('This subscription is Unsubscribed Successfully.')
                        );
                    }
                }
            } else {
                if ($model->getRefProfileId() == "") {
                    $model->setStatus(false);
                    $model->setCancellationReason($cancellationReason);
                    $model->setId($model->getId())->save();
                    $errorFlag = 0;
                    $this->messageManager->addSuccessMessage(
                        __('This subscription is Unsubscribed Successfully.')
                    );
                } else {
                    if ($actionType == self::HOLD && $this->paypalHelper->holdSubscriptions($model)) {
                        $cancellationReason = self::HOLD;
                        $holdSubscription = $this->saveModelData($model, $cancellationReason, $status);
                        $this->messageManager->addSuccessMessage(
                            __('This record is in Hold Successfully.')
                        );
                    } elseif ($actionType == self::RESUME && $this->paypalHelper->resumeSubscriptions($model)) {
                        $cancellationReason = self::RESUME;
                        $resumeSubscription = $this->saveModelData($model, $cancellationReason, $status);
                        $this->messageManager->addSuccessMessage(
                            __('This record is Resumed Successfully.')
                        );
                    } else {
                        if ($this->paypalHelper->cancelSubscriptions($model)) {
                            $status = false;
                            $errorFlag =  $this->saveModelData($model, $cancellationReason, $status);
                            $this->sendEmailForCancellation($model);
                            $this->logger->info('Unsubscribe'.$errorFlag);
                            $this->messageManager->addSuccessMessage(
                                __('This subscription is Unsubscribed Successfully.')
                            );
                        } else {
                            $this->messageManager->addErrorMessage(
                                __('Something went wrong.')
                            );
                        }
                    }
                }
            }
            if (!$errorFlag) {
                $result = [
                    'state' => self::CANCELED,
                    'success' => true
                ];
            }
            if (!$holdSubscription) {
                $result = [
                    'state' => self::HOLD,
                    'success' => true
                ];
            }
            if (!$resumeSubscription) {
                $result = [
                    'state' => self::RESUME,
                    'success' => true
                ];
            }
            if ($errorFlag || $holdSubscription || $resumeSubscription) {
                $result = [
                    'error' => true
                ];
                $this->messageManager->addErrorMessage(
                    __('Invalid profile status for cancel action.')
                );
            }
            return $result;
        } catch (\Exception $e) {
            $result = [
                'error' => true
            ];
            $this->dataHelper->logDataInLogger('unsubscribe'. $e->getMessage());
            return $result;
        }
    }

    /**
     * Save model data function
     *
     * @param array $model
     * @param string $cancellationReason
     * @param bool $status
     */
    protected function saveModelData($model, $cancellationReason, $status)
    {
        $errorFlag = 0;
        try {
            $model->setStatus($status);
            $model->setCancellationReason($cancellationReason);
            $model->setId($model->getId())->save();
            return $errorFlag;
        } catch (\Exception $e) {
            $errorFlag = 1;
            $this->dataHelper->logDataInLogger('saveModelData'. $e->getMessage());
            return $errorFlag;
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
