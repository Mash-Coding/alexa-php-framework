<?php
    namespace MashCoding\AlexaPHPFramework;

    use MashCoding\AlexaPHPFramework\helper\ArrayHelper;
    use MashCoding\AlexaPHPFramework\helper\JSONObject;

    class Request extends JSONObject
    {
        private static $BASE_REQUEST_SCHEME = [
            "version" => null,
            "session" => [
                "new" => null,
                "sessionId" => null,
                "application" => [
                    "applicationId" => null,
                ],
                "attributes" => [],
                "user" => [
                    "userId" => null,
                ],
            ],
            "context" => [
                "System" => [
                    "application" => [
                        "applicationId" => null,
                    ],
                    "user" => [
                        "userId" => null,
                    ],
                    "device" => [],
                ],
            ],
            "request" => [
                "type" => null,
                "requestId" => null,
                "timestamp" => null,
                "locale" => null,
            ],
        ];

        private static function validateRequest (&$input)
        {
            if (empty($input))
                return false;
            
            $input = array_merge(self::$BASE_REQUEST_SCHEME, json_decode($input, true));

            return true;
        }

        public function __construct ($stdin = null)
        {
            if (!isset($stdin))
                $stdin = file_get_contents('php://input');

            if (!empty($stdin) && self::validateRequest($stdin))
                parent::__construct($stdin);
        }
    }