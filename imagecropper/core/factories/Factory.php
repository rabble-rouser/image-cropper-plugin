<?php
namespace Core\Factories;

use Core\Interfaces\FactoryInterface;

abstract class Factory implements FactoryInterface
{
    protected $type;
    protected $obj;

    /**
     * Factory constructor.
     * @param $type
     */
    public function __construct($type)
    {
        $this->type = $type;
    }

    /**
     * Accessor for type
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Accessor for obj
     * @return mixed
     */
    public function getObject()
    {
        return $this->obj;
    }
}