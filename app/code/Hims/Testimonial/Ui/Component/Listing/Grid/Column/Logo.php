<?php
namespace Hims\Testimonial\Ui\Component\Listing\Grid\Column;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\Component\Listing\Columns\Column;
class Logo extends Column
{
    const ALT_FIELD = 'title';

    protected $storeManager;

    protected $imageHelper;
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        StoreManagerInterface $storeManager,
        \Magento\Catalog\Helper\Image $imageHelper,
        array $components = [],
        array $data = []
    ) {
        $this->storeManager = $storeManager;
        $this->imageHelper = $imageHelper;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as &$item) {
                $url = '';
                if (isset($item[$fieldName])) {
                    
                    if ($item[$fieldName] != '') {
                        
                        
                        $url = $this->storeManager->getStore()->getBaseUrl(
                            \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
                        ) . '' . $item[$fieldName];
                        
                    } else {
                        $url = $this->imageHelper->getDefaultPlaceholderUrl('small_image');
                    }
                } else {
                    $url = $this->imageHelper->getDefaultPlaceholderUrl('small_image');
                }
                
                $item[$fieldName . '_src'] = $url;
                $item[$fieldName . '_alt'] = $this->getAlt($item) ?: '';
                $item[$fieldName . '_orig_src'] = $url;
            }
        }
    
        return $dataSource;
    }
    protected function getAlt($row)
    {
        $altField = $this->getData('config/altField') ?: self::ALT_FIELD;
        return isset($row[$altField]) ? $row[$altField] : null;
    }
}