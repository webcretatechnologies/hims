<?php

namespace Hims\Testimonial\Controller\Index;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Framework\Image\AdapterFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Controller\ResultFactory;
use Magento\Store\Model\StoreManagerInterface;

class Save extends \Magento\Framework\App\Action\Action
{
    protected $gridFactory;
    protected $storeManager;
    protected $uploaderFactory;
    protected $resultFactory;
    protected $dateTime;
    protected $adapterFactory;
    protected $filesystem;
 
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Hims\Testimonial\Model\GridFactory $gridFactory,
        UploaderFactory $uploaderFactory,
        AdapterFactory $adapterFactory,
        DateTime $dateTime,
        ResultFactory $resultFactory,
        StoreManagerInterface $storeManager,
        Filesystem $filesystem
    ) {
       
        $this->gridFactory = $gridFactory;
        $this->filesystem = $filesystem;
        $this->storeManager = $storeManager;
        $this->dateTime = $dateTime;
        $this->resultFactory = $resultFactory;
        $this->adapterFactory = $adapterFactory;
        $this->uploaderFactory = $uploaderFactory;
        parent::__construct($context);
    }
    public function execute()
    {
        $storeId = $this->storeManager->getStore()->getId();
        $status = "Rejected";
        $data = $this->getRequest()->getPostValue();
        if (!$data) {
            $this->_redirect('testimonial/');
            return;
        }
            try {
                $data['date'] = $this->dateTime->gmtDate();
                $data['store'] = $storeId;
                $data['status'] = $status;
                $rowData = $this->gridFactory->create();
                $rowData->setData($data);
            
                if ((isset($_FILES['image']['name'])) && ($_FILES['image']['name'] != '') && (!isset($data['image']['delete'])))
                {
                    try
                    {
            
                        $uploaderFactory = $this->uploaderFactory->create(['fileId' => 'image']);
                        $uploaderFactory->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
                        $imageAdapter = $this->adapterFactory->create();
                        $uploaderFactory->addValidateCallback('custom_image_upload',$imageAdapter,'validateUploadFile');
                        $uploaderFactory->setAllowRenameFiles(true);
                        //$uploaderFactory->setFilesDispersion(true);
                        $mediaDirectory = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);
                        $destinationPath = $mediaDirectory->getAbsolutePath('Hims/Testimonial');
                        $result = $uploaderFactory->save($destinationPath);
                    
                        if (!$result) {
                            throw new LocalizedException(__('File cannot be saved to path: $1', $destinationPath));
                        }
                        $imagePath = 'Hims/Testimonial/' . $result['file'];
                        $data['image'] = $imagePath;
                        $rowData['image'] = $data['image'];
                    }
                    catch (\Exception $e) {
                            $this->messageManager->addError(__("Image not Upload Pleae Try Again"));
                    }
                }         
                $rowData->setData('update_time', $this->dateTime->gmtDate());
                if($rowData->save())
                {
                    $this->messageManager->addSuccess(__('Row data has been successfully saved.'));
                }else{
                    $this->messageManager->addError(__('Row data save Failed.')); 
                } 
                    
            } catch (\Exception $e) {
                $this->messageManager->addError(__($e->getMessage()));
            }
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setPath('testimonial');
            return $resultRedirect;
            // $this->_redirect('testimonial/');
        }
}