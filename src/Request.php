<?php
    namespace MashCoding\AlexaPHPFramework;

    use MashCoding\AlexaPHPFramework\exceptions\ResponseException;
    use MashCoding\AlexaPHPFramework\exceptions\SignatureException;
    use MashCoding\AlexaPHPFramework\helper\ArrayHelper;
    use MashCoding\AlexaPHPFramework\helper\DataHandler;
    use MashCoding\AlexaPHPFramework\helper\FileHelper;
    use MashCoding\AlexaPHPFramework\helper\JSONObject;
    use MashCoding\AlexaPHPFramework\helper\LocalizationHelper;
    use MashCoding\AlexaPHPFramework\helper\SettingsHelper;
    use MashCoding\AlexaPHPFramework\helper\URLHelper;
    use SebastianBergmann\CodeCoverage\Report\Html\File;

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
                    "?offsetInMilliseconds" => "number",
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
         * @throws SignatureException
         * @return bool
         */
        private static function validateRequest (&$input)
        {
            if (empty($input))
                throw new SignatureException(400, "empty stdin");

            $Settings = SettingsHelper::getConfig();

            $validate = (DEBUG) ? FileHelper::parseJSON('/dev/amazon_request/amazon_request_header_example.json') : $_SERVER;
            if (!isset($validate['HTTP_SIGNATURECERTCHAINURL']))
                throw new SignatureException(401, "HTTP_SIGNATURECERTCHAINURL not set in http header");
            else if (!isset($validate['HTTP_SIGNATURE']))
                throw new SignatureException(401, "HTTP_SIGNATURE not set in http header");

            // Verify the URL specified by the SignatureCertChainUrl header value on the request to ensure that it matches the format used by Amazon.
            $normalizedURL = URLHelper::normalizeURL($validate['HTTP_SIGNATURECERTCHAINURL']);
            foreach ($Settings->acceptedSignatures->data() as $acceptedSignature) {
                foreach ($acceptedSignature as $type => $pattern) {
                    $optional = (substr($type, 0, 1) == '?');
                    if ($optional)
                        $type = substr($type, 1);

                    $parsed = URLHelper::parseURL($normalizedURL, $type, false);
                    if (!$parsed && !$optional || $parsed && !preg_match('#' . $pattern . '#', $parsed))
                        throw new SignatureException(401, $pattern . ' != ' . $parsed);
                };
            };


            $certFile = $normalizedURL;
            if (DEBUG)
                $certFile = '/dev/amazon_request/echo-api-cert-4.pem';

            // Download the PEM-encoded X.509 certificate chain that Alexa used to sign the message as specified by the SignatureCertChainUrl header value on the request.
            if (!FileHelper::fileExists($certFile))
                throw new SignatureException(401, $certFile . " does not exist");

            $certContent = FileHelper::getFileContents($certFile, null);
            if (!trim($certContent))
                throw new SignatureException(400, "certificate-file empty");


            // The signing certificate has not expired (examine both the Not Before and Not After dates)
            // The domain echo-api.amazon.com is present in the Subject Alternative Names (SANs) section of the signing certificate
            $certDetails = openssl_x509_parse($certContent);
            if (!$certDetails || !count($certDetails))
                throw new SignatureException(400, "certificate invalid");

            $time = time();
            if (!ArrayHelper::areKeysSet(['validFrom_time_t', 'validTo_time_t', 'extensions'], $certDetails))
                throw new SignatureException(400, "certificate invalid");
            else if ($time < $certDetails['validFrom_time_t'] || $time > $certDetails['validTo_time_t'])
                throw new SignatureException(400, "certificate has expired");
            else if (!isset($certDetails['extensions']['subjectAltName']))
                throw new SignatureException(400, "no Subject Alternative Name in certificate provided");


            // TODO [marcel, 23.02.2017]: check chain of trust up to the root CA
            var_dump(openssl_x509_checkpurpose($certContent, X509_PURPOSE_SSL_CLIENT)); print ' in ' . __FILE__ . '::' . __LINE__ . PHP_EOL . PHP_EOL;
            var_dump(openssl_x509_checkpurpose($certContent, X509_PURPOSE_SSL_SERVER)); print ' in ' . __FILE__ . '::' . __LINE__ . PHP_EOL . PHP_EOL;
            var_dump(openssl_x509_checkpurpose($certContent, X509_PURPOSE_NS_SSL_SERVER)); print ' in ' . __FILE__ . '::' . __LINE__ . PHP_EOL . PHP_EOL;
            var_dump(openssl_x509_checkpurpose($certContent, X509_PURPOSE_SMIME_SIGN)); print ' in ' . __FILE__ . '::' . __LINE__ . PHP_EOL . PHP_EOL;
            var_dump(openssl_x509_checkpurpose($certContent, X509_PURPOSE_SMIME_ENCRYPT)); print ' in ' . __FILE__ . '::' . __LINE__ . PHP_EOL . PHP_EOL;
            var_dump(openssl_x509_checkpurpose($certContent, X509_PURPOSE_CRL_SIGN)); print ' in ' . __FILE__ . '::' . __LINE__ . PHP_EOL . PHP_EOL;
            var_dump(openssl_x509_checkpurpose($certContent, X509_PURPOSE_ANY)); print ' in ' . __FILE__ . '::' . __LINE__ . PHP_EOL . PHP_EOL;

            $anyValidName = false;
            foreach (array_keys($Settings->acceptedSignatures->data()) as $signatureURL) {
                if (strpos($certDetails['extensions']['subjectAltName'], $signatureURL) !== false) {
                    $anyValidName = true;
                    break;
                }
            };
            if (!$anyValidName)
                throw new SignatureException(400, "Subject Alternative Name '" . $certDetails['extensions']['subjectAltName'] . "' is not valid");

            $time = time();
            if ($time < $certDetails['validFrom_time_t'] || $time > $certDetails['validTo_time_t'])
                throw new SignatureException(400, "certificate has expired");

            $certPublicKey = openssl_pkey_get_public($certContent);
            if (!$certPublicKey)
                throw new SignatureException(401, "certificate is not a valid one");

            $signature = base64_decode($validate['HTTP_SIGNATURE']);

            $certValid = openssl_verify($certContent, $signature, $certPublicKey);

            var_dump($certContent, $certPublicKey, $signature, $certValid); print ' in ' . __FILE__ . '::' . __LINE__ . PHP_EOL . PHP_EOL;

            exit;

            $input = array_merge(self::$BASE_REQUEST, json_decode($input, true));

            ArrayHelper::validateArrayScheme(self::$REQUEST_SCHEME, $input);

            LocalizationHelper::validateLocale($input['request']['locale']);

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
            try {
                SettingsHelper::parseConfig(FileHelper::getRelativePath(__DIR__ . '/../config/') . "default.json");
                SettingsHelper::parseConfig((isset($configFile)) ? $configFile : '/config/alexa.json');

                $AlexaRequest  = new \MashCoding\AlexaPHPFramework\Request((defined('DEBUG') && DEBUG) ? $stdinOverride : null);
                $AlexaResponse = \MashCoding\AlexaPHPFramework\Response::fromRequest($AlexaRequest);

                try {
                    $AlexaResponse->fetch();
                } catch (ResponseException $e) {

                    if ($e->getCode() == ResponseException::CODE_REPROMT)
                        $AlexaResponse->ask($e->sayMessage());
                    else
                        $AlexaResponse->respond($e->sayMessage());

                }
            } catch (SignatureException $e) {
                header('HTTP/1.1 ' . $e->getCode() . ' ' . $e->errorMessage());

                if (DEBUG) {
                    var_dump($e); print ' in ' . __FILE__ . '::' . __LINE__ . PHP_EOL . PHP_EOL;
                }

                exit;
            } catch (\Exception $e) {
                // catch all remaining exceptions
                $AlexaResponse = Response::defaultResponse();
                $STDIN = "";
                if (defined('DEBUG') && DEBUG) {
                    $AlexaResponse->respond($e->getMessage());
                    $STDIN = DataHandler::getDataObject()->stdin;
                }

                $AlexaResponse->appendCard(Card::TYPE_SIMPLE)
                    ->setTitle("Exception #" . $e->getCode())
                    ->setText($e->getMessage() . ((defined('DEBUG') && DEBUG) ? PHP_EOL . 'Request:' . PHP_EOL . $STDIN : ''));
            }

            header('Content-Type: application/json; charset=UTF-8');
            echo (isset($AlexaResponse)) ? $AlexaResponse->json() : '{}';
        }

        public function __construct ($stdin = null)
        {
            if (!isset($stdin) || empty($stdin))
                $stdin = file_get_contents('php://input');

            DataHandler::getDataObject()->stdin = $stdin;
            if ($stdin && self::validateRequest($stdin))
                parent::__construct($stdin);
        }
    }