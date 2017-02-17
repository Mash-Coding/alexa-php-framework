<?php
    namespace MashCoding\AlexaPHPFramework\helper;

    class DataHandler extends JSONObject
    {
        /**
         * initializes or gets the global DateHandler object
         * @return DataHandler
         */
        public static function getDataObject ()
        {
            global $__PARSED_ALEXA_DATA__;

            if (!isset($__PARSED_ALEXA_DATA__))
                $__PARSED_ALEXA_DATA__ = new DataHandler([], 'DataHandler');

            return $__PARSED_ALEXA_DATA__;
        }
    }