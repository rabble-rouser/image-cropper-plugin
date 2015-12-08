<?php
namespace Craft;

class ImageCropper_ContextModel extends BaseModel
{
    public function defineAttributes()
    {
        return array(
            'asset'         =>  AttributeType::Mixed,

            'entry'         =>  AttributeType::Mixed,
            'cropSource'    =>  AttributeType::Mixed,
            'folder'        =>  AttributeType::Mixed,

            'dimensions'    =>  AttributeType::Mixed,
            'coordinates'   =>  AttributeType::Mixed,

            'name'          =>  AttributeType::String,
            'namespace'     =>  array(AttributeType::String, 'default' => ''),

            'cropType'      =>  AttributeType::String,
            'path'          =>  AttributeType::String,

            'width'         =>  AttributeType::Number,
            'height'        =>  AttributeType::Number,

            'image'         =>  AttributeType::Mixed,
            'crop'          =>  AttributeType::Mixed,
        );
    }
}