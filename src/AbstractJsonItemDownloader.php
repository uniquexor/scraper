<?php
    namespace unique\scraper;

    use GuzzleHttp\ClientInterface;
    use unique\scraper\exceptions\BadJsonFileException;
    use unique\scraper\interfaces\ScrapableInterface;
    use unique\scraper\interfaces\SiteItemInterface;
    use unique\scraper\traits\RetryableRequestTrait;

    abstract class AbstractJsonItemDownloader implements ScrapableInterface{

        use RetryableRequestTrait;

        /**
         * The url of the item being scraped.
         * @var string
         */
        protected $url;

        /**
         * The ID of the item, being scraped.
         * @var string
         */
        protected $id;

        /**
         * The item being created from the scraped data.
         * @var SiteItemInterface
         */
        protected $item;

        /**
         * The transport to be used for requests.
         * @var ClientInterface
         */
        protected $transport;

        /**
         * AbstractItemDownloader constructor.
         * @param string $url - The url of the item being scraped.
         * @param string $id - The ID of the item, being scraped.
         * @param ClientInterface $transport - Transport to be used for requests.
         * @param SiteItemInterface $site_item - The item being created from the scraped data.
         */
        public function __construct( $url, string $id, ClientInterface $transport, SiteItemInterface $site_item ) {

            $this->url = $url;
            $this->id = $id;
            $this->transport = $transport;
            $this->item = $site_item;
        }

        /**
         * @inheritdoc
         */
        public function scrape() {

            $this->assignItemData( $this->createJsonFromUrl( $this->url ) );
        }

        /**
         * Downloads the url and creates a JSON array from it's contents.
         * @param string $url - The url to be downloaded
         * @return array
         * @throws \GuzzleHttp\Exception\GuzzleException|BadJsonFileException
         */
        protected function createJsonFromUrl( string $url ): array {

            $response = $this->retryRequest( $this->transport, 'GET', $url );
            $json = json_decode( (string)$response->getBody(), true );

            if ( $json === null ) {

                throw BadJsonFileException::createFromResponse( $response );
            }

            return $json;
        }

        /**
         * Returns an item, that was created from the scraped data.
         * @return SiteItemInterface
         */
        public function getItem(): SiteItemInterface {

            return $this->item;
        }

        /**
         * Assigns the data from the url to {@see $item}
         * @param array $json - JSON data received from the url.
         */
        abstract protected function assignItemData( array $json );
    }