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

/**
 * Recurring Adminhtml Plans massDelete Controller
 */
class MassDelete extends \Webkul\Recurring\Controller\Adminhtml\AbstractRecurring
{
    /**
     * Authorization level of a basic admin session
     */
    public const ADMIN_RESOURCE = 'Webkul_Recurring::term';
    
    /**
     * Execute
     *
     * @return \Magento\Framework\Controller\Result\RedirectFactory
     */
    public function execute()
    {
        $termsModel  = $this->terms;
        $filterModel = $this->massFilter;
        $collection  = $filterModel->getCollection($termsModel->getCollection());
        $collection->addFieldToFilter('entity_id', ["nin" => [1,2,3]]);
        if ($collection->getSize() == 0) {
            $this->messageManager->addNoticeMessage(
                __('Subscription Type(s) Can\'t be Deleted.')
            );
        } else {
            $this->messageManager->addSuccessMessage(
                __('Subscription Type(s) deleted successfully.')
            );
        }
        $collection->walk('delete');
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('*/*/');
    }
}
