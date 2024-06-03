<?php
/**
 * Interface FaqInterface
 *
 * PHP version 8.2
 *
 * @category Sparsh
 * @package  Sparsh_Faq
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
namespace Sparsh\Faq\Api\Data;

/**
 * Interface FaqInterface
 *
 * @category Sparsh
 * @package  Sparsh_Faq
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
interface FaqInterface
{
    /**
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const FAQ_ID          = 'faq_id';
    const FAQ_ANSWER          = 'faq_answer';

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId();

    /**
     * Set ID
     *
     * @param int $id set faq id
     *
     * @return \Sparsh\Faq\Api\Data\FaqInterface
     */
    public function setId($id);

    /**
     * Get Stores
     *
     * @return array
     */
    public function getStores();

    /**
     * @return mixed
     */
    public function getFaqAnswer();
}
