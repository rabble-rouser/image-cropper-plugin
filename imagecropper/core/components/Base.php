<?php
namespace Core\Components;
use Craft\ImageCropper_ContextModel;
abstract class Base
{

    public function __construct($attributes = null)
    {
        $this->setAttributes($attributes);

    }

    public function craft()
    {
        return \Craft\craft();
    }

    public function getClass()
    {
        return get_class($this);
    }

    public function setAttribute($name, $value)
    {
        $this->$name = $value;
    }

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

    public function setAttributesByContext(ImageCropper_ContextModel $contextModel)
    {
        $this->setAttributes($contextModel->getAttributes());
    }
}