<?php
namespace Core\Components;
use Craft\ImageCropper_ContextModel;
abstract class Base
{

    /**
     * Base constructor.
     * @param null $attributes
     */
    public function __construct($attributes = null)
    {
        $this->setAttributes($attributes);

    }

    /**
     * @return mixed
     */
    public function craft()
    {
        return \Craft\craft();
    }

    public function getClass()
    {
        return get_class($this);
    }

    /**
     * @param $name
     * @param $value
     */
    public function setAttribute($name, $value)
    {
        $this->$name = $value;
    }

    /**
     * @param null $attributes
     */
    public function setAttributes($attributes = null)
    {
        if(isset($attributes) && is_array($attributes))
        {
            foreach($attributes as $name => $value)
            {
                $this->setAttribute($name, $value);
            }
        }
    }

    /**
     * @param $name
     * @return null
     */
    public function getAttribute($name)
    {
        if(isset($this->$name))
        {
            return $this->$name;
        }

        return null;
    }

    /**
     * Returns an array of attribute values.
     * @return array
     */
    public function getAttributes()
    {
        return get_class_vars($this->getClass());
    }

    /**
     * Set attributes from an ImageCropper_ContextModel
     * @param ImageCropper_ContextModel $contextModel
     */
    public function setAttributesByContext(ImageCropper_ContextModel $contextModel)
    {
        $this->setAttributes($contextModel->getAttributes());
    }
}