<?php
    namespace MashCoding\AlexaPHPFramework;

    use MashCoding\AlexaPHPFramework\exceptions\ResponseException;
    use MashCoding\AlexaPHPFramework\helper\ArrayHelper;
    use MashCoding\AlexaPHPFramework\helper\FileHelper;
    use MashCoding\AlexaPHPFramework\helper\JSONObject;
    use MashCoding\AlexaPHPFramework\helper\LocalizationHelper;
    use MashCoding\AlexaPHPFramework\helper\SettingsHelper;

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
                        "applicationId" => "",
                    ],
                    "user" => [
                        "userId" => "",
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
            "?context" => [
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
                "?intent"   => [
                    "name"  => "string",
                    "slots" => "array",
                ],
                "?error" => [
                    "type" => "string",
                    "message" => "string"
                ],
            ],
        ];

        /**
         * validates given $input by merging it with a default request scheme and then validating the values
         *
         * @param $input
         *
         * @return bool
         */
        private static function validateRequest (&$input)
        {
            if (empty($input))
                return false;
            
            $input = array_merge(self::$BASE_REQUEST, json_decode($input, true));
            try {
                ArrayHelper::validateArrayScheme(self::$REQUEST_SCHEME, $input);

                LocalizationHelper::validateLocale($input['request']['locale']);
            } catch (\Exception $e) {
                $input = null;
                return false;
            }

            return true;
        }

        /**
         * parses either stdin or given $input and creates a appropriate response
         *
         * @param null|string $configFile    path to a JSON-config file
         * @param null|string $stdinOverride manual override for default PHP STDIN
         */
        public static function run ($configFile = null, $stdinOverride = null)
        {
            SettingsHelper::parseConfig(FileHelper::getRelativePath(__DIR__ . '/../config/') . "default.json");
            SettingsHelper::parseConfig((isset($configFile)) ? $configFile : '/config/alexa.json');

            $AlexaRequest  = new \MashCoding\AlexaPHPFramework\Request($stdinOverride);
            $AlexaResponse = \MashCoding\AlexaPHPFramework\Response::fromRequest($AlexaRequest);

            try {
                $AlexaResponse->fetch();
            } catch (ResponseException $e) {

                if ($e->getCode() == ResponseException::CODE_REPROMT)
                    $AlexaResponse->ask($e->sayMessage());
                else
                    $AlexaResponse->respond($e->sayMessage());

            } catch (\Exception $e) {
                var_dump($e); print ' in ' . __FILE__ . '::' . __LINE__ . PHP_EOL . PHP_EOL;
            }

//            header('Content-Type: application/json; charset=UTF-8');
            echo $AlexaResponse->json();
        }

        public function __construct ($stdin = null)
        {
            if (!isset($stdin))
                $stdin = file_get_contents('php://input');

            if (!empty($stdin) && self::validateRequest($stdin))
                parent::__construct($stdin);
        }
    }