<?php
/**
 * Class ReadHandler
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

use Sparsh\Faq\Model\ResourceModel\Faq;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;

/**
 * Class ReadHandler
 *
 * @category Sparsh
 * @package  Sparsh_Faq
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
class ReadHandler implements ExtensionInterface
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
     * Set Storeids
     *
     * @param object $entity    entity
     * @param array  $arguments arguments
     *
     * @return object
     */
    public function execute($entity, $arguments = [])
    {
        if ($entity->getId()) {
            $stores = $this->resourcePage->lookupStoreIds((int)$entity->getId());
            $entity->setData('store_id', $stores);
        }
        return $entity;
    }
}
