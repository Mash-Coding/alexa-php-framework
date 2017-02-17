<?php
namespace MashCoding\AlexaPHPFramework\helper;

class ObjectHelper
{
    /**
     * returns the class name of a class without its namespaces
     *
     * @param $class
     *
     * @return mixed
     */
    public static function getClassname ($class)
    {
        if (is_object($class))
            $class = get_class($class);

        $arr = explode('\\', $class);
        return array_pop($arr);
    }
}