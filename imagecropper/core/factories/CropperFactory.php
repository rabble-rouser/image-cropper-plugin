<?php
namespace Core\Factories;

use Core\Croppers\CoordinateCropper;
use Core\Croppers\DimensionCropper;
use Core\Croppers\ImgixCropper;

class CropperFactory extends Factory
{
    /**
     * Create a cropper class
     * @param null $type
     * @param null $args
     * @return CoordinateCropper|DimensionCropper|ImgixCropper|null
     */
    public function create($type = null, $args = null)
    {
        if($this->type == $type && $this->obj){
            return $this->obj;
        }

        if(!empty($type)){
            $this->type = $type;
        }

        switch($this->type){
            case 'coordinates':
                $cropper = new CoordinateCropper($args);
                break;
            case 'dimensions':
                $cropper = new DimensionCropper($args);
                break;
            case 'imgix':
                $cropper = new ImgixCropper($args);
                break;
            default:
                $cropper = null;
        }

        $this->obj = $cropper;
        return $cropper;
    }
}