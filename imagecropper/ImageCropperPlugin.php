<?php
namespace Craft;

class ImageCropperPlugin extends BasePlugin
{
    /* --------------------------------------------------------------
    * PLUGIN INFO
    * ------------------------------------------------------------ */

    public function getName()
    {
        return Craft::t('Image Cropper');
    }

    public function getVersion()
    {
        return 'Beta';
    }

    public function getDeveloper()
    {
        return 'Rabble and Rouser';
    }

    public function getDeveloperUrl()
    {
        return 'http://www.rabbleandrouser.com';
    }

    public function getSettingsHtml()
    {
        // Entries
        $editableSections = array();
        $allSections = craft()->sections->getAllSections();

        foreach ($allSections as $section)
        {
            // No sense in doing this for singles.
            $editableSections[$section->handle]['section'] = $section;
            $entryTypes = $section->getEntryTypes();
            $editableSections[$section->handle]['entryTypes'] = $entryTypes;
        }

        $assetSources = craft()->assetSources->getAllSources();

        return craft()->templates->render('imageCropper/_plugin/settings', array(
            'settings' => $this->getSettings(),
            'editableSections' => $editableSections,
            'assetSources'      => $assetSources,
        ));
    }

    /**
     * @return \Craft\ImageCropper_SettingsModel
     */
    protected function getSettingsModel()
    {
        return new ImageCropper_SettingsModel();
    }


    /* --------------------------------------------------------------
    * HOOKS
    * ------------------------------------------------------------ */

    public function init()
    {
        parent::init();
        require CRAFT_PLUGINS_PATH.'/ImageCropper/vendor/autoload.php';
    }
}
