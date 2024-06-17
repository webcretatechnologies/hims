<?php

namespace Webcreta\ProductRecommendationQuiz\Block\Adminhtml\Customer\Edit;

use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;

class Tabs extends \Magento\Backend\Block\Template implements \Magento\Ui\Component\Layout\Tabs\TabInterface
{
    protected $categoryCollectionFactory;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        CollectionFactory $categoryCollectionFactory,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        parent::__construct($context, $data);
    }
    public function getCustomerId()
    {
        return $this->_coreRegistry->registry(\Magento\Customer\Controller\RegistryConstants::CURRENT_CUSTOMER_ID);
    }
    public function getTabLabel()
    {
        return __('Quiz Data');
    }
    public function getTabTitle()
    {
        return __('Quiz Data');
    }

    public function canShowTab()
    {
        if ($this->getCustomerId()) {
            return true;
        }
        return false;
    }
    public function isHidden()
    {
        if ($this->getCustomerId()) {
            return false;
        }
        return true;
    }
    public function getTabClass()
    {
        return '';
    }

    public function getTabUrl()
    {
        return $this->getUrl('productrecommendationquiz/customer/profile',['_current' => true]);
    }
    public function isAjaxLoaded()
    {
        return true;
    }

}
