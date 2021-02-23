<?php
    namespace unique\scraper\exceptions;

    use GuzzleHttp\Psr7\Response;
    use unique\scraper\AbstractJsonItemDownloader;

    /**
     * Class BadJsonFileException.
     * Exception thrown, when {@see AbstractJsonItemDownloader} was not able to convert response body in to JSON.
     * Contains the aformentioned response object, that can be retrieved using {@see getResponse()} method.
     *
     * @package unique\scraper\exceptions
     */
    class BadJsonFileException extends \Exception {

        /**
         * Depending on the implementation contains a response object, that caused the exception.
         * If standart implementation is used, will contain {@see \GuzzleHttp\Psr7\Response} object.
         * @var Response|mixed|null
         */
        protected $response;

        /**
         * Creates a new exception, assigning a causing response object.
         * @param Response|mixed|null $response - Response object that caused the exception.
         * @return BadJsonFileException
         */
        public static function createFromResponse( $response ) {

            $exception = new self();
            $exception->response = $response;

            return $exception;
        }

        /**
         * Returns a response object, that caused the exception.
         * @return Response|mixed|null
         */
        public function getResponse() {

            return $this->response;
        }
    }