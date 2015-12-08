<?php
namespace Craft;

class ImageCropper_SettingsModel extends BaseModel
{
    protected function defineAttributes()
    {
        return array(
            'height'                   => AttributeType::Mixed,
            'width'                    => AttributeType::Mixed,
            'displaySectionSettings'   => AttributeType::Mixed,
            'numberOfCrops'            => array(AttributeType::Mixed, 'default' => 3),
            'source'                   => AttributeType::String,
        );
    }

    public function rules()
    {
        $rules = parent::rules();
        $rules[] = ['numberOfCrops', 'validateNumberOfCrops'];

        return $rules;
    }

    public function validateNumberOfCrops($attribute)
    {
        $sectionsArrs = $this->$attribute;

        $breakParent = false;
        foreach($sectionsArrs as $sectionArr){
            foreach($sectionArr as $value){
                if (!empty($value) && $value < 1) {
                    $message = Craft::t("Number of crops cannot be less than one (1).");
                    $this->addError($attribute, $message);
                    $breakParent = true;
                    break;
                }
            }
            if($breakParent)
            {
                break;
            }
        }
    }

}