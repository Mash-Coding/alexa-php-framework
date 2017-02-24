<?php
    namespace MashCoding\AlexaPHPFramework\helper;

    use MashCoding\AlexaPHPFramework\exceptions\CertificateException;

    class CertHelper
    {
        public static function getCertificate ($certFile)
        {
            // Download the PEM-encoded X.509 certificate chain that Alexa used to sign the message as specified by the SignatureCertChainUrl header value on the request.
            $certContent = FileHelper::getFileContents($certFile, null);
            if (!$certContent)
                throw new CertificateException("'" . $certFile . "' does not exist");
            else if (!trim($certContent))
                throw new CertificateException(basename($certFile) . " is empty");

            return $certContent;
        }

        public static function validateBasicCertificateDetails ($cert)
        {
            // The signing certificate has not expired (examine both the Not Before and Not After dates)
            // The domain echo-api.amazon.com is present in the Subject Alternative Names (SANs) section of the signing certificate
            $cert = openssl_x509_parse($cert);
            if (!$cert || !count($cert))
                throw new CertificateException("certificate invalid");

            $time = time();
            if (!ArrayHelper::areKeysSet(['validFrom_time_t', 'validTo_time_t', 'extensions'], $cert))
                throw new CertificateException("certificate invalid");
            else if ($time < $cert['validFrom_time_t'] || $time > $cert['validTo_time_t'])
                throw new CertificateException("certificate has expired");
            else if (!isset($cert['extensions']['subjectAltName']))
                throw new CertificateException("no Subject Alternative Name in certificate provided");

            return $cert;
        }

        protected static function isRootCA (array $certDetails)
        {
            return $certDetails['issuer'] === ArrayHelper::getFilteredArray(array_keys($certDetails['issuer']), $certDetails['subject']);
        }

        protected static function getCertificatesChainOfTrust ($chainOfTrust)
        {
            preg_match_all('/(?<=-----BEGIN CERTIFICATE-----)(?:\S+|\s(?!-----END CERTIFICATE-----))+(?=\s-----END CERTIFICATE-----)/', $chainOfTrust, $certs);
            return array_map(function ($cert) { return "-----BEGIN CERTIFICATE-----\n" . trim($cert) . "\n-----END CERTIFICATE-----"; }, $certs[0]);
        }

        public static function validateCertificate ($certContent, array $certDetails)
        {
            global $validate;
            var_dump($certDetails); print ' in ' . __FILE__ . '::' . __LINE__ . PHP_EOL . PHP_EOL;

            $time = time();
            if ($time < $certDetails['validFrom_time_t'] || $time > $certDetails['validTo_time_t'])
                throw new CertificateException("certificate has expired");

            $certPublicKey = openssl_pkey_get_public($certContent);
            if (!$certPublicKey)
                throw new CertificateException("certificate is not a valid one");

            $signature = base64_decode($validate['HTTP_SIGNATURE']);

            $certValid = openssl_verify($certContent, $signature, $certPublicKey);

            var_dump($signature, $certValid); print ' in ' . __FILE__ . '::' . __LINE__ . PHP_EOL . PHP_EOL;
            
            return true;
        }

        public static function verifyCertificate ($certContent)
        {
            $certs = self::getCertificatesChainOfTrust($certContent);
            foreach ($certs as $pos => &$cert) {
                $raw = $cert;
                $cert = self::validateBasicCertificateDetails($cert);

                if (!self::validateCertificate($raw, $cert))
                    throw new CertificateException("invalid " . (($pos == 0) ? 'initial' : 'intermediate') . " certificate provided");

                if (self::isRootCA($cert) && $pos !== count($certs)-1)
                    throw new CertificateException("invalid chain of trust");
            };

            return $certs;
        }

        public static function checkCertificate ($certFile, $signature = null)
        {
            $certContent = self::getCertificate($certFile);

            return self::verifyCertificate($certContent);
        }
    }