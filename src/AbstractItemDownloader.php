<?php
    namespace unique\scraper;

    use GuzzleHttp\Client;
    use GuzzleHttp\ClientInterface;
    use GuzzleHttp\Exception\ConnectException;
    use Symfony\Component\DomCrawler\Crawler;
    use unique\scraper\interfaces\ScrapableInterface;
    use unique\scraper\interfaces\SiteItemInterface;
    use unique\scraper\traits\RetryableRequestTrait;

    abstract class AbstractItemDownloader implements ScrapableInterface {

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

            $this->assignItemData( $this->createCrawlerFromUrl( $this->url ) );
        }

        /**
         * Downloads the url and creates a {@see Crawler} from it's contents.
         * @param string $url - The url to be downloaded
         * @return Crawler
         * @throws \GuzzleHttp\Exception\GuzzleException
         */
        protected function createCrawlerFromUrl( string $url ): Crawler {

            $response = $this->retryRequest( $this->transport, 'GET', $url );
            return new Crawler( (string) $response->getBody() );
        }

        /**
         * Returns an item, that was created from the scraped data.
         * @return SiteItemInterface
         */
        public function getItem(): SiteItemInterface {

            return $this->item;
        }

        /**
         * Assigns the data from the website to {@see $item}
         * @param Crawler $doc - Website contents
         */
        abstract protected function assignItemData( Crawler $doc );
    }