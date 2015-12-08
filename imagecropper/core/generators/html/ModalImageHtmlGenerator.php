<?php
namespace Core\Generators\Html;

class ModalImageHtmlGenerator extends HtmlGenerator
{
    private $asset;
    private $constraint;
    public function __construct($asset, $constraint = 1000)
    {
        $this->asset = $asset;
        $this->constraint = $constraint;
    }
    public function generate()
    {
        // Never scale up the images, so make the scaling factor always <= 1
        $factor = min($this->constraint/ $this->asset->width, $this->constraint / $this->asset->height, 1);
        $imageUrl = $this->asset->url . '?' . uniqid();
        $width = round($this->asset->width * $factor);
        $height = round($this->asset->height * $factor);

        $html = '<img src="'.$imageUrl.'" width="'.$width.'" height="'.$height.'" data-factor="'.$factor.'" data-constraint="'.$this->constraint.'" data-orig-width="'. $this->asset->width .'" data-orig-height="'. $this->asset->height .'"/>';

        return $html;
    }
}