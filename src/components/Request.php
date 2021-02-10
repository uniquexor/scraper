<?php
    namespace unique\scraper\components;

    /**
     * A class for consolidating request options to be passed as an object.
     *
     * @package unique\scraper\components
     */
    class Request {

        protected string $method = 'GET';

        protected string $uri;

        protected array $options = [];

        /**
         * Request constructor.
         * @param string $uri
         * @param string $method
         * @param array $options
         */
        public function __construct( string $uri, string $method = 'GET', array $options = [] ) {

            $this->uri = $uri;
            $this->method = $method;
            $this->options = $options;
        }

        /**
         * @return string
         */
        public function getMethod(): string {

            return $this->method;
        }

        /**
         * @return string
         */
        public function getUri(): string {

            return $this->uri;
        }

        /**
         * @return array
         */
        public function getOptions(): array {

            return $this->options;
        }

        public function __toString() {

            return $this->getUri();
        }
    }