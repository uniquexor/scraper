<?php
    namespace unique\scraper;

    use Symfony\Component\DomCrawler\Crawler;
    use unique\scraper\interfaces\SiteItemInterface;

    abstract class AbstractItemDownloader {

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
         * The list downloader used to get the item.
         * @var AbstractItemListDownloader
         */
        protected $list_downloader;

        /**
         * AbstractItemDownloader constructor.
         * @param string $url - The url of the item being scraped.
         * @param string $id - The ID of the item, being scraped.
         * @param AbstractItemListDownloader $list_downloader - The list downloader used to get the item.
         * @param SiteItemInterface $site_item - The item being created from the scraped data.
         */
        public function __construct( $url, string $id, AbstractItemListDownloader $list_downloader, SiteItemInterface $site_item ) {

            $this->url = $url;
            $this->id = $id;
            $this->list_downloader = $list_downloader;
            $this->item = $site_item;

            $this->assignItemData( $this->createCrawlerFromUrl( $url ) );
        }

        /**
         * Downloads the url and creates a {@see Crawler} from it's contents.
         * @param string $url - The url to be downloaded
         * @return Crawler
         * @throws \GuzzleHttp\Exception\GuzzleException
         */
        protected function createCrawlerFromUrl( string $url ): Crawler {

            $response = $this->list_downloader->getTransport()->request( 'GET', $url );
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