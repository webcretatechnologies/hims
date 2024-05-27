<?php


namespace Hims\Testimonial\Model\System\Config;

class Value implements \Magento\Framework\Option\ArrayInterface
{
    protected $_options = array();
    protected $_ratting = array();

    public function toOptionArray()
    {
        if(!$this->_options){
            $options = array();
            $options["Approved"] = "Approved";
            $options["Rejected"] = "Rejected";
            $this->_options = $options;
        }
        return $this->_options;
    }
    public function toOptionArrayForRating()
    {
        if(!$this->_ratting){
            $options = array();
            $options[1] = "1";
            $options[2] = "2";
            $options[3] = "3";
            $options[4] = "4";
            $options[5] = "5";
            $this->_ratting = $options;
        }
        return $this->_ratting;
    }

}
