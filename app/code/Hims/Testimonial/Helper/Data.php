<?php

namespace Hims\Testimonial\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Encryption\EncryptorInterface;

class Data extends AbstractHelper
{
    protected $encryptor;

    public function __construct(
        Context $context,
        EncryptorInterface $encryptor
    )
    {
        parent::__construct($context);
        $this->encryptor = $encryptor;
    }

    public function isEnable()
    {
          return $this->scopeConfig->getValue('testimonial/general/enable', ScopeInterface::SCOPE_STORE);
    }
    public function isFormEnable()
    {
        return $this->scopeConfig->getValue('testimonial/general/form_show', ScopeInterface::SCOPE_STORE);
    }
    // public function isYoutube()
    // {
    //     return $this->scopeConfig->getValue('testimonial/general/youtube', ScopeInterface::SCOPE_STORE);
    // }
    public function emailSubject()
    {
        return $this->scopeConfig->getValue('testimonial/general/emailsubject', ScopeInterface::SCOPE_STORE);
    }
    // public function isTwitter()
    // {
    //     return $this->scopeConfig->getValue('testimonial/general/twitter', ScopeInterface::SCOPE_STORE);
    // }
    // public function isFacebook()
    // {
    //     return $this->scopeConfig->getValue('testimonial/general/facebook', ScopeInterface::SCOPE_STORE);
    // }
    public function isRating()
    {
        return $this->scopeConfig->getValue('testimonial/general/stars', ScopeInterface::SCOPE_STORE);
    }
}