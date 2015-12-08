<?php

namespace Core\Support;

class Helper
{
    /**
     * check for empty values
     * @param $array
     * @return bool
     */
    public static function arrHasEmptyValues($array)
    {
        $hasEmpty = false;
        foreach($array as $key=>$value){
            if(empty($value)){
                $hasEmpty = true;
                break;
            }
        }
        return $hasEmpty;
    }
}