<?php

namespace Hims\Testimonial\Block;

use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Action\Action;
use Magento\Framework\View\Element\Template;

class Form extends Template
{
    protected $resultPageFactory;
    protected $extensionFactory;
    protected $_storeManager;
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        PageFactory $resultPageFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        array $data = []
    )
    {
        $this->_storeManager = $storeManager;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context, $data);
    }
}