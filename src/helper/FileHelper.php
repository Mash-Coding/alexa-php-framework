<?php
    namespace MashCoding\AlexaPHPFramework\helper;

    class FileHelper
    {
        const EXTENSION_PNG = "png";
        const EXTENSION_JPEG = "jpg";
        const EXTENSION_JPG = "jpg";
        const EXTENSION_JSON = "json";

        public static function getRoot ()
        {
            return realpath($_SERVER['DOCUMENT_ROOT'] . '/../') . "/";
        }

        /**
         * gets the file path based on the root path
         *
         * @param $path
         *
         * @return mixed
         */
        public static function getRelativePath ($path)
        {
            return str_replace(self::getRoot(), "/", realpath((!is_dir($path)) ? dirname($path) . '/' : $path) . "/");
        }

        public static function getPublicPath ($path, $absolute = false)
        {
            self::getFilePath($path);

            $Settings = SettingsHelper::getConfig();
            return (($absolute) ? '//' . $_SERVER['HTTP_HOST'] : '') . strtr($path, [
                self::getRoot() => '/',
                $Settings->path->public => '/',
            ]);
        }

        /**
         * parses the specified JSON $file if it exists
         *
         * @param $file
         *
         * @return array
         */
        public static function parseJSON ($file)
        {
            return json_decode(self::getFileContents($file, '{}'), true);
        }

        /**
         * gets the files extension by its path $file
         *
         * @param $file
         *
         * @return mixed|null
         *
         * @todo add possibility to get file extension of remote URLs which haven't a direct extension in the URL
         */
        public static function getFileExtension ($file, $validityCheck = true)
        {
            $ext = null;
            if (!$validityCheck || self::fileExists($file)) {
                $arr = explode('.', basename($file));
                $ext = array_pop($arr);
            }
            return $ext;
        }

        /**
         * returns either the files contents or returns $fallback
         *
         * @param $file
         * @param $fallback
         *
         * @return null|string
         */
        public static function getFileContents ($file, $fallback)
        {
            $content = null;
            if (self::fileExists($file))
                $content = file_get_contents($file);

            return (!empty($content)) ? $content : $fallback;
        }

        /**
         * output given $content to $file if its directory exists and the file is writable
         *
         * @param $file
         * @param $content
         */
        public static function writeContentsToFile ($file, $content)
        {
            $dir = dirname($file);
            if (self::dirExists($dir) && is_writable($dir))
                file_put_contents($dir . '/' . basename($file), $content);
        }

        /**
         * checks if a file (may it be remote or local) exists
         *
         * @param $file
         *
         * @return bool
         */
        public static function fileExists (&$file)
        {
            return URLHelper::isValidURL($file) ? self::fileExistsAtURL($file) : file_exists(self::getFilePath($file));
        }

        /**
         * checks if a file (may it be remote or local) exists
         *
         * @param $dir
         *
         * @return bool
         */
        public static function dirExists (&$dir)
        {
            if (URLHelper::isValidURL($dir))
                return false;

            return is_dir(dirname(self::getFilePath($dir)));
        }

        /**
         * checks if remote file exists with curl
         *
         * @param $url
         *
         * @return bool
         */
        public static function fileExistsAtURL ($url)
        {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_NOBODY, true);
            curl_exec($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            return $code == 200;
        }

        /**
         * gets absolute file path to $file
         *
         * @param $file
         *
         * @return string
         */
        public static function getFilePath (&$file)
        {
            $root = FileHelper::getRoot();
            if (substr($file, 0, strlen($root)) != $root && substr($file, 0, 1) == '/')
                $file = $root . substr($file, 1);

            if (!URLHelper::isValidURL($file))
                $file = realpath($file);

            return $file;
        }

        public static function getMostCurrentFileByFileName ($dir, $fileNameOnly = true, $fileIndex = 0)
        {
            $file = null;
            if (self::dirExists($dir)) {
                $files = scandir($dir, SCANDIR_SORT_DESCENDING);
                $file = (isset($files[$fileIndex]) && $files[$fileIndex] != '.' && $files[$fileIndex] != '..') ? $files[$fileIndex] : null;
            }

            if (isset($file) && !$fileNameOnly)
                $file = self::getRelativePath($dir . '/') . $file;

            return $file;
        }

        public static function getModificationTime ($file)
        {
            return (self::fileExists($file)) ? filemtime($file) : null;
        }

        /**
         * converts $name into a legit filename
         *
         * @param $name
         *
         * @return string
         */
        public static function makeConformName ($name)
        {
            return strtr(strtolower($name), [
                " " => "_",
                "ä" => "ae",
                "ö" => "oe",
                "ü" => "ue",
                "ß" => "ss",

                "%" => "",
                "&" => "",
                "?" => "",
                "!" => "",
                ";" => "",
                ":" => "",
            ]);
        }
    }