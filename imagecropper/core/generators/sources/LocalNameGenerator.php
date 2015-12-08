<?php
namespace Core\Generators\Sources;
use Craft\IOHelper;

class LocalNameGenerator extends NameGenerator
{
    public function getNameReplacement()
    {
        $sourceType = $this->craft()->assetSources->getSourceTypeById($this->source->id);
        $fileList = IOHelper::getFolderContents($this->getSourceFileSystemPath($sourceType).$this->folder->path, false);
        $existingFiles = array();

        foreach ($fileList as $file)
        {
            $existingFiles[mb_strtolower(IOHelper::getFileName($file))] = true;
        }

        // Double-check
        if (!isset($existingFiles[mb_strtolower($this->filename)]))
        {
            return $this->filename;
        }

        $fileParts = explode(".", $this->filename);
        $extension = array_pop($fileParts);
        $fileName = join(".", $fileParts);

        for ($i = 1; $i <= 50; $i++)
        {
            if (!isset($existingFiles[mb_strtolower($fileName.'_'.$i.'.'.$extension)]))
            {
                return $fileName.'_'.$i.'.'.$extension;
            }
        }

        return false;
    }

    /**
     * Get the file system path for upload source.
     *
     * @param $sourceType
     *
     * @return string
     */
    public function getSourceFileSystemPath($sourceType = null)
    {
        $path = is_null($sourceType) ? $this->getBasePath($this->source->settings) : $sourceType->getBasePath($this->source->settings);
        $path = IOHelper::getRealPath($path);

        return $path;
    }

    /**
     * Returns the source's base server path.
     *
     * @return string
     */
    public function getBasePath($settings)
    {
        $path = $settings->path;

        return $this->craft()->config->parseEnvironmentString($path);
    }
}