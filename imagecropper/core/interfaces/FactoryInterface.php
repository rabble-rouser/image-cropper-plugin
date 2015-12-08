<?php
namespace Core\Interfaces;

interface FactoryInterface
{
    public function create($type, $args);
}