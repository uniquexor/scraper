<?php
    namespace app\components;

    /**
     * Class RequestHelper.
     * Provides an easy way to generate headers for a request with default data.
     * @package app\components
     */
    class RequestHelper {

        protected $host;

        protected $headers = [];

        public function __construct( $host ) {

            $this->host = $host;
        }

        public function addAccept( $value = '*/*' ) {

            $this->headers['Accept'] = $value;
            return $this;
        }

        public function addAcceptAsHtml() {

            return $this->addAccept( 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9' );
        }

        public function addAcceptEncoding( $value = 'gzip, deflate, br' ) {

            $this->headers['Accept-Encoding'] = $value;
            return $this;
        }

        public function addAcceptLanguage( $value = 'en-US,en;q=0.9,lt-LT;q=0.8,lt;q=0.7' ) {

            $this->headers['Accept-Language'] = $value;
            return $this;
        }

        public function addConnection( $value = 'keep-alive' ) {

            $this->headers['Connection'] = $value;
            return $this;
        }

        public function addCacheControl( $value = 'no-cache' ) {

            $this->headers['Cache-Control'] = $value;
            $this->headers['Pragma'] = $value;
            return $this;
        }

        public function addContentType( $value = 'application/x-www-form-urlencoded' ) {

            $this->headers['Content-Type'] = $value;
            return $this;
        }

        public function addHost() {

            $this->headers['Host'] = $this->host;
            $this->headers['Origin'] = 'https://' . $this->host;
            return $this;
        }

        public function addSec(
            $ua = '"Chromium";v="88", "Google Chrome";v="88", ";Not A Brand";v="99"',
            $ua_mobile = '?0',
            $document = 'document',
            $mode = 'navigate',
            $site = 'same-origin',
            $user = '?1'
        ) {

            $headers = [
                'sec-ch-ua' => $ua,
                'sec-ch-ua-mobile' => $ua_mobile,
                'Sec-Fetch-Dest' => $document,
                'Sec-Fetch-Mode' => $mode,
                'Sec-Fetch-Site' => $site,
                'Sec-Fetch-User' => $user,
            ];

            $headers = array_filter( $headers );
            $this->headers = array_merge( $this->headers, $headers );

            return $this;
        }

        public function addUpgradeInsecureRequests( $value = '1' ) {

            $this->headers['Upgrade-Insecure-Requests'] = $value;
            return $this;
        }

        public function addUserAgent( $value = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/88.0.4324.104 Safari/537.36' ) {

            $this->headers['User-Agent'] = $value;
            return $this;
        }

        public static function createDefaultHtmlRequest( $host ) {

            return ( new self( $host ) )
                ->addAcceptAsHtml()
                ->addAcceptEncoding()
                ->addAcceptLanguage()
                ->addCacheControl()
                ->addConnection()
                ->addContentType()
                ->addHost()
                ->addSec()
                ->addUpgradeInsecureRequests()
                ->addUserAgent();

        }

        public function toArray() {

            return $this->headers;
        }
    }