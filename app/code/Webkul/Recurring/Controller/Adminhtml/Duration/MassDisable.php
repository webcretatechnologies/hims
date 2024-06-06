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
class MassDisable extends \Webkul\Recurring\Controller\Adminhtml\AbstractRecurring
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
        $plansModel  = $this->terms;
        $filterModel = $this->massFilter;
        $collection  = $filterModel->getCollection($plansModel->getCollection());
        foreach ($collection as $model) {
            $this->setStatus($model, parent::DISABLE);
        }
        $this->messageManager->addSuccessMessage(
            __('Duration Type(s) Disabled successfully.')
        );
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('*/*/');
    }
}
