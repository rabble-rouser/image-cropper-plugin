# Image Cropper Plugin

This plugin allows you to create autocrops of images of specified dimensions via a Cropper Image field type.

## Installation

* Download & unzip
* Move the /imagecropper folder to craft/plugins
* run composer install from the craft/plugins/imagecropper directory
* Install from the Control Panel

### Setup

* From within the Image Cropper plugin settings, first specify an asset source to store your image crops.
  * Supported Asset sources at this time are Local and Rackspace.
  * **Important**: _This source should only be used for the cropped images._
* Specify the number of crops and crop dimensions for the entry types that you will use the Cropper Image field type in.
* Create a Cropper Image field type and assign it to an entry.
* Select an image from the Cropper Image field. Crops will be auto created upon select.
* Click the image to view the crops. If you do not like them, click the blue plus (+) to manually crop.
* Save the entry!

### Template Usage

Accessing your Cropper Image field on an entry in the templates will give you an ImageCropper_CriteriaModel. This gives you access to Craft criteria model methods such as

* first()
* last()
* ids()
* total()
* count()
* find()
* nth()

As well as added methods

* getImages() - get the original image
* getCrops() - get an array of all of the cropped images
* getCrop($dimensions) - get a crop by it's dimension

Example Usage:

```
{{ entry.cropperImage.find() }}
{{ entry.cropperImage.getImages() }}
{{ entry.cropperImage.getCrops() }}
{{ entry.cropperImage.getCrop({
    'width': 100,
    'height': 100
    }) }}
}
```

Additionally there are two methods available in the variable class for template use

* getCrop(ImageCropper_CriteriaModel $criteria, Array $dimensions) - get a crop from a criteria and given dimensions
* getDimensions(EntryModel $entry) - get the dimensions array for an entry

Example Usage:

```
 {{ craft.imageCropper.getCrop(entry.cropperImage, {'width': 100,'height': 100}) }}
 {{ craft.imageCropper.getDimensions(entry) }}
```


### Road Map:

* Add support for imgix
* Add support for Amazon s3
* Add support for Google Cloud Storage


_This plugin is in Beta so please report any issues and suggestions!_