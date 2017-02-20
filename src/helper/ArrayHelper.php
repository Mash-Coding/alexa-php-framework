<?php
    namespace MashCoding\AlexaPHPFramework\helper;

    use MashCoding\AlexaPHPFramework\exceptions\ResponseException;

    class ArrayHelper
    {
        /**
         * @param array $source
         * @param array $merge
         *
         * @return array
         */
        public static function merge ($source = [], $merge = [])
        {
            if (!is_array($source))
                $source = [];

            foreach ($merge as $key => $value) {
                $source[$key] = (is_array($value) && isset($source[$key])) ? self::merge($source[$key], $value) : $value;
            };

            for ($i = 2; $i < func_num_args(); ++$i) {
                $moreMerge = func_get_arg($i);
                if (is_array($moreMerge))
                    $source = self::merge($source, $moreMerge);
            };

            return $source;
        }

        /**
         * checks if all keys from $needleArray are set in $haystack
         *
         * @param array $needleArray
         * @param array $haystack
         *
         * @return bool
         */
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

        /**
         * checks if specified $expectedScheme is given in $actualArray. Additionally this method will do a type cast
         * to provide type-safety
         *
         * @param array $expectedScheme
         * @param array $actualArray
         *
         * @return bool
         * @throws \Exception
         */
        public static function validateArrayScheme (array $expectedScheme, array &$actualArray)
        {
            $isValid = true;
            foreach ($expectedScheme as $key => $type) {
                $optional = (substr($key, 0, 1) == '?');
                if ($optional)
                    $key = substr($key, 1);

                if (!array_key_exists($key, $actualArray) && $type !== "array") {
                    if (!$optional)
                        throw new \Exception("Key '" . $key . "' does not exist in array", ResponseException::CODE_FATAL);
                } else if (!is_array($type)) {

                    $value = (isset($actualArray[$key])) ? $actualArray[$key] : null;
                    switch ($type) {
                        case "array":
                            $valid = is_array($value) || $value === null;

                            if (!$valid || $value === null)
                                $value = [];
                        break;

                        case "float":
                        case "number":
                        case "integer":
                            $valid = is_numeric($value);
                            if ($valid && $type == "float") {
                                $valid = is_float($value);
                                if ($valid)
                                    $value = floatval($value);
                            } else if ($valid) {
                                $value = (int)$value;
                            }
                        break;

                        case "language":
                        case "string":
                            $valid = is_string($value);
                            if ($type == "language" && $valid)
                                $valid = LocalizationHelper::isValidLocale($value);
                        break;

                        default:
                            $valid = isset($value);
                    };

                    if (!$valid)
                        throw new \Exception("Value for key '" . $key . "' does not match expected type '" . $type . "'", ResponseException::CODE_FATAL);
                    else
                        $actualArray[$key] = $value;
                } else if (is_array($type)) {
                    if (!self::validateArrayScheme($type, $actualArray[$key]))
                        $isValid = false;
                }
            };

            return $isValid;
        }

        /**
         * gets only those values of $array, whose key is specified in $filter
         *
         * @param array $filter
         * @param array $array
         *
         * @return array
         */
        public static function getFilteredArray (array $filter, array $array)
        {
            $filteredArray = [];
            foreach ($filter as $key) {
                $filteredArray[$key] = (array_key_exists($key, $array)) ? $array[$key] : null;
            };

            return $filteredArray;
        }
    }