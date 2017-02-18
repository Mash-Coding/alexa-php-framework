<?php
    namespace MashCoding\AlexaPHPFramework\helper;

    class SSMLHelper
    {
        const BREAK_SOFT = "\\s";
        const BREAK_HARD = PHP_EOL;

        const PAUSE_NONE = 'none';
        const PAUSE_WEAK = 'weak';
        const PAUSE_MEDIUM = 'medium';
        const PAUSE_STRONG = 'strong';
        const PAUSE_LONG = 'x-strong';

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
            $string = '<p>' . strtr($string, [
                    self::BREAK_HARD => '</p><p>',
                    self::BREAK_SOFT => PHP_EOL,
                    '  ' => ' '
                ]) . '</p>';

            return self::finalize($string);
        }

        public static function finalize ($string)
        {
            if (strpos($string, '<speak>') === false)
                $string = '<speak>' . $string . '</speak>';

            return $string;
        }

        /**
         * outputs a list as valid SSML (with seperators and such)
         *
         * @param array       $list
         * @param string|null $divider
         * @param string|null $lastDivider
         * @param string      $title
         * @param string|null $beginSeperator
         * @param string|null $atEnd
         *
         * @return string
         */
        public static function sayList (array $list, $divider = null, $lastDivider = null, $title = "", $beginSeperator = null, $atEnd = null)
        {
            if (!isset($divider))
                $divider = self::BREAK_SOFT;
            if (!isset($lastDivider))
                $lastDivider = $divider . ' and ';
            if (!isset($beginSeperator))
                $beginSeperator = self::BREAK_SOFT;

            $last = null;
            if (count($list) > 1)
                $last = array_pop($list);

            return self::say((($title) ? $title . ':' . $beginSeperator : '') . implode($divider, $list) . (($last) ? $lastDivider . $last : '') . (($atEnd) ? $atEnd : ''));
        }

        public static function pause ($pauseType = self::PAUSE_STRONG)
        {
            $pause = (preg_match('/\\d/', $pauseType)) ? floatval($pauseType) : $pauseType;
            return '<break ' . ((is_numeric($pause)) ? 'time="' . $pause . 's"' : 'strength="' . $pause . '"') . '/>';
        }
    }