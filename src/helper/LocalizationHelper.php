<?php
    namespace MashCoding\AlexaPHPFramework\helper;

    use MashCoding\AlexaPHPFramework\exceptions\ResponseException;

    class LocalizationHelper
    {
        /**
         * checke if given $locale is a valid locale
         *
         * @param $locale
         *
         * @return bool
         *
         * @example en-US, de-DE, fr-CA
         */
        public static function isValidLocale ($locale)
        {
            $Locale = self::getLocale();
            $Locale->invokeData(\Locale::parseLocale($locale));
            return ($Locale->language && strlen($Locale->language) <= 3 && $Locale->region);
        }

        /**
         * gets the currently active Locale
         * @return JSONObject|null
         */
        public static function getLocale ()
        {
            return DataHandler::getDataObject()->lang;
        }
        /**
         * returns the currently loaded translations
         * @return JSONObject|null
         */
        public static function getLocalization ()
        {
            return SettingsHelper::getConfig('localization');
        }

        /**
         * tries to find $message in current translations and replaces $properies, if $message is not found in loaded
         * translations, the original message will be returned
         *
         * @param       $message
         * @param array $properties
         *
         * @example ("test message with {{value}}", ["value" => "a value"]) => "test message a value"
         * @example ("test message with {{0}}", ["another value"])          => "test message another value"
         *
         * @return mixed
         */
        public static function localize ($message, $properties = [])
        {
            $Localization = self::getLocalization();

            $search = array_map(function ($a) { return '{{' . $a . '}}'; }, array_keys($properties));
            $replace = array_values($properties);
            $message = ($Localization->hasProperty($message)) ? str_replace($search, $replace, $Localization->$message) : $message;
            return $message;
        }

        /**
         * loads base translations for currently set Locale
         *
         * @param $locale
         *
         * @throws ResponseException
         */
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