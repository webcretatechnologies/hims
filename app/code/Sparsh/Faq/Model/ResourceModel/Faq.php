<?php
/**
 * Class Faq
 *
 * PHP version 8.2
 *
 * @category Sparsh
 * @package  Sparsh_Faq
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
namespace Sparsh\Faq\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\EntityManager\MetadataPool;
use Sparsh\Faq\Api\Data\FaqInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\DB\Select;

/**
 * Class Faq
 *
 * @category Sparsh
 * @package  Sparsh_Faq
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
class Faq extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Store model
     *
     * @var null|Store
     */
    protected $store = null;

    /**
     * Store manager
     *
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Datetime
     *
     * @var DateTime
     */
    protected $dateTime;

    /**
     * EntityManager
     *
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * MetadataPool
     *
     * @var MetadataPool
     */
    protected $metadataPool;

    /**
     * Faq Class constructor
     *
     * @param Context               $context        Context
     * @param StoreManagerInterface $storeManager   Storemanager
     * @param DateTime              $dateTime       Datetime
     * @param EntityManager         $entityManager  Entitymanager
     * @param MetadataPool          $metadataPool   MetadataPool
     * @param string                $connectionName Connectionname
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        DateTime $dateTime,
        EntityManager $entityManager,
        MetadataPool $metadataPool,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->storeManager = $storeManager;
        $this->dateTime = $dateTime;
        $this->entityManager = $entityManager;
        $this->metadataPool = $metadataPool;
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('sparsh_faq', 'faq_id');
    }

    /**
     * Connection
     *
     * @return $this
     */
    public function getConnection()
    {
        return $this->metadataPool->getMetadata(FaqInterface::class)->getEntityConnection();
    }

    /**
     * Get Faqid
     *
     * @param AbstractModel $object Object
     * @param string        $value  Value
     * @param string|null   $field  Field
     *
     * @return bool|int|string
     * @throws LocalizedException
     * @throws \Exception
     */
    private function _getFaqId(AbstractModel $object, $value, $field = null)
    {
        $entityMetadata = $this->metadataPool->getMetadata(FaqInterface::class);

        if (!is_numeric($value) && $field === null) {
            $field = 'faq_id';
        } elseif (!$field) {
            $field = $entityMetadata->getIdentifierField();
        }

        $faqId = $value;
        if ($field != $entityMetadata->getIdentifierField() || $object->getStoreId()) {
            $select = $this->_getLoadSelect($field, $value, $object);
            $select->reset(Select::COLUMNS)
                ->columns($this->getMainTable() . '.' . $entityMetadata->getIdentifierField())
                ->limit(1);
            $result = $this->getConnection()->fetchCol($select);
            $faqId = count($result) ? $result[0] : false;
        }
        return $faqId;
    }

    /**
     * Load an object
     *
     * @param CmsPage|AbstractModel $object object
     * @param mixed                 $value  value
     * @param string                $field  field to load by (defaults to model id)
     *
     * @return $this
     */
    public function load(AbstractModel $object, $value, $field = null)
    {
        $faqId = $this->_getFaqId($object, $value, $field);
        if ($faqId) {
            $this->entityManager->load($object, $faqId);
        }
        return $this;
    }

    /**
     * Retrieve select object for load object data
     *
     * @param string                $field  field
     * @param mixed                 $value  value
     * @param CmsPage|AbstractModel $object object
     *
     * @return Select
     */
    protected function _getLoadSelect($field, $value, $object)
    {
        $entityMetadata = $this->metadataPool->getMetadata(FaqInterface::class);
        $linkField = $entityMetadata->getLinkField();

        $select = parent::_getLoadSelect($field, $value, $object);

        if ($object->getStoreId()) {
            $storeIds = [
                Store::DEFAULT_STORE_ID,
                (int)$object->getStoreId(),
            ];
            $select->join(
                ['faq_store' => $this->getTable('sparsh_faq_store')],
                $this->getMainTable() . '.' . $linkField . ' = faq_store.' . $linkField,
                []
            )
                ->where('is_active = ?', 1)
                ->where('faq_store.store_id IN (?)', $storeIds)
                ->order('faq_store.store_id DESC')
                ->limit(1);
        }

        return $select;
    }

    /**
     * Get store ids to which specified item is assigned
     *
     * @param int $faqId faqid
     *
     * @return array
     */
    public function lookupStoreIds($faqId)
    {
        $connection = $this->getConnection();

        $entityMetadata = $this->metadataPool->getMetadata(FaqInterface::class);
        $linkField = $entityMetadata->getLinkField();

        $select = $connection->select()
            ->from(['cps' => $this->getTable('sparsh_faq_store')], 'store_id')
            ->join(
                ['cp' => $this->getMainTable()],
                'cps.' . $linkField . ' = cp.' . $linkField,
                []
            )
            ->where('cp.' . $entityMetadata->getIdentifierField() . ' = :faq_id');

        return $connection->fetchCol($select, ['faq_id' => (int)$faqId]);
    }

    /**
     * Set store model
     *
     * @param Store $store store
     *
     * @return $this
     */
    public function setStore($store)
    {
        $this->store = $store;
        return $this;
    }

    /**
     * Retrieve store model
     *
     * @return Store
     */
    public function getStore()
    {
        return $this->storeManager->getStore($this->store);
    }

    /**
     * Save Data
     *
     * @param object $object AbstractModel
     *
     * @return $this
     */
    public function save(AbstractModel $object)
    {
        $this->entityManager->save($object);
        return $this;
    }

    /**
     * Delete Data
     *
     * @param object $object AbstractModel
     *
     * @return $this
     */
    public function delete(AbstractModel $object)
    {
        $this->entityManager->delete($object);
        return $this;
    }
}
