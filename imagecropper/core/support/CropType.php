<?php
namespace Core\Support;
use Craft\BaseEnum;
/**
 * The CropType enum is a static class to keep track of the different type of crops available.
 *
 * This class is a poor man's version of an enum, since PHP does not have support for native enumerations.
 *
 */
abstract class CropType extends BaseEnum
{
    // Constants
    // =========================================================================

    const Coordinates = 'coordinates';
    const Dimensions  = 'dimensions';
    const Imgix   = 'imgix';
}
