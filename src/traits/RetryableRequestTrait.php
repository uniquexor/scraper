<?php
    namespace unique\scraper\traits;

    use GuzzleHttp\ClientInterface;
    use GuzzleHttp\Exception\ConnectException;

    trait RetryableRequestTrait {

        /**
         * When scraping, if a ConnectException is thrown, retry this amount of times.
         * @var int
         */
        public int $on_connect_exception_retry = 3;

        /**
         * Number of seconds to wait before retrying a request if it had failed.
         * @var int
         */
        public int $on_connect_exception_retry_timeout = 5;

        /**
         * Try making a request. If a ConnectException is thrown, retry request for a specified amount of times.
         * To control how many times to retry and how much seconds to wait in between every retry, configure:
         * {@see $on_connect_exception_retry} and {@see $on_connect_exception_retry_timeout}
         *
         * @param ClientInterface $transport
         * @param string $method - HTTP Method, e.g. 'GET', 'POST', etc...
         * @param \Psr\Http\Message\UriInterface|string $url - Request URL
         * @param array $options - Options to be passed to the client.
         * @return \Psr\Http\Message\ResponseInterface
         * @throws \GuzzleHttp\Exception\GuzzleException
         */
        protected function retryRequest( ClientInterface $transport, string $method, $url, $options = [] ) {

            $exception = null;
            $retry = 0;

            do {

                try {

                    return $transport->request( $method, $url, $options );
                } catch ( ConnectException $exception ) {

                    sleep( $this->on_connect_exception_retry_timeout );
                    $retry++;
                }
            } while ( $exception !== null && $retry <= $this->on_connect_exception_retry );

            throw $exception;
        }
    }