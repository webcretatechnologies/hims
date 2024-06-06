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

use Webkul\Recurring\Controller\Adminhtml\AbstractRecurring as PlansController;
use Magento\Framework\Controller\ResultFactory;

/**
 * Recurring Adminhtml Plans Edit Controller
 */
class Edit extends PlansController
{
    /**
     * Authorization level of a basic admin session
     */
    public const ADMIN_RESOURCE = 'Webkul_Recurring::term';
    
    /**
     * Execute
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $params = $this->getRequest()->getParams();
        $model = $this->terms;
        $data = $this->backendSession->getFormData(true);

        if (isset($params['id']) && $params['id'] != "") {
            $model->load($params['id']);
        }
        if (!empty($data)) {
            $model->setData($data);
        }

        /* Terms data */
        $this->registry->register('recurring_data', $model);
        
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->getConfig()->getTitle()->prepend(__('Plans'));
        $resultPage->getConfig()->getTitle()->prepend(
            $model->getId() ? $model->getTitle() : __('New Duration Type')
        );

        return $resultPage;
    }
}
