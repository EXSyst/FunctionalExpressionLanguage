<?php
namespace EXSyst\Component\FunctionalExpressionLanguage;

class PatchHelper
{
    private function __construct() { }

    public static function getProperty($class, $property)
    {
        $reflectionClass = new \ReflectionClass($class);
        $reflectionProperty = $reflectionClass->getProperty($property);
        $reflectionProperty->setAccessible(true);
        return $reflectionProperty;
    }

    public static function get($receiver, $class, $property)
    {
        return static::getProperty($class, $property)->getValue($receiver);
    }

    public static function set($receiver, $class, $property, $value)
    {
        static::getProperty($class, $property)->setValue($receiver, $value);
    }
}
