<?php
    namespace MashCoding\AlexaPHPFramework\helper;

    class DOMHelper
    {
        /**
         * @param      $id
         * @param null $source
         *
         * @return \DOMNode|null
         */
        public static function getElementById ($id, $source = null)
        {
            $DOMXPath = self::getDOMBySource($source);
            $nodes = $DOMXPath->query('//*[@id="' . $id . '"]');
            return ($nodes->length) ? $nodes->item(0) : null;
        }

        /**
         * @param      $tag
         * @param null $source
         *
         * @return \DOMNodeList|null
         */
        public static function getElementsByTag ($tag, $source = null)
        {
            $DOMXPath = self::getDOMBySource($source);
            $nodes = $DOMXPath->query('//' . $tag);
            return ($nodes->length) ? $nodes : null;
        }

        /**
         * @param $source
         *
         * @return \DOMXPath
         */
        private static function getDOMBySource ($source)
        {
            return (isset($source) && $source instanceof \DOMNode) ? self::getDOMFromNode($source) : ((URLHelper::isValidURL($source)) ? self::getDOMFromURL($source) : self::getDOMByHtml($source));
        }

        /**
         * @param \DOMNode $Node
         *
         * @return string
         */
        public static function getInnerHtml (\DOMNode $Node)
        {
            return implode(array_map([$Node->ownerDocument, "saveXML"], iterator_to_array($Node->childNodes)));
        }

        /**
         * @param null $url
         *
         * @return \DOMXPath
         */
        private static function getDOMFromURL ($url = null)
        {
            $loadedHtml = DataHandler::getDataObject()->loadedHtml;

            if (isset($url)) {
                $html = null;
                foreach ($loadedHtml->data() as $loaded) {
                    if ($loaded['url'] == $url) {
                        $html = $loaded['html'];
                        break;
                    }
                };

                if (!isset($html)) {
                    $html = FileHelper::getFileContents($url, "");

                    $loadedHtml->setData(count($loadedHtml->data()), [
                        "url" => $url,
                        "html" => $html,
                    ]);
                }

            } else if (!isset($url) && isset($loadedHtml) && $loadedHtml->hasProperties())
                $html = $loadedHtml[count($loadedHtml->data()) - 1]->html;
            else
                $html = "";

            return self::getDOMByHtml($html);
        }

        /**
         * @param \DOMNode $Node
         *
         * @return \DOMXPath
         */
        private static function getDOMFromNode (\DOMNode $Node)
        {
            return self::getDOMByHtml(self::getInnerHtml($Node));
        }

        /**
         * @param $html
         *
         * @return \DOMXPath
         */
        private static function getDOMByHtml ($html) {
            libxml_use_internal_errors(true);
            $DOM = new \DOMDocument();
            $DOM->loadHTML($html);
            libxml_clear_errors();

            return new \DOMXPath($DOM);
        }
    }