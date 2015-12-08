<?php
namespace Core\Generators\Sources;

use Core\Components\Base;

abstract class NameGenerator extends Base
{
    protected $source;
    protected $folder;
    protected $filename;

    public function __construct($args)
    {
        parent::__construct();
        $this->setAttributes($args);
    }

    abstract public function getNameReplacement();

    /**
     * Get a unique file name by ImageCropper_ContextModel
     * @param $context
     * @return string
     */
    public function createFileNameByContext($context)
    {
        $this->setAttributesByContext($context);
        $this->source = $context->cropSource;

        $sectionHandle = $context->entry->section->handle;
        $entryType = $context->entry->type->handle;
        $asset = $context->asset;
        $imageWidth = $context->width;
        $imageHeight = $context->height;
        
        $this->filename = $asset->title . '_crop_' . $sectionHandle . '_' . $entryType . '_' . $imageWidth .'x' . $imageHeight . '_' . time() . '.'. $asset->getExtension();

        // check to see if the file exists, if it does, lets rename our file.
        if($this->fileExists()){
            $this->getNameReplacement();
        }

        return $this->filename;
    }

    /**
     * Check if a file exists
     * @return mixed
     */
    public function fileExists()
    {
        $sourceType = $this->craft()->assetSources->getSourceTypeById($this->source->id);

        // If the folder has a parent, lets get that path.
        $parentPath = '';
        if(!empty($this->folder->parent)){
            $parentPath = $this->folder->parent->path;
        }

        return $sourceType->fileExists($parentPath, $this->filename);
    }


    /**
     * Get $filename
     * @return mixed
     */
    public function getFileName()
    {
        return $this->filename;
    }
}