<?php

namespace Core\Interfaces;

use Craft\ImageCropper_ContextModel;

interface CropInterface
{
    public function crop();
    public function save(ImageCropper_ContextModel $contextModel);
    public function cropAndSave(ImageCropper_ContextModel $contextModel);
}