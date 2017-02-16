<?php
    namespace MashCoding\AlexaPHPFramework;

    use MashCoding\AlexaPHPFramework\helper\ArrayHelper;
    use MashCoding\AlexaPHPFramework\helper\JSONObject;

    class Request extends JSONObject
    {
        const TYPE_INTENT = "IntentRequest";
        const TYPE_LAUNCH = "LaunchRequest";
        const TYPE_SESSION_END = "SessionEndRequest";

        public static $VALID_TYPES = [
            self::TYPE_INTENT, self::TYPE_LAUNCH, self::TYPE_SESSION_END,
        ];

        private static $BASE_REQUEST   = [
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
        private static $REQUEST_SCHEME = [
            "version" => "string",
            "session" => [
                "new" => "boolean",
                "sessionId" => "string",
                "application" => [
                    "applicationId" => "string",
                ],
                "attributes" => "array",
                "user" => [
                    "userId" => "string",
                    "?accessToken" => "string",
                ],
            ],
            "context" => [
                "System" => [
                    "application" => [
                        "applicationId" => "string",
                    ],
                    "user" => [
                        "userId" => "string",
                    ],
                    "device" => [
                        "?supportedInterfaces" => [
                            "?AudioPlayer" => "array",
                        ],
                    ],
                ],
                "?AudioPlayer" => [
                    "?token" => "string",
                    "offsetInMilliseconds" => "number",
                    "playerActivity" => "string"
                ],
            ],
            "request" => [
                "type"      => "string",
                "requestId" => "string",
                "timestamp" => "string",
                "locale"    => "language",
            ],
        ];

        private static function validateRequest (&$input)
        {
            if (empty($input))
                return false;
            
            $input = array_merge(self::$BASE_REQUEST, json_decode($input, true));

            try {
                ArrayHelper::validateArrayScheme(self::$REQUEST_SCHEME, $input);
            } catch (\Exception $e) {
                $input = null;
                return false;
            }

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