<?php
    namespace MashCoding\AlexaPHPFramework\helper;

    use MashCoding\AlexaPHPFramework\exceptions\CertificateException;
    use phpseclib\File\X509;
    use SebastianBergmann\CodeCoverage\Report\Html\File;

    class CertHelper
    {
        const SHA1_BYTES = '3021300906052b0e03021a05000414';

        /**
         * returns the content of given $certFile (only checking if it exists and whether its empty)
         *
         * @param $certFile
         *
         * @return null|string
         * @throws CertificateException
         */
        public static function getCertificate ($certFile)
        {
            // Download the PEM-encoded X.509 certificate chain that Alexa used to sign the message as specified by the SignatureCertChainUrl header value on the request.
            $certContent = FileHelper::getFileContents($certFile, null);
            if (!$certContent)
                throw new CertificateException("'" . $certFile . "' does not exist");
            else if (!trim($certContent))
                throw new CertificateException(basename($certFile) . " is empty");

            if (DOWNLOAD_CERTS) {
                $Settings = SettingsHelper::getConfig();
                FileHelper::writeContentsToFile($Settings->path->cache . FileHelper::getFileName($certFile), $certContent);
            }

            return $certContent;
        }

        /**
         * splits given certificate content and splits it into an array of single certificates
         *
         * @param $chainOfTrust
         *
         * @return array
         */
        protected static function getCertificatesChainOfTrust ($chainOfTrust)
        {
            preg_match_all('/(?<=-----BEGIN CERTIFICATE-----)(?:\S+|\s(?!-----END CERTIFICATE-----))+(?=\s-----END CERTIFICATE-----)/', $chainOfTrust, $certs);
            return array_map(function ($cert) { return "-----BEGIN CERTIFICATE-----\n" . trim($cert) . "\n-----END CERTIFICATE-----"; }, $certs[0]);
        }

        /**
         * this method tries to retrieve the parent certificate by given X.509 certificate.
         * null is returned when no parent certificate is found.
         *
         * @param X509 $X509
         *
         * @return string|null
         */
        protected static function getImmediateCertificate (X509 $X509)
        {
            $extension = $X509->getExtension('id-pe-authorityInfoAccess');
            if (isset($extension)) {
                foreach ($extension as $extnValue) {
                    if ($extnValue['accessMethod'] == 'id-ad-caIssuers')
                        return $extnValue['accessLocation']['uniformResourceIdentifier'];
                };
            }
            return null;
        }

        /**
         * gets an array containing all alternative domains of subject in given X.509 certificate
         *
         * @param X509 $X509
         *
         * @return array
         */
        protected static function getSubjectAlternativeNames (X509 $X509)
        {
            $names = [];
            $extension = $X509->getExtension('id-ce-subjectAltName');
            if (isset($extension)) {
                foreach ($extension as $extnValue) {
                    if (isset($extnValue['dNSName']))
                        $names[] = $extnValue['dNSName'];
                };
            }

            return $names;
        }

        /**
         * compares the signatures of the two given certificate strings
         *
         * @param $cert1Content
         * @param $cert2Content
         *
         * @return bool
         */
        protected static function compareCertificateSignatures ($cert1Content, $cert2Content)
        {
            $X509 = new X509();
            $cert1 = $X509->loadX509($cert1Content);
            $cert2 = $X509->loadX509($cert2Content);
            return $cert1['signature'] === $cert2['signature'];
        }

        /**
         * verifies a given certificate string by splitting it into single certificates (if its a chained certificate)
         * and then iterates through all certificates and validates for each of it:
         *  - has the certificate expired?
         *  - does one of certificates subject alternative names match the acceptedSignature URLs from the config?
         *  - is it the last certificate of the chain and thus has no parent certificate?
         *  - if it's not the last one and it has a parent certificate
         *  - does the next in line certificate match the defined parent certificate (compared by signature)?
         *  - is the certificate valid in general?
         *
         * @param $certContent
         *
         * @return array containing the validated X509 objects
         * @throws CertificateException
         */
        public static function verifyCertificate ($certContent)
        {
            $Settings = SettingsHelper::getConfig();
            $Certificates = self::getLoadedCertificates();

            $certs = self::getCertificatesChainOfTrust($certContent);
            $X509 = new X509();
            foreach ($certs as $pos => $certData) {
                $cert = $X509->loadX509($certData);

                // The signing certificate has not expired (examine both the Not Before and Not After dates)
                if (!$X509->validateDate())
                    throw new CertificateException("certificate has expired");

                // The domain echo-api.amazon.com is present in the Subject Alternative Names (SANs) section of the signing certificate
                if ($pos == 0) {
                    $anyValidName = false;
                    $subjectAltNames = self::getSubjectAlternativeNames($X509);
                    foreach (array_keys($Settings->acceptedSignatures->data()) as $signatureURL) {
                        if (in_array($signatureURL, $subjectAltNames)) {
                            $anyValidName = true;
                            break;
                        }
                    };
                    if (!$anyValidName)
                        throw new CertificateException("Subject Alternative Names '" . implode(', ', $subjectAltNames) . "' of '" . $Certificates->getProperty($pos) . "' are not valid");
                }

                // All certificates in the chain combine to create a chain of trust to a trusted root CA certificate
                $caCertFile = self::getImmediateCertificate($X509);
                if ($pos < count($certs)-1) {
                    if (!$caCertFile)
                        throw new CertificateException("certificate '" . $Certificates->getProperty($pos) . "' is required to have immediate certificate");

                    $caCert = self::getCertificate((DEBUG) ? '/dev/amazon_request/' . FileHelper::getFileName($caCertFile) : $caCertFile);
                    if (!$caCert)
                        throw new CertificateException("invalid certificate signer '" . FileHelper::getFileName($caCertFile) . "'");
                    else if (isset($certs[$pos + 1]) && !self::compareCertificateSignatures($caCert, $certs[$pos + 1]))
                        throw new CertificateException("immediate certificate '" . FileHelper::getFileName($caCertFile) . "' does not fit in chain of trust");

                    $X509->loadCA($caCert);

                    if (!$X509->validateSignature())
                        throw new CertificateException("certificate '" . $Certificates->getProperty($pos) . "' has invalid signature");

                    $Certificates->push(FileHelper::getFileName(self::getImmediateCertificate($X509)));
                }
                $certs[$pos] = clone $X509;
            };

            return $certs;
        }

        /**
         * @return JSONObject
         */
        public static function getLoadedCertificates ()
        {
            return DataHandler::getDataObject()->certs;
        }

        /**
         * initialized the verification process for given $certFile
         *
         * @param      $certFile
         *
         * @return array
         * @throws CertificateException
         * @see CertHelper::verifyCertificate()
         */
        public static function checkCertificate ($certFile)
        {
            $Certificates = self::getLoadedCertificates();
            $Certificates->push(basename($certFile));

            $certContent = self::getCertificate($certFile);
            return self::verifyCertificate($certContent);
        }
    }