<?php
namespace Craft;

class ImageCropper_CropperImageModel extends BaseModel
{
    public function defineAttributes()
    {
        return array(
            'image'         => AttributeType::Mixed,
            'crops'         => AttributeType::Mixed,
        );
    }

    public function getImage()
    {
        return $this->images;
    }

    public function getCrops()
    {
        return $this->crops;
    }
}