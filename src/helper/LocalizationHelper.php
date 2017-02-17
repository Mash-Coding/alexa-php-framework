<?php
    namespace MashCoding\AlexaPHPFramework\helper;

    use MashCoding\AlexaPHPFramework\exceptions\ResponseException;

    class LocalizationHelper
    {
        public static function isValidLocale ($locale)
        {
            $Localization = self::getLocale();
            $Localization->invokeData(\Locale::parseLocale($locale));
            return ($Localization->language && strlen($Localization->language) <= 3 && $Localization->region);
        }

        public static function getLocale ()
        {
            return DataHandler::getDataObject()->lang;
        }

        public static function validateLocale ($locale)
        {
            $Settings = SettingsHelper::getConfig();
            if (!self::isValidLocale($locale) && $Settings->defaults->locale != $locale && !self::isValidLocale($Settings->defaults->locale))
                throw new ResponseException("Invalid locale '" . $locale . "'", ResponseException::CODE_FATAL);
        }
    }