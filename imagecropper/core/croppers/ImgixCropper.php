<?php
namespace Core\Croppers;
use Craft\ImageCropper_ContextModel;

class ImgixCropper extends Cropper
{
    public function crop()
    {
        // TODO: Implement crop() method.
    }

    public function save(ImageCropper_ContextModel $contextModel)
    {
        return true;
    }

    public function cropAndSave(ImageCropper_ContextModel $contextModel)
    {
        return true;
    }

}