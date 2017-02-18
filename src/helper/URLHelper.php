<?php
    namespace MashCoding\AlexaPHPFramework\helper;

    class URLHelper
    {
        const PROTOCOL_HTTP = "http://";
        const PROTOCOL_HTTPS = "https://";
        const PROTOCOL_FTP = "ftp://";

        /**
         * checks if given $url is valid and if $protocol set, if $url starts with $protocol
         *
         * @param      $url
         * @param null $protocol
         *
         * @return bool
         */
        public static function isValidURL ($url, $protocol = null)
        {
            return filter_var($url, FILTER_VALIDATE_URL) && (!isset($protocol) || substr($url, 0, strlen($protocol)) == $protocol);
        }

        public static function stripQueryString ($url)
        {
            return self::isValidURL($url) ? preg_replace('/\\?.*/', '', $url) : $url;
        }
    }