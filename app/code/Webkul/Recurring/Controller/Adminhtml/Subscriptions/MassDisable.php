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

/**
 * Recurring Adminhtml Plans massDelete Controller
 */
class MassDisable extends \Webkul\Recurring\Controller\Adminhtml\AbstractRecurring
{
    /**
     * Authorization level of a basic admin session
     */
    public const ADMIN_RESOURCE = 'Webkul_Recurring::subscriptions';
    
    /**
     * Execute
     *
     * @return \Magento\Framework\Controller\Result\RedirectFactory
     */
    public function execute()
    {
        $subscriptions  = $this->subscriptions;
        $filterModel = $this->massFilter;
        $collection  = $filterModel->getCollection($subscriptions->getCollection());
        $errorFlag   = 1;
        foreach ($collection as $model) {
            if ($model->getStatus() == parent::DISABLE) {
                if ($errorFlag != 0) {
                    $errorFlag = 2;
                }
            } else {
                if ($model->getRefProfileId() != "" && $model->getStripeCustomerId() != "") {
                    if ($this->stripeHelper->cancelSubscriptions($model)) {
                        $this->setStatus($model, parent::DISABLE);
                        $errorFlag = 0;
                    }
                }
                if ($model->getRefProfileId() == "") {
                    $this->setStatus($model, parent::DISABLE);
                    $errorFlag  = 0;
                } else {
                    if ($this->paypalHelper->cancelSubscriptions($model)) {
                        $this->setStatus($model, parent::DISABLE);
                        $errorFlag  = 0;
                    }
                }
            }
        }
        if ($errorFlag == 1) {
            $this->messageManager->addErrorMessage(
                __('Invalid profile status for cancel action.')
            );
            $this->messageManager->addNoticeMessage(
                __('Future Date Subscription(s).')
            );
        } elseif ($errorFlag == 0) {
            $this->messageManager->addSuccessMessage(
                __('Subscription(s) Unsubscribed successfully.')
            );
        } elseif ($errorFlag == 2) {
            $this->messageManager->addSuccessMessage(
                __('Subscription(s) already Unsubscribed')
            );
        }
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('*/*/');
    }
}
