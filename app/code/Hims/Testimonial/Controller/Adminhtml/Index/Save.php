<?php

namespace Hims\Testimonial\Controller\Adminhtml\Index;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Framework\Image\AdapterFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\Stdlib\DateTime\DateTime;

class Save extends \Magento\Backend\App\Action
{
    protected $gridFactory;
    protected $uploaderFactory;
    protected $dateTime;

    protected $adapterFactory;
    protected $filesystem;
 
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Hims\Testimonial\Model\GridFactory $gridFactory,
        UploaderFactory $uploaderFactory,
        AdapterFactory $adapterFactory,
        DateTime $dateTime,
        Filesystem $filesystem
    ) {
       
        $this->gridFactory = $gridFactory;
        $this->filesystem = $filesystem;
        $this->dateTime = $dateTime;
        $this->adapterFactory = $adapterFactory;
        $this->uploaderFactory = $uploaderFactory;
        parent::__construct($context);
    }
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
      
        
        if($data['testimonial_id']==""){
            unset($data['testimonial_id']);
            $data['date'] = $this->dateTime->gmtDate();
        }
        if (!$data) {
            $this->_redirect('testimonial/index/addrow');
            return;
        }
            try {
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
        
                if (isset($data['testimonial_id'])) {  
                    $rowData['testimonial_id'] = $data['testimonial_id'];
                }
                // echo "<pre>";
                // print_r($rowData['status']);
                // die;
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
            $this->_redirect('testimonial/index/index');
        }
        protected function _isAllowed()
        {
            return $this->_authorization->isAllowed('Hims_Testimonial::manager');
        }
}
