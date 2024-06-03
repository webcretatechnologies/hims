<?php
namespace Hims\Testimonial\Model\ResourceModel\Grid;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'testimonial_id';
    
    protected function _construct()
    {
        $this->_init(
            'Hims\Testimonial\Model\Grid',
            'Hims\Testimonial\Model\ResourceModel\Grid'
        );
    }
}
