<?php
    namespace MashCoding\AlexaPHPFramework\helper;

    class SettingsHelper
    {
        /**
         * parses and then merges specified $file into the global DataObject->$name
         *
         * @param        $file
         * @param string $name
         *
         * @return array|JSONObject|mixed|null
         */
        public static function parseConfig ($file, $name = 'settings')
        {
            $Settings = self::getConfig($name);
            $Settings->invokeData(array_merge($Settings->data(), FileHelper::parseJSON($file)));
            return $Settings;
        }

        /**
         * gets current loaded settings
         *
         * @param string $name
         *
         * @return array|JSONObject|mixed|null
         */
        public static function getConfig ($name = 'settings')
        {
            return DataHandler::getDataObject()->$name;
        }
    }