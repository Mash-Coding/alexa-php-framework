<?php
    namespace MashCoding\AlexaPHPFramework;

    use MashCoding\AlexaPHPFramework\helper\JSONObject;

    class OutputSpeech extends JSONObject
    {
        const RESPONSE_TYPE_PLAIN = 'PlainText';
        const RESPONSE_TYPE_SSML = 'SSML';

        private static $RESPONSE_TYPE_PROPERTY = [
            self::RESPONSE_TYPE_PLAIN => "text",
            self::RESPONSE_TYPE_SSML => "ssml",
        ];

        /**
         * returns the current response type of the object
         *
         * @param $text
         *
         * @return string
         */
        public static function getResponseType ($text)
        {
            return ($text != strip_tags($text)) ? self::RESPONSE_TYPE_SSML : self::RESPONSE_TYPE_PLAIN;
        }

        /**
         * OutputSpeech constructor.
         *
         * @param array $message
         */
        public function __construct ($message)
        {
            $responseType = self::getResponseType($message);
            parent::__construct([
                "type" => $responseType,
                 self::$RESPONSE_TYPE_PROPERTY[$responseType] => $message
            ]);
        }
        
    }