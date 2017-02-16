<?php
    namespace MashCoding\AlexaPHPFramework\helper;

    class FileHelper
    {
        const EXTENSION_PNG = "png";
        const EXTENSION_JPEG = "jpg";
        const EXTENSION_JPG = "jpg";

        public static function getRoot ()
        {
            return realpath($_SERVER['DOCUMENT_ROOT'] . '/../') . "/";
        }

        public static function getRelativePath ($path)
        {
            return str_replace(self::getRoot(), "/", realpath($path) . "/");
        }

        public static function parseJSON ($file)
        {
            return json_decode(self::getFileContents($file, '{}'), true);
        }

        public static function getFileExtension ($file)
        {
            $ext = null;

            if (self::fileExists($file)) {
                $arr = explode('.', basename($file));
                $ext = array_pop($arr);
            }

            return $ext;
        }

        public static function getFileContents (&$file, $fallback)
        {
            $content = null;
            if (self::fileExists($file))
                $content = file_get_contents($file);

            return (!empty($content)) ? $content : $fallback;
        }

        public static function fileExists (&$file)
        {
            return URLHelper::isValidURL($file) ? self::fileExistsAtURL($file) : file_exists(self::getFilePath($file));
        }

        public static function fileExistsAtURL ($url)
        {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_NOBODY, true);
            curl_exec($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            return $code == 200;
        }

        public static function getFilePath (&$file)
        {
            if (substr($file, 0, 1) == '/')
                $file = FileHelper::getRoot() . substr($file, 1);

            if (!URLHelper::isValidURL($file))
                $file = realpath($file);

            return $file;
        }
    }