<?php
    namespace MashCoding\AlexaPHPFramework\helper;

    class SettingsHelper
    {
        public static function parseConfig ($file, $name = 'settings')
        {
            $Settings = self::getConfig();
            $Settings->invokeData(array_merge($Settings->data(), FileHelper::parseJSON($file)));
            return $Settings;
        }

        public static function getConfig ($name = 'settings')
        {
            return DataHandler::getDataObject()->$name;
        }
    }