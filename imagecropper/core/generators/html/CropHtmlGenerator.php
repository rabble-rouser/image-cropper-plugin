<?php
namespace Core\Generators\Html;

use Craft\ImageCropper_ContextModel;

class CropHtmlGenerator extends HtmlGenerator
{
    protected $context;

    /**
     * CropHtmlGenerator constructor.
     * @param ImageCropper_ContextModel|null $contextModel
     */
    public function __construct(ImageCropper_ContextModel $contextModel = null)
    {
        $this->context = $contextModel;
    }

    /**
     * generate the html for a crop image
     * @return string
     */
    public function generate()
    {
        $html = '';
        if(!empty($this->context)){
            $context = $this->context;
            $element = $context->crop;
            $baseElement = $context->asset;
            $elementId = $element->id;
            $elementLocale = $element->locale;
            $title = $element->title;
            $url = $element->url;
            $originalUrl = $baseElement->url;
            $originalId = $baseElement->id;
            $width = $context->width;
            $height = $context->height;
            $thumb = $element->getThumbUrl(30);
            $name = $context->name;
            $namespace = $context->namespace;
            if(!empty($namespace)){
                $nameSegment = $namespace . '[' . $name . '][crops][]';
            }
            else{
                $nameSegment = $name . '[crops][]';
            }

            $html =
                '<div class="element hasthumb -pad-right" data-id="'. $elementId.'" data-locale="'. $elementLocale .'" data-label="'. $title .'" data-url="'. $url . '" data-original-url="'. $originalUrl . '"data-width="'. $width . '"data-height="'. $height .'" data-editable="" data-orig-id="'. $originalId.'" tabindex="0" style="visibility: visible;"> ' .
                '<div class="elementthumb elementthumb20"> ' .
                '   <input type="hidden" name="'. $nameSegment .'" value="'. $element->id .'">' .
                '   <a class="view-image">' .
                '       <img src="'. $thumb .'"> ' .
                '   </a>' .
                '</div> ' .
                '<div class="label"> ' .
                '<span class="title">' . $width . 'x' . $height . '</span> ' .
                '</div> ' .
                '<a class="add icon imgCropper-viewAsset" title="View"></a> ' .
                '</div>';
        }

        return $html;
    }

    /**
     * Mutator for $context
     * @param $context
     */
    public function setContext($context)
    {
        $this->context = $context;
    }
}