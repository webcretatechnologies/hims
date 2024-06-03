<?php
/**
 * Interface FaqRepositoryInterface
 *
 * PHP version 8.2
 *
 * @category Sparsh
 * @package  Sparsh_Faq
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
namespace Sparsh\Faq\Api;

use Sparsh\Faq\Api\Data\FaqInterface;

/**
 * Interface FaqRepositoryInterface
 *
 * @category Sparsh
 * @package  Sparsh_Faq
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
interface FaqRepositoryInterface
{
    /**
     * Save Data
     *
     * @param object $faq object
     *
     * @return \Sparsh\Faq\Api\Data\FaqInterface
     **/
    public function save(FaqInterface $faq);

    /**
     * Get Data By Id
     *
     * @param int $id Load Data by Id
     *
     * @return \Sparsh\Faq\Api\Data\FaqInterface
     **/
    public function getById($id);

    /**
     * Delete Object Data
     *
     * @param object $faq Object
     *
     * @return \Sparsh\Faq\Api\Data\FaqInterface
     **/
    public function delete(FaqInterface $faq);

    /**
     * Delete Data By ID
     *
     * @param int $id Delete Object By Id
     *
     * @return \Sparsh\Faq\Api\Data\FaqInterface
     **/
    public function deleteById($id);
}
