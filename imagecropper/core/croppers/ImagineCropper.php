<?php
namespace Core\Croppers;

use Core\Factories\NameGeneratorFactory;
use Craft\AssetFileModel;
use Craft\ImageCropper_ContextModel;
use Craft\ImageCropper_ResponseModel;
use Craft\AssetConflictResolution;
use Craft\AssetFileRecord;
use Craft\IOHelper;

abstract class ImagineCropper extends Cropper
{
    protected $path;
    protected $asset;
    protected $image;

    abstract public function performCrop();

    /**
     * create a crop
     * @return mixed
     */
    public function crop()
    {
        $assetSourceId = $this->asset->getSource()->id;
        $assetSourceType = $this->craft()->assetSources->getSourceTypeById($assetSourceId);

        //get a local copy
        $this->path = $assetSourceType->getLocalCopy($this->asset);

        if ($this->craft()->images->checkMemoryForImage($this->path)) {

            // load the image
            $this->image = $this->craft()->images->loadImage($this->path);

            // Set image quality - but normalise (for PNG)!
            $quality = $this->getImageQuality($this->asset);
            $this->image->setQuality($quality);

            // do the actual crop!
            $this->performCrop();

            // save the image!
            $this->image->saveAs($this->path);

        }

        return $this->image;
    }

    /**
     * save the crop
     * @param ImageCropper_ContextModel $contextModel
     * @return ImageCropper_ResponseModel
     */
    public function save(ImageCropper_ContextModel $contextModel)
    {
        $response = new ImageCropper_ResponseModel();

        $conflictResolution = AssetConflictResolution::Replace;

        // now that we have our crop, lets upload it
        $type = $contextModel->cropSource->type;
        $nameFactory = new NameGeneratorFactory($type);
        $nameGenerator = $nameFactory->create();
        $filename = $nameGenerator->createFileNameByContext($contextModel);


        // This is the folder that our assets are stored in
        $folderId = $contextModel->folder->id;

        $image = $contextModel->image;

        // Now lets insert the file.
        $assetOperationResponseModel = $this->craft()->assets->insertFileByLocalPath($contextModel->path, $filename, $folderId, $conflictResolution);

        $assetOperationResponseData = $assetOperationResponseModel->getResponseData();

        if($assetOperationResponseData['success'] != true){
            $response->success = false;
            $response->message = 'Could not generate crops. Failed to insert file by local path.';
            return $response;
        }

        $record = null;
        if(isset($assetOperationResponseData['fileId']) && !empty($assetOperationResponseData['fileId'])){
            $fileId = $assetOperationResponseData['fileId'];
            $record = AssetFileRecord::model()->findById($fileId);
        }

        $cropFileModel = new AssetFileModel($record);

        // Update the asset record to reflect changes
        $this->updateAsset($cropFileModel, $image, $contextModel->path);

        $contextModel->crop = $cropFileModel;

        $response->context = $contextModel;
        $response->success = true;

        return $response;
    }

    public function cropAndSave(ImageCropper_ContextModel $contextModel)
    {
        $response = new ImageCropper_ResponseModel();
        $image = $this->crop();
        if($image) {
            $contextModel->path = $this->path;
            $contextModel->image = $image;
            $response = $this->save($contextModel);
        }
        return $response;
    }

    public function getAsset()
    {
        return $this->asset;
    }

    public function getImage()
    {
        return $this->image;
    }

    public function getPath()
    {
        return $this->path;
    }

    protected function updateAsset($asset, $image, $path)
    {
        // Update our model
        $asset->size = IOHelper::getFileSize($path);
        $asset->width = $image->getWidth();
        $asset->height = $image->getHeight();

        // Then, make sure we update the asset info as stored in the database
        $fileRecord = AssetFileRecord::model()->findById($asset->id);
        $fileRecord->size = $asset->size;
        $fileRecord->width = $asset->width;
        $fileRecord->height = $asset->height;
        $fileRecord->dateModified = IOHelper::getLastTimeModified($path);

        $fileRecord->save(false);
    }

    protected function getImageQuality(AssetFileModel $asset, $quality = null)
    {
        $desiredQuality = (!$quality) ? 100 : $quality;

        if ($asset->getExtension() == 'png') {
            // Valid PNG quality settings are 0-9, so normalize and flip, because we're talking about compression
            // levels, not quality, like jpg and gif.
            $quality = round(($desiredQuality * 9) / 100);
            $quality = 9 - $quality;

            if ($quality < 0) {
                $quality = 0;
            }

            if ($quality > 9) {
                $quality = 9;
            }
        } else {
            $quality = $desiredQuality;
        }

        return $quality;
    }

}