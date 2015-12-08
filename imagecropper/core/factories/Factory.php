<?php
namespace Core\Factories;

use Core\Interfaces\FactoryInterface;

abstract class Factory implements FactoryInterface
{
    protected $type;
    protected $obj;

    public function __construct($type)
    {
        $this->type = $type;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getObject()
    {
        return $this->obj;
    }
}