<?php
    namespace MashCoding\AlexaPHPFramework\helper;

    class SSMLHelper
    {
        /**
         * parses a $string by \n into a valid SSML-syntaxed message
         *
         * @param $string
         *
         * @return string
         *
         * @see https://developer.amazon.com/public/solutions/alexa/alexa-skills-kit/docs/speech-synthesis-markup-language-ssml-reference
         */
        public static function say ($string)
        {
            $string = '<p>' . implode('</p><p>', explode(PHP_EOL, $string)) . '</p>';
            if (strpos($string, '<speak>') === false)
                $string = '<speak>' . $string . '</speak>';

            return $string;
        }
    }