<?php

namespace Hims\Testimonial\Model;

use Hims\Testimonial\Api\Data\GridInterface;

class Grid extends \Magento\Framework\Model\AbstractModel implements GridInterface
{

    const CACHE_TAG = 'hims_testimonial_record';

    protected $_cacheTag = 'hims_testimonial_record';

    protected $_eventPrefix = 'hims_testimonial_record';

    protected function _construct()
    {
        $this->_init('Hims\Testimonial\Model\ResourceModel\Grid');
    }
    //testimonial id 
    public function getTestimonialId()
    {
        return $this->getData(self::TESTIMONIAL_ID);
    }
    public function setTestimonialId($testimonial_id)
    {
        return $this->setData(self::TESTIMONIAL_ID, $testimonial_id);
    }

    //testimonial status
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }
 
    //testimonial date
    public function getDate()
    {
        return $this->getData(self::DATE);
    }
    public function setDate($date)
    {
        return $this->setData(self::DATE, $date);
    }

    //testimonial update_time
    public function getUpdateTime()
    {
        return $this->getData(self::UPDATE_TIME);
    }
    public function setUpdateTime($update_time)
    {
        return $this->setData(self::UPDATE_TIME, $update_time);
    }

    //testimonial name
    public function getName()
    {
        return $this->getData(self::NAME);
    }
    public function setName($name)
    {
        return $this->setData(self::NAME, $name);
    }

    //testimonial email
    public function getEmail()
    {
        return $this->getData(self::EMAIL);
    }
    public function setEmail($email)
    {
        return $this->setData(self::EMAIL, $email);
    }

    //testimonial message
    public function getMessage()
    {
        return $this->getData(self::MESSAGE);
    }
    public function setMessage($message)
    {
        return $this->setData(self::MESSAGE, $message);
    }

    //testimonial youtube
    // public function getYoutube()
    // {
    //     return $this->getData(self::YOUTUBE);
    // }
    // public function setYoutube($youtube)
    // {
    //     return $this->setData(self::YOUTUBE, $youtube);
    // }

    //testimonial twitter
    // public function getTwitter()
    // {
    //     return $this->getData(self::TWITTER);
    // }
    // public function setTwitter($twitter)
    // {
    //     return $this->setData(self::TWITTER, $twitter);
    // }

    //testimonial facebook
    // public function getFacebook()
    // {
    //     return $this->getData(self::FACEBOOK);
    // }
    // public function setFacebook($facebook)
    // {
    //     return $this->setData(self::FACEBOOK, $facebook);
    // }

    //testimonial image
    public function getImage()
    {
        return $this->getData(self::IMAGE);
    }
    public function setImage($image)
    {
        return $this->setData(self::IMAGE, $image);
    }

    //testimonial rating
    public function getRating()
    {
        return $this->getData(self::RATING);
    }
    public function setRating($rating)
    {
        return $this->setData(self::RATING, $rating);
    }

    //testimonial store
    public function getStore()
    {
        return $this->getData(self::STORE);
    }
    public function setStore($store)
    {
        return $this->setData(self::STORE, $store);
    }
}
