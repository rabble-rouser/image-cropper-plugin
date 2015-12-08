<?php
namespace Craft;

use Core\Generators\Html\CropHtmlGenerator;
use Core\Support\Helper;

class ImageCropper_CropperImageFieldType extends BaseFieldType
{

    /**
     * @return null|string
     */
    public function getName()
    {
        return Craft::t('Cropper Image');
    }

    public function defineContentAttribute()
    {
        return false;
    }

    protected function defineSettings()
    {
        return array(
            'limit' => array(
                AttributeType::Number,
                'min' => 1,
                'max' => 1, //TODO: RM when implementation updated
                'default' => 1
            ),
            'addButtonLabel'    => array(
                AttributeType::String,
                'default'   => 'Select Image',
            ),
        );
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return null|string
     */
    public function getInputHtml($name, $value)
    {
        // More intiative name for our values
        $fieldCriteria = $value;

        $plugin = craft()->plugins->getPlugin('ImageCropper');
        $settings = $plugin->getSettings();

        $source = $settings->source;
        // Do some basic checking to make sure settings are correctly set.
        if (empty($source)) {
            return Craft::t('To use the ' . $this->getName() . ' plugin you need to set a source.');
        }

        if (!is_object($this->element)) {
            return Craft::t('For this version of the ' . $this->getName() . ' plugin, you can only use this field with Entries.');
        }

        $elementType = $this->element->elementType;
        if ($elementType != 'Entry') {
            return Craft::t('For this version of the ' . $this->getName() . ' plugin, you can only use this field with Entries.');
        }

        $entryType = $this->element->getType()->handle;
        $section = $this->element->section->handle;
        if(Helper::arrHasEmptyValues($settings->width[$section][$entryType]) || Helper::arrHasEmptyValues($settings->height[$section][$entryType])){
            return Craft::t('Please enter crop dimensions in the plugin settings');
        }

        $availableSources = craft()->imageCropper->getAvailableSources();

        $croppedImagesHTML = craft()->imageCropper->getCropHtmlByCriteria($fieldCriteria, $name);

        $originalImages = $fieldCriteria->getImages();

        $id           = craft()->templates->formatInputId($name);
        $namespaceId = craft()->templates->namespaceInputId($id, 'imageCropper');

        $criteria = craft()->elements->getCriteria(ElementType::Asset);
        $criteria->kind = 'image';


        $namespace = craft()->templates->getNamespace();
        $fieldElementType = craft()->elements->getElementType(ElementType::Asset); //new AssetElementType(),

        // get our asset limit
        $limit = $this->settings['limit']; // TODO update impl to allow for limit > 1


        craft()->templates->includeJs(
            'var nameSpace = "' . $namespace .'";' .
            'var name = "' . $name .'";' .
            'var id = "' . $namespaceId . '";' .
            'var entryId = "' . $this->element->id .'";'
            );
        craft()->templates->includeJsResource('imagecropper/js/input.js');
        craft()->templates->includeCssResource('imagecropper/css/styles.css');

        craft()->templates->includeJsResource('lib/imgareaselect/jquery.imgareaselect.pack.js');
        craft()->templates->includeCssResource('lib/imgareaselect/imgareaselect-animated.css');


        return craft()->templates->render('imagecropper/_fieldtype/input', array(
            'name'          => $name . '[originalImage]',
            'value'         =>  $originalImages,
            'id'            => $namespaceId,
            'nameSpace'     => $namespace,
            'elementType'   => $fieldElementType,
            'sources'   => $availableSources,
            'elements'  =>  $originalImages,
            'croppedImages'  => $croppedImagesHTML,
            'sourceElementId'   => $this->element->id, // todo what is this for?
            'criteria'  =>  $criteria,
            'limit'     => $limit,
            'addButtonLabel'     => $this->settings['addButtonLabel'] ?: 'Select Image',
            'entryId'       =>  $this->element->id,
        ));
    }

    /**
     * Get the field settings html
     * @return mixed
     */
    public function getSettingsHtml()
    {

        return craft()->templates->render(
            'imageCropper/_fieldtype/settings', array(
                'settings' => $this->getSettings()
            )
        );
    }

    /**
     * @override
     * @param mixed $value
     * @return mixed $value
     */
    public function prepValue($value)
    {
        $criteria = new ImageCropper_CriteriaModel();
        $criteria->element = $this->element;
        $criteria->section = $this->element->section->handle;
        $criteria->field = $this->model->handle;
        $criteria->limit = null;
        return $criteria;
    }

    /**
     * save the relationships
     */
    public function onAfterElementSave()
    {
        craft()->imageCropper->saveRelationship($this);
        craft()->imageCropper->cleanUp();

    }
}
