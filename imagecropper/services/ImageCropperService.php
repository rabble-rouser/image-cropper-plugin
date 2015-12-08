<?php
namespace Craft;

use Core\Factories\CropperFactory;
use Core\Generators\Html\CropHtmlGenerator;

class ImageCropperService extends BaseApplicationComponent 
{

    /**
     * Get a crop
     * @param ImageCropper_ContextModel $context
     * @return bool|ImageCropper_ResponseModel
     */
    public function getCrop(ImageCropper_ContextModel $context)
    {
        $response = new ImageCropper_ResponseModel();

        try{
            $cropperFactory = new CropperFactory($context->cropType);
            $cropper = $cropperFactory->create();
            $cropper->setAttributesByContext($context);
            $response = $cropper->cropAndSave($context);
            if($response->isSuccess()){
                $htmlGenerator = new CropHtmlGenerator($context);
                $response->data = $htmlGenerator->generate();
            }
            else{
                $response->setErrorMessage('Unable to create crops.');
            }
        }
        catch(Exception $e){
            $response->setErrorMessage($e->getMessage());
        }

        return $response;

    }

    /**
     * Perform our clean up task. Remove any orphan assets from our crop source.
     */
    public function cleanUp()
    {
        $plugin = craft()->plugins->getPlugin('ImageCropper');
        $settings = $plugin->getSettings();
        $sourceId = $settings->source;
        $assets = craft()->assets->getFilesBySourceId($sourceId);

        $validAssetIds = craft()->db->createCommand()
            ->selectDistinct('targetId')
            ->from('relations')
            ->query();

        $targetIds = array();
        foreach($validAssetIds as $idArr){
            $targetIds[] = $idArr['targetId'];
        }

        $task = craft()->tasks->createTask('ImageCropper', 'Cleaning up images', array(
            'assets'          => $assets,
            'validAssetIds'   =>  $targetIds,
        ));

        $taskSuccessful = craft()->tasks->runTask($task);
        if(!$taskSuccessful){
            Craft::log('Unable to complete ImageCropperTask. Some failure occurred.');
        }

    }

    /**
     * Save the relation between the field, entry, and images
     * Note: Craft's saveRelations method will clear existing relations
     * TODO we'll need to restructure our input names so that this is more organized
     * @param BaseFieldType $fieldType
     */
    public function saveRelationship(BaseFieldType $fieldType)
    {
        $source = $fieldType->element;
        $field = $fieldType->model;

        // Get the post values for this field
        $handle      = $fieldType->model->handle;
        $content     = $fieldType->element->getContent();

        $ids = $content->getAttribute($handle);

        if(isset($ids['originalImage']) && isset($ids['crops'])){
            $originalImages = $ids['originalImage'];
            craft()->relations->saveRelations($field, $source, $originalImages);
            $crops = $ids['crops'];
            $numCrops = sizeof($crops) / sizeof($originalImages);
            foreach($originalImages as $key => $assetId){
                $asset = craft()->assets->getFileById($assetId);
                $cropTargetIds = array_slice($crops, $key * $numCrops, $numCrops);
                craft()->relations->saveRelations($field, $asset, $cropTargetIds);
            }
        }
        else{
            craft()->relations->saveRelations($field, $source, []);
        }
    }

    /**
     * @param ImageCropper_CriteriaModel $criteria
     * @param $justIds
     * @return array
     * @internal param $element
     * @internal param $sectionHandle
     * @internal param $fieldHandle
     */
    public function getPreppedAssets(ImageCropper_CriteriaModel $criteria, $justIds = false)
    {
        $data = array();

        // get the images
        $assets = $this->getRelatedAssets($criteria);

        // get the crops
        foreach($assets as $asset){
            $cropCriteria = new ImageCropper_CriteriaModel($criteria);
            $cropCriteria->element = $asset;
            $cropCriteria->limit = null;
            $cropCriteria->offset = null;

            $crops = $this->getRelatedAssets($cropCriteria);


            if(!$justIds){
                $model = new ImageCropper_CropperImageModel();
                $model->setAttribute('image', $asset);
                $model->setAttribute('crops', $crops);
            }
            else{
                $model = array(
                    'image' => $asset->id,
                );
                foreach($crops as $crop){
                    $model['crops'][] = $crop->id;
                }
            }

            $data[] = $model;
        }
        return $data;
    }

    /**
     * @param $myCriteria
     * @return mixed
     * @internal param $element
     * @internal param $sectionHandle
     * @internal param $fieldHandle
     */
    public function getRelatedAssets($myCriteria)
    {
        $criteria = craft()->elements->getCriteria(ElementType::Asset);
        $criteria->setAttributes($myCriteria->getAttributes());

        $criteria->relatedTo = array(
            'sourceElement' => $myCriteria->element,
            'field'         => $myCriteria->field,
        );
        $assets = $criteria->find();
        return $assets;
    }

    /**
     * get the defined crop source
     * @return mixed
     */
    public function getCropSource()
    {
        $plugin = craft()->plugins->getPlugin('ImageCropper');
        $settings = $plugin->getSettings();
        $sourceId = $settings->source;
        $source = craft()->assetSources->getSourceById($sourceId);
        return $source;
    }

    /**
     * Get folder
     * @param array $data
     * @return mixed
     */
    public function getFolder($data = array())
    {
        $criteria = new FolderCriteriaModel();
        foreach($data as $key => $value){
            $criteria->$key = $value;
        }
        $folders = craft()->assets->findFolders($criteria);
        $folder = $folders[0];
        return $folder;
    }

    /**
     * get the folder for the crops
     * @return mixed
     */
    public function getCropFolder()
    {
        $source = craft()->imageCropper->getCropSource();

        // TODO this should probably be a plugin setting and mirror assets logic
        $folderData = array(
            'sourceId'  =>  $source->id,
            'name'      =>  $source->name
        );
        $folder = craft()->imageCropper->getFolder($folderData);
        return $folder;
    }

    /**
     * Get the available asset sources
     */
    public function getAvailableSources()
    {
        $plugin = craft()->plugins->getPlugin('ImageCropper');
        $settings = $plugin->getSettings();

        $source = $settings->source;
        $assetSources = craft()->assetSources->getAllSources();
        $availableSources = array();

        foreach($assetSources as $assetSource){
            if($assetSource->id != $source){
                $availableSources[] = 'folder:' . $assetSource->id;
            }
        }
        return $availableSources;
    }

    /**
     * Get crop html by criteria
     * @param ImageCropper_CriteriaModel $criteriaModel
     * @param string $name
     * @return array
     */
    public function getCropHtmlByCriteria(ImageCropper_CriteriaModel $criteriaModel, $name = '')
    {
        $plugin = craft()->plugins->getPlugin('ImageCropper');
        $settings = $plugin->getSettings();

        $entryType = $criteriaModel->element->getType()->handle;
        $section = $criteriaModel->element->section->handle;

        $croppedImagesHTML = array();
        $htmlGenerator = new CropHtmlGenerator();

        $context = new ImageCropper_ContextModel();
        $context->name = $name;
        $context->namespace = '';

        foreach($criteriaModel as $fieldModel){
            $context->asset = $fieldModel['image'];
            foreach($fieldModel['crops'] as $key=>$cropAsset){
                $context->crop = $cropAsset;
                $context->width = $settings->width[$section][$entryType][$key];
                $context->height = $settings->height[$section][$entryType][$key];

                $htmlGenerator->setContext($context);
                $croppedImagesHTML[] = $htmlGenerator->generate();
            }
        }
        return $croppedImagesHTML;
    }

    /**
     * Convert our dimensions settings into an array that is easier to use in our service
     * @param $sectionHandle
     * @param $entryTypeHandle
     * @return array
     */
    public function getDimensionsArray($sectionHandle, $entryTypeHandle)
    {
        $plugin = craft()->plugins->getPlugin('ImageCropper');
        $settings = $plugin->getSettings();
        $width = $settings->width[$sectionHandle][$entryTypeHandle];
        $height = $settings->height[$sectionHandle][$entryTypeHandle];

        $numCrops = $settings->numberOfCrops[$sectionHandle][$entryTypeHandle];
        $dimensions = array();
        for($i = 0; $i < $numCrops; $i++){
            $dimensions[] = array(
                'width' => $width[$i],
                'height'    => $height[$i]
            );
        }
        return $dimensions;
    }

}