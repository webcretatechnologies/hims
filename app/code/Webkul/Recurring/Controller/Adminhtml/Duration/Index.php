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
namespace Webkul\Recurring\Controller\Adminhtml\Duration;

use Webkul\Recurring\Controller\Adminhtml\AbstractRecurring;

class Index extends AbstractRecurring
{
    /**
     * Authorization level of a basic admin session
     */
    public const ADMIN_RESOURCE = 'Webkul_Recurring::term';
    
     /**
      * Plans list
      *
      * @return \Magento\Backend\Model\View\Result\Page
      */
    public function execute()
    {
        $pageLabel = __("Duration Type");
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__($pageLabel));
        return $resultPage;
    }
}
