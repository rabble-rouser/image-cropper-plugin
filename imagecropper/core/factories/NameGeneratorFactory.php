<?php
namespace Core\Factories;

use Core\Generators\Sources\LocalNameGenerator;
use Core\Generators\Sources\RackspaceNameGenerator;

class NameGeneratorFactory extends Factory
{
    public function create($type = null, $args = null)
    {
        if($this->type == $type && $this->obj){
            return $this->obj;
        }

        if(!empty($type)){
            $this->type = $type;
        }

        switch($this->type){
            case 'Local':
                $generator = new LocalNameGenerator($args);
                break;
            case 'Rackspace':
                $generator = new RackspaceNameGenerator($args);
                break;
            case 'Google':
                $generator = null;
                break;
            case 'Amazon':
                $generator = null;
                break;
            default:
                $generator = null;
        }

        $this->obj = $generator;
        return $generator;
    }
}