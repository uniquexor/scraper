<?php
    namespace unique\scraper;

    use GuzzleHttp\ClientInterface;
    use Symfony\Component\DomCrawler\Crawler;
    use unique\events\interfaces\EventHandlingInterface;
    use unique\events\traits\EventTrait;
    use unique\scraper\events\BreakListEvent;
    use unique\scraper\events\ItemBeginEvent;
    use unique\scraper\events\ItemEndEvent;
    use unique\scraper\events\ItemMissingUrlEvent;
    use unique\scraper\events\ListBeginEvent;
    use unique\scraper\events\ListEndEvent;
    use unique\scraper\interfaces\LogContainerInterface;
    use unique\scraper\interfaces\ScrapableInterface;

    abstract class AbstractItemListDownloader implements EventHandlingInterface, ScrapableInterface {

        use EventTrait;

        const STATE_OK = 0;
        const STATE_SKIP = 1;
        const STATE_FAIL = 2;
        const STATE_MISSING_DATA = 3;

        // Event triggered before starting to scrape a listing page
        const EVENT_ON_LIST_BEGIN = 'on_page_begin';

        // Event triggered after the listing page has been scraped.
        const EVENT_ON_LIST_END = 'on_list_end';

        // Event triggered before scraping an item of the listing page.
        const EVENT_ON_ITEM_BEGIN = 'on_item_begin';

        // Event triggered after scraping an item of the listing page.
        const EVENT_ON_ITEM_END = 'on_item_end';

        // Event triggered, when EVENT_ON_PAGE_BEGIN or EVENT_ON_ITEM_BEGIN instructs to abort scraping.
        const EVENT_ON_BREAK_LIST = 'on_break_list';

        // Event triggered when getItemUrl() returned null.
        const EVENT_ON_ITEM_MISSING_URL = 'on_item_missing_url';

        /**
         * Stores information about items: total number, number in a page and the current page number.
         * @var ItemCount
         */
        protected ItemCount $item_count;

        /**
         * @var LogContainerInterface|LogContainer
         */
        protected LogContainerInterface $log_container;

        /**
         * Specifies a class name, that the scraped items should be creating.
         * This class must implement {@see SiteItemInterface}.
         * @var string
         */
        protected $class_site_item;

        /**
         * The transport object that the scraper will use.
         * Can either be a GuzzleHttp\Client or a `unique\proxyswitcher` component, if you want an easy way of switching proxies.
         * @var ClientInterface
         */
        protected $transport;

        /**
         * AbstractItemListDownloader constructor.
         * The `$class_site_item` specifies a class name, that the scraped items should be creating. This class must implement {@see SiteItemInterface}.
         * For `$transport` you can either use a GuzzleHttp\Client or a `unique\proxyswitcher` component, if you want an easy way of switching proxies.
         *
         * @param string $class_site_item - The class name that the scraper will use to create objects of, when handling items.
         * @param ClientInterface $transport - The transport object that the scraper will use.
         * @param LogContainer|null $log_container
         */
        public function __construct( string $class_site_item, ClientInterface $transport, LogContainer $log_container = null ) {

            $this->class_site_item = $class_site_item;
            $this->transport = $transport;

            if ( $log_container === null ) {

                $log_container = new LogContainer();
            }

            $this->setLogContainer( $log_container );
        }

        /**
         * Sets a log container.
         * @param LogContainerInterface $log_container
         */
        public function setLogContainer( LogContainerInterface $log_container ) {

            $this->log_container = $log_container;
        }

        /**
         * Returns the transport object that is currently used to download the files.
         * @return ClientInterface
         */
        public function getTransport(): ClientInterface {

            return $this->transport;
        }

        /**
         * Downloads a single list page, iterates through all items in it and handles them.
         * Returns a boolean, indicating if there are more list pages to be downloaded.
         * @param int $page_num - The number of list page.
         * @return bool
         * @throws \Throwable
         */
        protected function downloadPage( int $page_num = 1 ): bool {

            $url = $this->getListUrl( $page_num );

            try {

                $response = $this->transport->request( 'GET', $url );
            } catch ( \Throwable $exception ) {

                $this->log_container->logException( $exception, $url );
                throw $exception;
            }

            $doc = new Crawler( (string) $response->getBody() );
            $continue = false;

            $this->item_count->setTotalItems( $this->getTotalItems( $doc ) );
            $this->item_count->setNumberOfItemsInPage( $this->getNumberOfItemsInPage( $doc ) );

            foreach ( $this->getItems( $doc ) as $item ) {

                /**
                 * @var \DOMElement $item
                 */

                $continue = true;
                $url = $this->getItemUrl( $item );
                if ( $url === null ) {

                    $event = new ItemMissingUrlEvent( $item );
                    $this->trigger( self::EVENT_ON_ITEM_MISSING_URL, $event );

                    $url = $event->getUrl();
                    if ( $url === null ) {

                        $this->log_container->logItemBegin( null, null );
                        $this->log_container->logItemEnd( null, self::STATE_MISSING_DATA );
                        continue;
                    }
                }

                $id = $this->getItemId( $url, $item );

                $this->log_container->logItemBegin( $id, $url );

                $event = new ItemBeginEvent( $id, $url, $item );
                $this->trigger( self::EVENT_ON_ITEM_BEGIN, $event );
                if ( $event->shouldSkip() ) {

                    $this->log_container->logItemEnd( null, self::STATE_SKIP );
                    continue;
                } elseif ( $event->shouldBreak() ) {

                    $continue = false;
                    $break_event = new BreakListEvent( $event );
                    $this->trigger( self::EVENT_ON_BREAK_LIST, $break_event );

                    $this->log_container->logItemEnd( null, self::STATE_SKIP );
                    $this->log_container->logBreakList();
                    break;
                }

                $site_entry = null;
                $item_downloader = null;
                try {

                    $item_downloader = $this->getItemDownloader( $url, $id );
                    if ( $item_downloader !== null ) {

                        $item_downloader->scrape();
                        $site_entry = $item_downloader->getItem();
                        $state = self::STATE_OK;
                    } else {

                        $state = self::STATE_SKIP;
                    }
                } catch ( \Throwable $exception ) {

                    $this->log_container->logException( $exception, $url );
                    $state = self::STATE_FAIL;
                }

                try {

                    $event = new ItemEndEvent( $site_entry, $this->item_count, $state, $item );
                    $this->trigger( self::EVENT_ON_ITEM_END, $event );
                    $state = $event->getState();
                    $this->log_container->logItemEnd( $site_entry, $state );
                } catch ( \Throwable $exception ) {

                    $this->log_container->logException( $exception, $url );

                    $state = self::STATE_FAIL;
                    $event = new ItemEndEvent( null, $this->item_count, $state, $item );
                    $this->trigger( self::EVENT_ON_ITEM_END, $event );
                    $state = $event->getState();
                    $this->log_container->logItemEnd( $site_entry, $state );
                }
            }

            return $continue && $this->hasNextPage( $doc, $page_num );
        }

        /**
         * Scrapes the site.
         * @param int $page_num - The page number to start from.
         * @return ItemCount
         * @throws \Throwable
         */
        public function scrape( int $page_num = 1 ) {

            $this->item_count = new ItemCount();

            do {

                $continue = true;

                $event = new ListBeginEvent( $page_num );
                $this->trigger( self::EVENT_ON_LIST_BEGIN, $event );
                if ( $event->shouldBreak() ) {

                    break;
                } elseif ( !$event->shouldSkip() ) {

                    $this->log_container->logListBegin( $page_num );

                    $this->item_count->setCurrentPage( $page_num );
                    $continue = $this->downloadPage( $page_num );

                    $event = new ListEndEvent( $this->item_count, $continue );
                    $this->trigger( self::EVENT_ON_LIST_END, $event );
                    if ( $event->shouldBreak() ) {

                        $continue = false;
                    }

                    $this->log_container->logListEnd( $this->item_count, $continue );
                }

                if ( $continue ) {

                    $page_num++;
                }
            } while ( $continue );

            return $this->item_count;
        }

        /**
         * Returns true if there are more pages in the list, to be downloaded.
         * Can be overriden to provide some checks.
         * However, if this function returns true, but {@see getItems()} returnd no items, the scrape will end either.
         *
         * @param Crawler $doc - Crawler'is
         * @param int $current_page_num - Dabartinis puslapio numeris
         * @return bool
         */
        protected function hasNextPage( Crawler $doc, int $current_page_num ): bool {

            return true;
        }

        /**
         * Renders the listing page url for the given page number.
         * @param int|null $page_num
         * @return string
         */
        public abstract function getListUrl( ?int $page_num ): string;

        /**
         * If possible, returns the total number of items (in all pages), otherwise - null.
         * @param Crawler $doc
         * @return int|null
         */
        public abstract function getTotalItems( Crawler $doc ): ?int;

        /**
         * Returns the items, that need to be scraped.
         * The returned list or iterator must be of \DOMElement objects.
         *
         * @param Crawler $doc
         * @return iterable|\DOMElement[]
         */
        public abstract function getItems( Crawler $doc ): iterable;

        /**
         * Renders a url of the item to be scraped, from a \DOMElement.
         * If the item does not need to be scraped, can return null.
         *
         * @param \DOMElement $item
         * @return string|null
         */
        public abstract function getItemUrl( \DOMElement $item ): ?string;

        /**
         * Returns an ID of the item to be scraped.
         * Since ID can usually be found in a URL, an item URL is passed as the first parameter, however, in case it
         * is not in a url, a corresponding \DOMElement is also provided.
         *
         * @param string $url
         * @param \DOMElement $item
         * @return string
         */
        public abstract function getItemId( string $url, \DOMElement $item ): string;

        /**
         * Creates a new item downloader.
         * @param string $url
         * @param string $id
         * @return AbstractItemDownloader|null
         */
        public abstract function getItemDownloader( string $url, string $id ): ?AbstractItemDownloader;

        /**
         * Returns the number of items in a single page.
         * @param Crawler $doc - The list of items document, that's being scraped.
         * @return int|null
         */
        protected abstract function getNumberOfItemsInPage( Crawler $doc ): ?int;
    }