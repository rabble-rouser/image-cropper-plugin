<?php namespace Craft;

class ImageCropperVariable
{

    /**
     * Get a crop by dimension
     * @param ImageCropper_CriteriaModel $criteria
     * @param array $dimensions
     * @return array|bool
     */
    public function getCrop(ImageCropper_CriteriaModel $criteria, Array $dimensions)
    {
        return $criteria->getCrop($dimensions);
    }

    /**
     * Get the dimensions for an entry
     * @param EntryModel $entry
     * @return mixed
     */
    public function getDimensions(EntryModel $entry)
    {
        $sectionHandle = $entry->section->handle;
        $entryTypeHandle = $entry->type->handle;
        return craft()->imageCropper->getDimensionsArray($sectionHandle, $entryTypeHandle);
    }
}