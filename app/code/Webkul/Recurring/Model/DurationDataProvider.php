<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Recurring
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Recurring\Model;
 
use Webkul\Recurring\Model\ResourceModel\RecurringTerms\CollectionFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\DataPersistorInterface;
 
class DurationDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var PopupCollectionFactory
     */
    protected $collection;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var String
     */
    protected $_loadedData;

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param RequestInterface $requestInterface
     * @param CollectionFactory $termCollectionFactory
     * @param DataPersistorInterface $dataPersistor
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        RequestInterface $requestInterface,
        CollectionFactory $termCollectionFactory,
        DataPersistorInterface $dataPersistor,
        array $meta = [],
        array $data = []
    ) {
        $this->request = $requestInterface;
        $this->collection = $termCollectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }
 
    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        if (null !== $this->_loadedData) {
            return $this->_loadedData;
        }
        $items = $this->collection->getItems();
        foreach ($items as $item) {
            $durationId = $item->getEntityId();
            $item->load($durationId);
            $this->_loadedData[$durationId]['information'] = $item->getData();
        }
        $data = $this->dataPersistor->get('recurring_duration');
        if (!empty($data)) {
            $duration = $this->collection->getNewEmptyItem();
            $duration->setData($data);
            $this->_loadedData[$duration->getId()]['information'] = $duration->getData();
            $this->dataPersistor->clear('recurring_duration');
        }
        return $this->_loadedData;
    }
}
