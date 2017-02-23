<?php
    namespace MashCoding\AlexaPHPFramework\helper;

    class URLHelper
    {
        const PROTOCOL_HTTP = "http";
        const PROTOCOL_HTTPS = "https";
        const PROTOCOL_FTP = "ftp";

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
            return filter_var($url, FILTER_VALIDATE_URL) && (!isset($protocol) || self::parseURL($url, 'scheme') == $protocol);
        }

        public static function stripQueryString ($url)
        {
            return self::isValidURL($url) ? preg_replace('/\\?.*/', '', $url) : $url;
        }

        public static function parseURL ($url, $format = null, $fallback = null)
        {
            $parsed = parse_url($url);
            if (isset($format))
                $parsed = strtr($format, $parsed);

            if (isset($fallback) && $parsed == $format)
                $parsed = $fallback;

            return $parsed;
        }

        public static function normalizeURL ($url)
        {
            $port = self::parseURL($url, 'port', false);
            $host = self::parseURL($url, 'scheme://host') . (($port) ? ':' . $port : '') . '/';

            $path = explode('/', self::parseURL($url, 'path'));
            $normalized = [];
            foreach ($path as $part) {
                $part = trim($part);
                if ($part == '' || $part == '.')
                    continue;
                else if ($part == '..')
                    array_pop($normalized);
                else
                    $normalized[] = $part;
            };

            return $host . implode('/', $normalized);
        }
    }