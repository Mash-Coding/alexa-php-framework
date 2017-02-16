<?php
    namespace MashCoding\AlexaPHPFramework\helper;

    class FileHelper
    {
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

        public static function getFileContents (&$file, $fallback)
        {
            $content = null;
            if (self::fileExists($file))
                $content = file_get_contents($file);

            return (!empty($content)) ? $content : $fallback;
        }

        public static function fileExists (&$file)
        {
            return file_exists(self::getFilePath($file));
        }

        public static function getFilePath (&$file)
        {
            if (substr($file, 0, 1) == '/')
                $file = FileHelper::getRoot() . substr($file, 1);

            $file = realpath($file);

            return $file;
        }
    }