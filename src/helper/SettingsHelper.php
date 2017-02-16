<?php
    namespace MashCoding\AlexaPHPFramework\helper;

    class SettingsHelper
    {
        public static function parseConfig ($file)
        {
            global $__PARSED_SETTINGS;

            $_settings = FileHelper::parseJSON($file);
            
            if (!isset($parsedSettings))
                $__PARSED_SETTINGS = [];

            $__PARSED_SETTINGS = array_merge($__PARSED_SETTINGS, $_settings);
            return $__PARSED_SETTINGS;
        }
    }