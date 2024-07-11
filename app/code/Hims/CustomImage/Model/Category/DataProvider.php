<?php
namespace Hims\CustomImage\Model\Category;
 
class DataProvider extends \Magento\Catalog\Model\Category\DataProvider
{
 
    protected function getFieldsMap()
    {
        $fields = parent::getFieldsMap();
        $fields['content'][] = 'custom_image'; // custom image field
        
        return $fields;
    }
}