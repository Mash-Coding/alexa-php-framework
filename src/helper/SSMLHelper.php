<?php
    namespace MashCoding\AlexaPHPFramework\helper;

    class SSMLHelper
    {
        public static function say ($string)
        {
            $string = '<p>' . implode('</p><p>', explode(PHP_EOL, $string)) . '</p>';
            if (strpos($string, '<speak>') === false)
                $string = '<speak>' . $string . '</speak>';

            return $string;
        }
    }