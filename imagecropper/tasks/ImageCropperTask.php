<?php
namespace Craft;

class ImageCropperTask extends BaseTask
{
    private $_assets;

    protected function defineSettings()
    {
        return array(
            'assets' => AttributeType::Mixed,
            'validAssetIds'   => AttributeType::Mixed
        );
    }

    public function getDescription()
    {
        return Craft::t('Asset cleanup');
    }

    public function getTotalSteps()
    {
        $this->_assets = $this->getSettings()->assets;

        return count($this->_assets);
    }

    public function runStep($step)
    {
        $assetId = $this->_assets[$step]->id;

        if(!in_array($assetId, $this->getSettings()->validAssetIds)){
            craft()->assets->deleteFiles($assetId);
        }

        return true;
    }
}