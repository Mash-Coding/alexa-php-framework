<?php
    namespace MashCoding\AlexaPHPFramework\helper;

    class URLHelper
    {
        const PROTOCOL_HTTP = "http://";
        const PROTOCOL_HTTPS = "https://";
        const PROTOCOL_FTP = "ftp://";

        public static function isValidURL ($url, $protocol = null)
        {
            return filter_var($url, FILTER_VALIDATE_URL) && (!isset($protocol) || substr($url, 0, strlen($protocol)) == $protocol);
        }
    }