<?php
    namespace MashCoding\AlexaPHPFramework\helper;

    class ArrayHelper
    {
        public static function areKeysSet (array $needleArray, array $haystack)
        {
            $keysSet = true;
            foreach ($needleArray as $needle) {
                if (!array_key_exists($needle, $haystack)) {
                    $keysSet = false;
                    break;
                }
            };

            return $keysSet;
        }

        public static function validateArrayScheme (array $expectedScheme, array $actualArray)
        {
            $isValid = true;
            foreach ($expectedScheme as $key => $value) {
                if (
                    (!array_key_exists($key, $actualArray) || !isset($value)) ||
                    (is_array($value) && !self::validateArrayScheme($value, $actualArray[$key])) ||
                    (!is_array($value) && !isset($actualArray[$key]))
                ) {
                    return false;
                }
            };

            return $isValid;
        }

        public static function getFilteredArray (array $filter, array $array)
        {
            $filteredArray = [];
            foreach ($filter as $key) {
                $filteredArray[$key] = (array_key_exists($key, $array)) ? $array[$key] : null;
            };

            return $filteredArray;
        }
    }