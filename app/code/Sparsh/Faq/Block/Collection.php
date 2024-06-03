<?php

namespace Sparsh\Faq\Block\Widget;

use Magento\Framework\View\Element\Template\Context;
use Magento\Widget\Block\BlockInterface;
use Sparsh\Faq\Model\ResourceModel\FaqCategory\CollectionFactory as FaqCollectionFactory;
use Sparsh\Faq\Model\FaqFactory;
use Sparsh\Faq\Model\FaqCategoryFactory;
use Sparsh\Faq\Model\ResourceModel\Faq\CollectionFactory;
use Sparsh\Faq\Helper\Data;
use Magento\Framework\View\Layout;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Element\Template;
use Magento\Framework\App\RequestInterface;

class Collection extends Template
{
    /**
     * FaqFactory
     *
     * @var \Sparsh\Faq\Model\FaqFactory
     */
    protected $faqFactory;

    /**
     * FaqCategoryFactory
     *
     * @var FaqCategoryFactory
     */
    protected $faqCategoryFactory;

    /**
     * FaqCollectionFactory
     *
     * @var CollectionFactory
     */
    protected $itemCollectionFactory;

    /**
     * FaqCategoryCollectionFactory
     *
     * @var FaqCollectionFactory
     */
    protected $faqitemCollectionFactory;

    /**
     * SparshFaq Helper
     *
     * @var Data
     */
    protected $helperData;

    /**
     * ResultPage
     *
     * @var Page
     */
    protected $pageResult;

    /**
     * Layout
     *
     * @var Layout
     */
    protected $layout;

    /**
     * Items
     *
     * @var items
     */
    protected $items;

    /**
     * Request
     *
     * @var RequestInterface
     */
    protected $request;

    /**
     * Faq constructor.
     *
     * @param Context              $context                  context
     * @param FaqFactory           $faqFactory               faqFactory
     * @param FaqCategoryFactory   $faqCategoryFactory       faqCategoryFactory
     * @param CollectionFactory    $itemCollectionFactory    itemCollectionFactory
     * @param FaqCollectionFactory $faqitemCollectionFactory faqitemCollectionFactory
     * @param Data                 $helperData               helperData
     * @param Layout               $layout                   layout
     * @param Page                 $pageResult               pageResult
     * @param RequestInterface     $request                  request
     * @param array                $data                     data
     */
    public function __construct(
        Context $context,
        FaqFactory $faqFactory,
        FaqCategoryFactory $faqCategoryFactory,
        CollectionFactory $itemCollectionFactory,
        FaqCollectionFactory $faqitemCollectionFactory,
        Data $helperData,
        Layout $layout,
        Page $pageResult,
        RequestInterface $request,
        array $data = []
    ) {
        $this->faqFactory = $faqFactory;
        $this->faqCategoryFactory = $faqCategoryFactory;
        $this->itemCollectionFactory = $itemCollectionFactory;
        $this->faqitemCollectionFactory = $faqitemCollectionFactory;
        $this->helperData = $helperData;
        $this->pageResult = $pageResult;
        $this->layout = $layout;
        $this->request = $request;
        parent::__construct($context, $data);
    }

   
    public function getFaqItems()
    {
        $faqCatId = $this->request->getParam('id') ? $this->request->getParam('id') : 1;
        if (!$this->items) {
            if (!$this->isCategoryNavbarEnable()) {
                $this->items = $this->itemCollectionFactory->create()
                    ->addFieldToSelect(
                        '*'
                    )->addFieldToFilter(
                        'is_active',
                        ['eq' => \Sparsh\Faq\Model\Faq::STATUS_ENABLED]
                    )
                    ->addStoreFilter($this->_storeManager->getStore()->getId())
                    ->setOrder('faq_id', 'asc')
                    ->addOrder('sort_order', 'asc')
                    ->addOrder('creation_time', 'desc');
            } else {
                $this->items = $this->itemCollectionFactory->create()
                    ->addFieldToSelect(
                        '*'
                    )->addFieldToFilter(
                        'is_active',
                        ['eq' => \Sparsh\Faq\Model\Faq::STATUS_ENABLED]
                    )->addFieldToFilter(
                        'faq_category_id',
                        $faqCatId
                    )
                    ->addStoreFilter($this->_storeManager->getStore()->getId())                    ->addStoreFilter($this->_storeManager->getStore()->getId())
                    ->setOrder('faq_id', 'asc')
                    ->addOrder('sort_order', 'asc')
                    ->addOrder('creation_time', 'desc');
            }
        }
        return $this->items;
    }

    /**
     * Get Faq is enabled or disabled
     *
     * @return string
     */
    public function isFaqEnabled()
    {
        return $this->helperData->getGeneralConfig('enable');
    }

    /**
     * Get Category Label Text
     *
     * @return string
     */
    public function getFaqListLabelText()
    {
        return $this->helperData->getFaqListConfig('faq_list_label_text');
    }

    /**
     * @return items|\Sparsh\Faq\Model\ResourceModel\FaqCategory\Collection
     */
    public function getFaqCategoryItems()
    {
        if (!$this->items) {
            $this->items = $this->faqitemCollectionFactory->create()
                ->addFieldToSelect(
                    '*'
                )->addFieldToFilter(
                    'is_active',
                    ['eq' => \Sparsh\Faq\Model\FaqCategory::STATUS_ENABLED]
                )->addOrder(
                    'sort_order',
                    'asc'
                )->addOrder(
                    'faq_category_name',
                    'asc'
                );
        }
        return $this->items;
    }

    /**
     * Is Category Navbar Enable
     *
     * @return boolean
     */
    public function isCategoryNavbarEnable()
    {
        return $this->helperData->getGeneralConfig('is_category_navbar_enable');
    }

    /**
     * Will return the currect page layout.
     *
     * @return string The current page layout.
     */
    public function getCurrentPageLayout()
    {
        $currentPageLayout = $this->pageResult->getConfig()->getPageLayout();

        if ($currentPageLayout === null) {
            return $this->layout->getUpdate()->getPageLayout();
        }

        return $currentPageLayout;
    }

    /**
     * Return Current Faq Id from request
     *
     * @return int
     */
    function getCurrentFaqCatId()
    {
        return $this->request->getParam('id');
    }
}
