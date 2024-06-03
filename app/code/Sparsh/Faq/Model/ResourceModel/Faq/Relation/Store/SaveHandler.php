<?php
/**
 * Class SaveHandler
 *
 * PHP version 8.2
 *
 * @category Sparsh
 * @package  Sparsh_Faq
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
namespace Sparsh\Faq\Model\ResourceModel\Faq\Relation\Store;

use Magento\Framework\EntityManager\Operation\ExtensionInterface;
use Sparsh\Faq\Api\Data\FaqInterface;
use Sparsh\Faq\Model\ResourceModel\Faq;
use Magento\Framework\EntityManager\MetadataPool;

/**
 * Class SaveHandler
 *
 * @category Sparsh
 * @package  Sparsh_Faq
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
class SaveHandler implements ExtensionInterface
{
    /**
     * MetadataPool
     *
     * @var MetadataPool
     */
    protected $metadataPool;

    /**
     * ResourcePage
     *
     * @var Page
     */
    protected $resourcePage;

    /**
     * ReadHandler Class constructor
     *
     * @param MetadataPool $metadataPool metadataPool
     * @param Faq          $resourcePage resourcePage
     **/
    public function __construct(
        MetadataPool $metadataPool,
        Faq $resourcePage
    ) {
        $this->metadataPool = $metadataPool;
        $this->resourcePage = $resourcePage;
    }

    /**
     * Save Storeids into database
     *
     * @param object $entity    entity
     * @param array  $arguments arguments
     *
     * @return object
     * @throws \Exception
     */
    public function execute($entity, $arguments = [])
    {
        $entityMetadata = $this->metadataPool->getMetadata(FaqInterface::class);
        $linkField = $entityMetadata->getLinkField();
        $connection = $entityMetadata->getEntityConnection();
        $oldStores = $this->resourcePage->lookupStoreIds((int)$entity->getId());
        $newStores = $entity->getStores();

        if (!empty($newStores)) {
            $newStores = json_decode($newStores[0]);
        }
        if (is_array($newStores) && is_array($oldStores)) {
            $table = $this->resourcePage->getTable('sparsh_faq_store');
            $delete = array_diff($oldStores, $newStores);
            if ($delete) {
                $where = [
                $linkField . ' = ?' => (int)$entity->getData($linkField),
                'store_id IN (?)' => $delete,
                ];
                $connection->delete($table, $where);
            }
            $insert = array_diff($newStores, $oldStores);
            if ($insert) {
                $data = [];
                foreach ($insert as $storeId) {
                    $data[] = [
                    $linkField => (int)$entity->getData($linkField),
                    'store_id' => (int)$storeId
                    ];
                }
                $connection->insertMultiple($table, $data);
            }
        }
        return $entity;
    }
}
