<?php
    namespace MashCoding\AlexaPHPFramework\helper;

    class JSONObject
    {
        protected $__data = [];
        protected $__name = "";

        /**
         * @var null|JSONObject
         */
        protected $__parent = null;

        public function __get ($name)
        {
            $value = null;
            if (array_key_exists($name, $this->__data))
                $value = $this->__data[$name];

            if (is_array($value))
                $value = new JSONObject($value, $name, $this);

            return $value;
        }

        public function __set ($name, $value)
        {
            $this->setData($name, $value);

            if (isset($this->__parent) && $this->__parent instanceof JSONObject)
                $this->__parent->setData($this->__name, $this->__data);

            return $this;
        }

        public function setData ($name, $value)
        {
            if (array_key_exists($name, $this->__data))
                $this->__data[$name] = $value;
        }

        public function json ()
        {
            return json_encode($this->__data);
        }

        public function __construct (array $data, $name = null, $parent = null)
        {
            $this->__data = $data;

            if (isset($name) && isset($parent) && $parent instanceof JSONObject) {
                $this->__name = $name;
                $this->__parent = $parent;
            }
        }
    }