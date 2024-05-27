<?php

namespace Hims\Testimonial\Block\Adminhtml\Index\Items\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;

class Main extends Generic implements TabInterface
{
    protected $_wysiwygConfig;
    protected $_value;
    protected $_storeManager;
 
    public function __construct(
        \Magento\Backend\Block\Template\Context $context, 
        \Magento\Framework\Registry $registry, 
        \Hims\Testimonial\Model\System\Config\Value $value,
        \Magento\Framework\Data\FormFactory $formFactory,  
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig, 
        array $data = []
    ) 
    {
        $this->_wysiwygConfig = $wysiwygConfig;
        $this->_value   = $value;
        $this->_storeManager = $storeManager;
        parent::__construct($context, $registry, $formFactory, $data);
    }
    public function getTabLabel()
    {
        return __('Testimonial Information');
    }

    public function getTabTitle()
    {
        return __('Testimonial Information');
    }

    public function canShowTab()
    {
        return true;
    }

    public function isHidden()
    {
        return false;
    }

    protected function _prepareForm()
    {
        $storeOptions = $this->_storeManager->getStores();
        $storeValues = [];
        foreach ($storeOptions as $store) {
            $storeValues[$store->getId()] = $store->getName();
        }
        $model = $this->_coreRegistry->registry('row_data');

        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('testimonial');
        if ($model->getId()) {
            $fieldset = $form->addFieldset(
                'base_fieldset',
                ['legend' => __('Edit Testimonial'), 'class' => 'fieldset-wide']
            );
            $fieldset->addField('id', 'hidden', ['name' => 'id']);
        } else {
            $fieldset = $form->addFieldset(
                'base_fieldset',
                ['legend' => __('Add New Testimonial'), 'class' => 'fieldset-wide']
            );
        }


        $wysiwygConfig = $this->_wysiwygConfig->getConfig(['tab_id' => $this->getTabId()]);
        $fieldset->addField(
            'testimonial_id',
            'hidden',
            [
                'name' => 'testimonial_id',
            ]
        );
    
        $fieldset->addField(
            'name',
            'text',
            [
                'name' => 'name',
                'label' => __('Customer Name'),
                'title' => __('Customer Name'),
                'required' => true,
            ]
        );
        $imageFieldConfig = [
            'name' => 'image',
            'label' => __('Customer Image'),
            'title' => __('Customer Image'),
            'note' => 'Allow image type: jpg, jpeg, gif, png',
        ];
        
        // Check if an existing image is available
        if ($model['image']) {
            $imageUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . $model['image'];
            $imageFieldConfig['after_element_html'] = '<br><img src="' . $imageUrl . '" height="100" />';
            $imageFieldConfig['required'] = false;
        }
        
        $fieldset->addField('image', 'file', $imageFieldConfig);
        $fieldset->addField(
            'email',
            'text',
            [
                'name' => 'email',
                'label' => __('Customer Email'),
                'title' => __('Customer Email'),
                'required' => true,
            ]
        );
        $fieldset->addField(
            'message',
            'textarea',
            [
                'name' => 'message',
                'label' => __('Message'),
                'title' => __('Message'),
                'required' => true,
            ]
        );
        $fieldset->addField(
            'location',
            'text',
            [
                'name' => 'location',
                'label' => __('Location'),
                'title' => __('Location'),
                'required' => true,
            ]
        );
        // $fieldset->addField(
        //     'youtube',
        //     'text',
        //     [
        //         'name' => 'youtube',
        //         'label' => __('Youtube Link'),
        //         'title' => __('Youtube Link'),
        //         'required' => false,
        //     ]
        // );
        // $fieldset->addField(
        //     'twitter',
        //     'text',
        //     [
        //         'name' => 'twitter',
        //         'label' => __('Twitter Link'),
        //         'title' => __('Tswitter Link'),
        //         'required' => false,
        //     ]
        // );
        // $fieldset->addField(
        //     'facebook',
        //     'text',
        //     [
        //         'name' => 'facebook',
        //         'label' => __('Facebook Link'),
        //         'title' => __('Facebook Link'),
        //         'required' => false,
        //     ]
        // );
        $statusOption = $this->_value->toOptionArray();
        
        if(array_filter($statusOption)){
            $fieldset->addField('status', 'select',
                [
                    'label' => __('Status'),
                    'title' => __('Status'),
                    'name' => 'status',
                    'options' => $this->_value->toOptionArray(),
                ]
            );
        }
        $ratinOption = $this->_value->toOptionArrayForRating();
        if(array_filter($ratinOption)){
            $fieldset->addField('rating', 'select',
                [
                    'label' => __('Rating'),
                    'title' => __('Rating'),
                    'name' => 'rating',
                    'options' => $this->_value->toOptionArrayForRating(),
                ]
            );
        }
        $fieldset->addField(
            'store',
            'select',
            [
                'label' => __('Store'),
                'title' => __('Store'),
                'name' => 'store',
                'options' => $storeValues,
            ]
        );
        $form->setValues($model->getData());
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
