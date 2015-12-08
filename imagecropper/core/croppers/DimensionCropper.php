<?php
namespace Core\Croppers;

use Craft\ImageHelper;

class DimensionCropper extends ImagineCropper
{
    protected $width;
    protected $height;

    public function __construct($width = null, $height = null)
    {
        parent::__construct();
        $this->width = $width;
        $this->height = $height;
    }

    public function performCrop()
    {
        // Calculate the missing width/height for the asset - ensure aspect ratio is maintained
        $dimensions = ImageHelper::calculateMissingDimension($this->width, $this->height, $this->image->getWidth(), $this->image->getHeight());
        $this->image = $this->image->scaleAndCrop($dimensions[0], $dimensions[1]);
    }
}