<?php
namespace MashCoding\AlexaPHPFramework\helper;

class ObjectHelper
{
    public static function getClassname ($class)
    {
        if (is_object($class))
            $class = get_class($class);

        $arr = explode('\\', $class);
        return array_pop($arr);
    }
}