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

/**
 * Webkul MpSpecialPromotions Landing page Index Controller.
 */
class Manage extends SubscriptionAbstract
{
    /**
     * Execute
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $subscriptionsLabel = __('My Subscriptions');
       
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set($subscriptionsLabel);

        return $resultPage;
    }
}
