<?php
    namespace MashCoding\AlexaPHPFramework\helper;

    class SettingsHelper
    {
        public static function parseConfig ($file)
        {
            global $__PARSED_SETTINGS;

            $_settings = FileHelper::parseJSON($file);
            
            if (!isset($__PARSED_SETTINGS))
                $__PARSED_SETTINGS = [];

            $__PARSED_SETTINGS = array_merge($__PARSED_SETTINGS, $_settings);

            return $__PARSED_SETTINGS;
        }

        public static function getConfig ()
        {
            global $__PARSED_SETTINGS;
            return $__PARSED_SETTINGS;
        }
    }