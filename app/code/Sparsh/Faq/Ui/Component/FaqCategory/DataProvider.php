<?php
/**
 * Class DataProvider
 *
 * PHP version 8.2
 *
 * @category Sparsh
 * @package  Sparsh_Faq
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
namespace Sparsh\Faq\Ui\Component\FaqCategory;

use Sparsh\Faq\Model\ResourceModel\FaqCategory\CollectionFactory;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class DataProvider
 *
 * @category Sparsh
 * @package  Sparsh_Faq
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * Collection
     *
     * @var \Magento\Cms\Model\ResourceModel\Block\Collection
     */
    protected $collection;

    /**
     * Data Persistor
     *
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * Loaddata
     *
     * @var array
     */
    protected $loadedData;

    /**
     * Storemanager
     *
     * @var StoreManagerInterface
     */
    public $storeManager;

    /**
     * Constructor DataProvider
     *
     * @param string                 $name                         name
     * @param string                 $primaryFieldName             Primaryfieldname
     * @param string                 $requestFieldName             Requestfieldname
     * @param CollectionFactory      $faqCategoryCollectionFactory faqCategoryCollectionFactory
     * @param StoreManagerInterface  $storeManager                 StoreManager
     * @param DataPersistorInterface $dataPersistor                DataPersistor
     * @param array                  $meta                         Meta
     * @param array                  $data                         Data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $faqCategoryCollectionFactory,
        StoreManagerInterface $storeManager,
        DataPersistorInterface $dataPersistor,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $faqCategoryCollectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        $this->storeManager=$storeManager;
        
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $meta,
            $data
        );
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $items = $this->collection->getItems();
        $this->loadedData = [];
        foreach ($items as $model) {
            $this->loadedData[$model->getId()] = $model->getData();
        }

        $data = $this->dataPersistor->get('faqcategorydata');
        if (!empty($data)) {
            $model = $this->collection->getNewEmptyItem();
            $model->setData($data);
            $this->loadedData[$model->getId()] = $model->getData();
            $this->dataPersistor->clear('faqcategorydata');
        }
        return $this->loadedData;
    }
}
