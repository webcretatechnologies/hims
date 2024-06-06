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

class Orders extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     */
    public const ADMIN_RESOURCE = 'Webkul_Recurring::subscriptions';
    
    /**
     * @var \Magento\Framework\View\Result\LayoutFactory
     * */
    private $resultLayoutFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    private $coreRegistry = null;

    /**
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
    ) {
        $this->coreRegistry = $coreRegistry;
        $this->resultLayoutFactory = $resultLayoutFactory;
        parent::__construct($context);
    }
    
    /**
     * Recurring order grid
     *
     * @return \Magento\Framework\View\Result\Layout
     */
    public function execute()
    {
        $subscriptionId = (int)$this->getRequest()->getParam('id');

        if ($subscriptionId) {
            $this->coreRegistry->register('subscription_id', $subscriptionId);
        }
        $resultLayout = $this->resultLayoutFactory->create();
        return $resultLayout;
    }
}
