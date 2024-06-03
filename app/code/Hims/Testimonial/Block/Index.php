<?php

namespace Hims\Testimonial\Block;
use Hims\Testimonial\Model\GridFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\View\Element\Template;

class Index extends Template
{
    protected $_gridFactory;
    protected $storeManager;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        GridFactory $gridFactory,
        StoreManagerInterface $storeManager,
        array $data = []
        
    )
    {
        $this->storeManager = $storeManager;
        $this->_gridFactory = $gridFactory;
        parent::__construct($context, $data);
    }

    public function Alltestimonial()
    {
        $storeId = $this->storeManager->getStore()->getId();
        $model = $this->_gridFactory->create()->getCollection()
        ->addFieldToFilter('status', 'Approved')
        ->addFieldToFilter('store', ['eq' => $storeId]);
        return $model;
    }


    public function getTotalReviews()
    {
        return $this->Alltestimonial()->count();
    }
    // public function getEmailIconUrl()
    // {
    //     return $this->getViewFileUrl('Hims_Testimonial::images/email.png');
    // }
    // public function getFacebookIconUrl()
    // {
    //     return $this->getViewFileUrl('Hims_Testimonial::images/facebook.png');
    // }
    // public function getTwitterIconUrl()
    // {
    //     return $this->getViewFileUrl('Hims_Testimonial::images/twitter.png');
    // }
    // public function getYoutubeIconUrl()
    // {
    //     return $this->getViewFileUrl('Hims_Testimonial::images/youtube.png');
    // }
    public function ImageUrl($img)
    {
        return $this->storeManager->getStore()->getBaseUrl(
            \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
        ) . '' . $img;
        
    }
  
}