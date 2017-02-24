<?php
    namespace MashCoding\AlexaPHPFramework\helper;

    use MashCoding\AlexaPHPFramework\exceptions\CertificateException;
    use phpseclib\File\X509;
    use SebastianBergmann\CodeCoverage\Report\Html\File;

    class CertHelper
    {
        const SHA1_BYTES = '3021300906052b0e03021a05000414';

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

        protected static function getCertificatesChainOfTrust ($chainOfTrust)
        {
            preg_match_all('/(?<=-----BEGIN CERTIFICATE-----)(?:\S+|\s(?!-----END CERTIFICATE-----))+(?=\s-----END CERTIFICATE-----)/', $chainOfTrust, $certs);
            return array_map(function ($cert) { return "-----BEGIN CERTIFICATE-----\n" . trim($cert) . "\n-----END CERTIFICATE-----"; }, $certs[0]);
        }

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

        protected static function getSubjectAlternativeNames (X509 $X509, $nameOnly = false)
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

        protected static function compareCertificateSignatures ($cert1Content, $cert2Content)
        {
            $X509 = new X509();
            $cert1 = $X509->loadX509($cert1Content);
            $cert2 = $X509->loadX509($cert2Content);
            return $cert1['signature'] === $cert2['signature'];
        }

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

        public static function getLoadedCertificates ()
        {
            return DataHandler::getDataObject()->certs;
        }

        public static function checkCertificate ($certFile, $signature = null)
        {
            $Certificates = self::getLoadedCertificates();
            $Certificates->push(basename($certFile));

            $certContent = self::getCertificate($certFile);
            return self::verifyCertificate($certContent);
        }
    }