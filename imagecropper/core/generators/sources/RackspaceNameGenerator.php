<?php
namespace Core\Generators\Sources;

class RackspaceNameGenerator extends NameGenerator
{

    /**
     * get a file name replacement
     *
     * Note: we are doing this here because with RackspaceSourceType, in the getNameReplacement(),
     * filename is returned as folder+filename, yet Craft 'cleans' the filename, so we cannot append a folder name.
     *
     * @return mixed
     */
    public function getNameReplacement()
    {
        $sourceId = $this->source->id;
        $prefix = $this->getPathPrefix($this->source->settings).$this->folder->path;
        $files = $this->craft()->assets->getFilesBySourceId($sourceId);

        $fileList = array();

        foreach ($files as $file)
        {
            $fileList[mb_strtolower($file->filename)] = true;
        }

        // Double-check
        if (!isset($fileList[mb_strtolower($this->filename)]))
        {
            return $this->filename;
        }

        $fileNameParts = explode(".", $this->filename);
        $extension = array_pop($fileNameParts);

        $fileNameStart = join(".", $fileNameParts).'_';
        $index = 1;

        while ( isset($fileList[mb_strtolower($prefix.$fileNameStart.$index.'.'.$extension)]))
        {
            $index++;
        }

        $this->filename = $fileNameStart.$index.'.'.$extension;
    }

    /**
     * Return a prefix for Rackspace path for settings.
     *
     * @param object|null $settings The settings to use. If null, will use the current settings.
     *
     * @return string
     */
    public function getPathPrefix($settings)
    {

        if (!empty($settings->subfolder))
        {
            return rtrim($settings->subfolder, '/').'/';
        }

        return '';
    }
}