<?php
namespace Craft;

use Core\Generators\Html\ModalImageHtmlGenerator;
use Core\Support\CropType;

class ImageCropperController extends BaseController {
    protected $allowAnonymous = true;

    public function actionGetCroppedImages()
    {

        // assign vars
        $this->requireAjaxRequest();
        $this->requireAdmin();
        $post = craft()->request->getPost();
        $assetId = $post['assetId'];
        $entryId = $post['entryId'];
        $name = $post['name'];

        $entry = craft()->elements->getElementById($entryId);
        $sectionHandle = $entry->section->handle;
        $entryType = $entry->type->handle;

        $folder = craft()->imageCropper->getCropFolder();
        $source = craft()->imageCropper->getCropSource();

        // get dimensions from plugin settings - based on sectionHandle and entryType
        $dimensions = craft()->imageCropper->getDimensionsArray($sectionHandle, $entryType);

        // fetch the asset
        $asset = craft()->assets->getFileById($assetId);

        $context = new ImageCropper_ContextModel();
        $context->setAttribute('asset', $asset);
        $context->setAttribute('entry', $entry);
        $context->setAttribute('cropSource', $source);
        $context->setAttribute('folder', $folder);
        $context->setAttribute('name', $name);
        $context->namespace = 'fields';
        $context->cropType = CropType::Dimensions;

        $response = new ImageCropper_ResponseModel();
        $htmlData = array();
        foreach($dimensions as $dimension)
        {
            $context->width = $dimension['width'];
            $context->height = $dimension['height'];
            // create image crops
            $cropResponse = craft()->imageCropper->getCrop($context);
            if($cropResponse->isSuccess()){
                $htmlData[] = $cropResponse->data;
                $response->setSuccessMessage($cropResponse->getMessage());
            }
            else{
                $response->setErrorMessage($cropResponse->getMessage());
                break;
            }
        }
        if($response->isSuccess()){
            $response->setSuccessMessage('Crops successfully generated.');
        }
        $response->data = $htmlData;
        $this->returnJson($response);
    }

    public function actionGetAssetInput()
    {
        $this->requireAjaxRequest();
        $this->requireAdmin();

        $assetId = craft()->request->getRequiredPost('assetId');

        $asset = craft()->assets->getFileById($assetId);

        $htmlGenerator = new ModalImageHtmlGenerator($asset);
        $html = $htmlGenerator->generate();

        $this->returnJson(array('html' => $html));
    }

    public function actionSaveManualCrop()
    {
        $this->requireAjaxRequest();
        $this->requireAdmin();

        $context = new ImageCropper_ContextModel();

        $post = craft()->request->getPost();
        $context->coordinates = array(
            'x1' => $post['x1'],
            'x2' => $post['x2'],
            'y1' => $post['y1'],
            'y2' => $post['y2'],
        );

        $context->width = $post['width'];
        $context->height = $post['height'];

        $originalAssetId = $post['origId'];
        $cropId = $post['id'];
        $entryId = $post['entryId'];

        $originalAsset = craft()->assets->getFileById($originalAssetId);
        $crop = craft()->assets->getFileById($cropId);

        $context->asset = $originalAsset;
        $context->crop = $crop;
        $context->cropSource = $crop->source;
        $context->entry = craft()->elements->getElementById($entryId);
        $context->name = $post['name'];
        $context->namespace = 'fields';

        $context->cropType = CropType::Coordinates;

        $folder = craft()->imageCropper->getCropFolder();
        $context->folder = $folder;

        try {
            $response = craft()->imageCropper->getCrop($context);
            if($response->isSuccess()){
                $response->setSuccessMessage('Crop successfully generated.');
            }
            $this->returnJson($response);

        }
        catch (Exception $e) {
            $this->returnErrorJson($e->getMessage());
        }

        $this->returnErrorJson(Craft::t('Something went wrong when processing the image.'));
    }
}