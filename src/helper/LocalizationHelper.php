<?php
    namespace MashCoding\AlexaPHPFramework\helper;

    use MashCoding\AlexaPHPFramework\exceptions\ResponseException;

    class LocalizationHelper
    {
        public static function isValidLocale ($locale)
        {
            $Locale = self::getLocale();
            $Locale->invokeData(\Locale::parseLocale($locale));
            return ($Locale->language && strlen($Locale->language) <= 3 && $Locale->region);
        }

        public static function getLocale ()
        {
            return DataHandler::getDataObject()->lang;
        }
        public static function getLocalization ()
        {
            return SettingsHelper::getConfig('localization');
        }

        public static function localize ($message, $properties = [])
        {
            $Localization = self::getLocalization();

            $search = array_map(function ($a) { return '{{' . $a . '}}'; }, array_keys($properties));
            $replace = array_values($properties);
            $message = ($Localization->hasProperty($message)) ? str_replace($search, $replace, $Localization->$message) : $message;
            return $message;
        }

        public static function validateLocale ($locale)
        {
            $Settings = SettingsHelper::getConfig();
            if (!self::isValidLocale($locale) && $Settings->defaults->locale != $locale && !self::isValidLocale($Settings->defaults->locale))
                throw new ResponseException(self::localize("invalid value", ["type" => "locale", "value" => $locale]), ResponseException::CODE_FATAL);

            $Locale = self::getLocale();
            SettingsHelper::parseConfig(FileHelper::getRelativePath(__DIR__ . '/../../lang/') . $Locale->language . '.json', 'localization');
            SettingsHelper::parseConfig($Settings->path->lang . $Locale->language . '.json', 'localization');
        }
    }