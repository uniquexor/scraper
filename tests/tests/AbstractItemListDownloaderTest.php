<?php
    namespace unique\scraperunit\tests;

    use GuzzleHttp\Client;
    use GuzzleHttp\Psr7\Response;
    use PHPUnit\Framework\TestCase;
    use Symfony\Component\DomCrawler\Crawler;
    use unique\scraper\AbstractItemListDownloader;
    use unique\scraper\events\BreakListEvent;
    use unique\scraper\events\ItemBeginEvent;
    use unique\scraper\events\ItemEndEvent;
    use unique\scraper\events\ItemMissingUrlEvent;
    use unique\scraper\events\ListBeginEvent;
    use unique\scraper\events\ListEndEvent;
    use unique\scraper\ItemCount;
    use unique\scraper\LogContainer;
    use unique\scraperunit\data\ItemDownloader;
    use unique\scraperunit\data\ItemListDownloader;
    use unique\scraperunit\data\SiteItem;

    /**
     * Class AbstractItemListDownloaderTest
     *
     * @covers \unique\scraper\AbstractItemListDownloader
     * @package unique\scraperunit\tests
     */
    class AbstractItemListDownloaderTest extends TestCase {

        /**
         * Sets up a mock Client object for mock requests.
         * @return Client|\PHPUnit\Framework\MockObject\MockObject
         */
        protected function setUpMockTransport() {

            $transport = $this->createMock( Client::class );
            $transport
                ->method( 'request' )
                ->willReturn( new Response( 200, [], '<body><p>Hello World!</p></body>' ) );

            return $transport;
        }

        public function testGetTransport() {

            $transport = new Client();
            $downloader = new ItemListDownloader( SiteItem::class, $transport );
            $this->assertSame( $transport, $downloader->getTransport() );
        }

        public function testScrapeCallsDownloadPagesAndContinuesWhenNecessary() {

            $mock = $this->createPartialMock( ItemListDownloader::class, [ 'downloadPage' ] );
            $mock
                ->expects( $this->exactly( 3 ) )
                ->method( 'downloadPage' )
                ->withConsecutive(
                    [ 1 ],
                    [ 2 ],
                    [ 3 ],
                )
                ->willReturnOnConsecutiveCalls(
                    true,
                    true,
                    false
                );

            $mock->__construct( SiteItem::class, new Client() );
            $mock->scrape();


            $mock = $this->createPartialMock( ItemListDownloader::class, [ 'downloadPage' ] );
            $mock
                ->expects( $this->exactly( 1 ) )
                ->method( 'downloadPage' )
                ->withConsecutive(
                    [ 2 ],
                )
                ->willReturnOnConsecutiveCalls(
                    false
                );

            $mock->__construct( SiteItem::class, new Client() );
            $res = $mock->scrape( 2 );
            $this->assertInstanceOf( ItemCount::class, $res );
        }

        public function testScrapeEventsTriggeredAndDataLogged() {

            $logger = $this->createPartialMock( LogContainer::class, [ 'logListBegin', 'logListEnd' ] );
            $logger
                ->expects( $this->exactly( 2 ) )
                ->method( 'logListBegin' )
                ->withConsecutive( [ 1 ], [ 2 ] );

            $first = true;
            $logger
                ->expects( $this->exactly( 2 ) )
                ->method( 'logListEnd' )
                ->withConsecutive(
                    [
                        $this->callback( function ( ItemCount $val ) use ( &$first ) {

                            if ( $first ) {

                                $this->assertSame( 1, $val->getCurrentPage() );

                                // Some weird phpunit bug?... get's called the second time, even though, from my understanding, it shouldn't happen...
                                $first = false;
                            }

                            return true;
                        } ),
                        true
                    ],
                    [
                        $this->callback( function ( ItemCount $val ) {

                            $this->assertSame( 2, $val->getCurrentPage() );
                            return true;
                        } ),
                        false
                    ]
                );

            $mock = $this->createPartialMock( ItemListDownloader::class, [ 'downloadPage' ] );
            $mock
                ->expects( $this->exactly( 2 ) )
                ->method( 'downloadPage' )
                ->willReturnOnConsecutiveCalls(
                    true,
                    false
                );

            $mock->__construct( SiteItem::class, new Client(), $logger );
            $mock->scrape();
        }

        public function testScrapeBreakEvent() {

            $mock = $this->createPartialMock( ItemListDownloader::class, [ 'downloadPage' ] );
            $mock
                ->expects( $this->exactly( 1 ) )
                ->method( 'downloadPage' )
                ->with( 2 )
                ->willReturn( true );

            $mock->__construct( SiteItem::class, new Client() );
            $mock->on( AbstractItemListDownloader::EVENT_ON_LIST_BEGIN, function ( ListBeginEvent $event ) {

                if ( $event->getPageNum() === 1 ) {

                    $event->skip();
                } elseif ( $event->getPageNum() === 2 ) {

                    $event->continue();
                } elseif ( $event->getPageNum() === 3 ) {

                    $event->break();
                }
            } );

            $mock->scrape();

            // Break on LIST END:

            $mock = $this->createPartialMock( ItemListDownloader::class, [ 'downloadPage' ] );
            $mock
                ->expects( $this->exactly( 3 ) )
                ->method( 'downloadPage' )
                ->willReturn( true );

            $mock->__construct( SiteItem::class, new Client() );
            $mock->on( AbstractItemListDownloader::EVENT_ON_LIST_END, function ( ListEndEvent $event ) {

                if ( $event->getItemCount()->getCurrentPage() === 1 ) {

                    $event->skip();
                } elseif ( $event->getItemCount()->getCurrentPage() === 2 ) {

                    $event->continue();
                } elseif ( $event->getItemCount()->getCurrentPage() === 3 ) {

                    $event->break();
                }
            } );

            $mock->scrape();
        }

        public function testDownloadPageRequestIsMadeAndItemCountSet() {

            $exception = new \Exception( 'Bad request' );
            $expectation = [
                new Response( 200, [], '<body><p>Hello World!</p></body>' ),
                $exception,
            ];
            $transport = $this->createPartialMock( Client::class, [ 'request' ] );
            $transport
                ->expects( $this->exactly( 2 ) )
                ->method( 'request' )
                ->with( 'GET', 'my.request.com', [] )
                ->willReturnCallback( function () use ( &$expectation ) {

                    $expect = array_shift( $expectation );
                    if ( $expect instanceof \Exception ) {

                        throw $expect;
                    } else {

                        return $expect;
                    }
                } );

            $mock = $this->createPartialMock( ItemListDownloader::class, [ 'getListUrl', 'getTotalItems', 'getNumberOfItemsInPage', 'getItems' ] );
            $mock
                ->expects( $this->exactly( 2 ) )
                ->method( 'getListUrl' )
                ->willReturn( 'my.request.com' );
            $mock
                ->method( 'getItems' )
                ->willReturn( [] );

            $mock
                ->expects( $this->once() )
                ->method( 'getTotalItems' )
                ->with( $this->callback( function ( Crawler $doc ) {

                    $this->assertSame( '<body><p>Hello World!</p></body>', $doc->html() );
                    return true;
                } ) )
                ->willReturn( 1000 );

            $mock
                ->expects( $this->once() )
                ->method( 'getNumberOfItemsInPage' )
                ->with( $this->callback( function ( Crawler $doc ) {

                    $this->assertSame( '<body><p>Hello World!</p></body>', $doc->html() );
                    return true;
                } ) )
                ->willReturn( 10 );

            $mock->__construct( SiteItem::class, $transport );
            $res = $mock->scrape();
            $this->assertSame( 1000, $res->getTotalItems() );
            $this->assertSame( 10, $res->getNumberOfItemsInPage() );

            $this->expectException( \Exception::class );
            $this->expectExceptionMessage( 'Bad request' );

            $log_container = $this->createPartialMock( LogContainer::class, [ 'logException' ] );
            $log_container
                ->expects( $this->once() )
                ->method( 'logException' )
                ->with( $exception, 'my.request.com' );

            $mock->setLogContainer( $log_container );
            $mock->scrape();
        }

        public function testItemIsDownloadedAndEndEventTriggered() {

            $transport = $this->setUpMockTransport();

            $mock = $this->createPartialMock( ItemListDownloader::class, [ 'getItems', 'getItemDownloader', 'hasNextPage', 'getItemUrl', 'getItemId' ] );
            $mock
                ->method( 'hasNextPage' )
                ->willReturn( false );
            $mock
                ->method( 'getItemUrl' )
                ->willReturnOnConsecutiveCalls( 'my.item1.com', 'my.item2.com', 'my.item3.com' );

            $items = [];
            $items[] = new \DOMElement( 'a' );
            $items[] = new \DOMElement( 'a' );
            $items[] = new \DOMElement( 'a' );

            $mock
                ->expects( $this->exactly( 3 ) )
                ->method( 'getItemId' )
                ->withConsecutive(
                    [ 'my.item1.com', $items[0] ],
                    [ 'my.item2.com', $items[1] ],
                    [ 'my.item3.com', $items[2] ],
                )
                ->willReturnOnConsecutiveCalls( 'id1', 'id2', 'id3', 'id4', 'id5' );

            $site_item = new SiteItem();
            $item_downloader = $this->createPartialMock( ItemDownloader::class, [ 'scrape', 'getItem' ] );
            $item_downloader
                ->expects( $this->once() )
                ->method( 'getItem' )
                ->willReturn( $site_item );

            $exception_item_downloader = new \Exception( 'test on get item downloader' );

            $get_item_downloader_expectation = [
                $item_downloader,
                $exception_item_downloader,
                null
            ];

            $mock
                ->expects( $this->exactly( 3 ) )
                ->method( 'getItemDownloader' )
                ->withConsecutive(
                    [ 'my.item1.com', 'id1' ],
                    [ 'my.item2.com', 'id2' ],
                    [ 'my.item3.com', 'id3' ],
                )
                ->willReturnCallback(
                    function () use ( &$get_item_downloader_expectation ) {

                        $item = array_shift( $get_item_downloader_expectation );
                        if ( $item instanceof \Exception ) {

                            throw $item;
                        } else {

                            return $item;
                        }
                    }
                );

            $mock
                ->expects( $this->once() )
                ->method( 'getItems' )
                ->willReturn( $items );

            $event_ok = new ItemEndEvent( $site_item, new ItemCount(), AbstractItemListDownloader::STATE_OK );
            $event_fail = new ItemEndEvent( null, new ItemCount(), AbstractItemListDownloader::STATE_FAIL );
            $event_skip = new ItemEndEvent( null, new ItemCount(), AbstractItemListDownloader::STATE_SKIP );

            $exception_item_end_event = new \Exception( 'test on event item end' );

            $expected_on_item_end = [
                [ $event_ok, $exception_item_end_event ],
                [ $event_fail, null ],
                [ $event_fail, null ],
                [ $event_skip, null ],
            ];
            $mock->on( AbstractItemListDownloader::EVENT_ON_ITEM_END, function ( ItemEndEvent $event ) use ( &$expected_on_item_end ) {

                $this->assertNotEmpty( $expected_on_item_end, 'Should not be called more than 4 times' );

                $item = array_shift( $expected_on_item_end );
                list( $item, $exception ) = $item;

                $this->assertSame( $item->getSiteItem(), $event->getSiteItem() );
                $this->assertSame( $item->getState(), $event->getState() );

                if ( $exception !== null ) {

                    throw $exception;
                }
            } );

            $log_container = $this->createPartialMock( LogContainer::class, [ 'logItemEnd', 'logException' ] );
            $log_container
                ->expects( $this->exactly( 2 ) )
                ->method( 'logException' )
                ->withConsecutive(
                    [ $exception_item_end_event, 'my.item1.com' ],
                    [ $exception_item_downloader, 'my.item2.com' ]
                );

            $log_container
                ->expects( $this->exactly( 3 ) )
                ->method( 'logItemEnd' )
                ->withConsecutive(
                    [ $site_item, AbstractItemListDownloader::STATE_FAIL ],
                    [ null, AbstractItemListDownloader::STATE_FAIL ],
                    [ null, AbstractItemListDownloader::STATE_SKIP ]
                );

            $mock->__construct( SiteItem::class, $transport, $log_container );
            $mock->scrape();

            $this->assertEmpty( $expected_on_item_end, 'Should be called 4 times' );
        }

        public function testItemStateChangeAfterItemEndEvent() {

            $transport = $this->setUpMockTransport();

            $mock = $this->createPartialMock( ItemListDownloader::class, [ 'getItems', 'getItemDownloader', 'hasNextPage', 'getItemUrl', 'getItemId' ] );
            $mock
                ->method( 'hasNextPage' )
                ->willReturn( false );
            $mock
                ->method( 'getItemUrl' )
                ->willReturnOnConsecutiveCalls( 'my.item1.com', 'my.item2.com', 'my.item3.com' );

            $items = [];
            $items[] = new \DOMElement( 'a' );
            $items[] = new \DOMElement( 'a' );
            $items[] = new \DOMElement( 'a' );

            $mock
                ->method( 'getItemId' )
                ->willReturnOnConsecutiveCalls( 'id1', 'id2', 'id3', 'id4', 'id5' );

            $site_item = new SiteItem();
            $item_downloader = $this->createPartialMock( ItemDownloader::class, [ 'scrape', 'getItem' ] );
            $item_downloader
                ->method( 'getItem' )
                ->willReturn( $site_item );

            $mock
                ->method( 'getItemDownloader' )
                ->willReturn( $item_downloader );

            $mock
                ->expects( $this->once() )
                ->method( 'getItems' )
                ->willReturn( $items );

            $exception_item_end_event = new \Exception( 'test on event item end' );

            $expected_on_item_end = [
                [ null, null ],
                [ AbstractItemListDownloader::STATE_SKIP, null ],
                [ null, $exception_item_end_event ],
                [ AbstractItemListDownloader::STATE_SKIP, null ],
            ];
            $mock->on( AbstractItemListDownloader::EVENT_ON_ITEM_END, function ( ItemEndEvent $event ) use ( &$expected_on_item_end ) {

                $this->assertNotEmpty( $expected_on_item_end, 'Should not be called more than 4 times' );

                $item = array_shift( $expected_on_item_end );
                list( $new_state, $exception ) = $item;

                if ( $new_state !== null ) {

                    $event->setState( $new_state );
                }

                if ( $exception !== null ) {

                    throw $exception;
                }
            } );

            $log_container = $this->createPartialMock( LogContainer::class, [ 'logItemEnd' ] );

            $log_container
                ->expects( $this->exactly( 3 ) )
                ->method( 'logItemEnd' )
                ->withConsecutive(
                    [ $site_item, AbstractItemListDownloader::STATE_OK ],
                    [ $site_item, AbstractItemListDownloader::STATE_SKIP ],
                    [ $site_item, AbstractItemListDownloader::STATE_SKIP ]
                );

            $mock->__construct( SiteItem::class, $transport, $log_container );
            $mock->scrape();
        }

        public function testItemBeginEventIsTriggeredAndIsBreakable() {

            $transport = $this->setUpMockTransport();

            $mock = $this->createPartialMock( ItemListDownloader::class, [ 'getItems', 'getItemDownloader', 'hasNextPage', 'getItemUrl', 'getItemId' ] );
            $mock
                ->expects( $this->never() )
                ->method( 'hasNextPage' );
            $mock
                ->method( 'getItemUrl' )
                ->willReturnOnConsecutiveCalls( 'my.item1.com', 'my.item2.com', 'my.item3.com' );

            $items = [];
            $items[] = new \DOMElement( 'a' );
            $items[] = new \DOMElement( 'a' );
            $items[] = new \DOMElement( 'a' );

            $mock
                ->expects( $this->exactly( 3 ) )
                ->method( 'getItemId' )
                ->withConsecutive(
                    [ 'my.item1.com', $items[0] ],
                    [ 'my.item2.com', $items[1] ],
                    [ 'my.item3.com', $items[2] ],
                )
                ->willReturnOnConsecutiveCalls( 'id1', 'id2', 'id3', 'id4', 'id5' );

            $site_item = new SiteItem();
            $item_downloader = $this->createPartialMock( ItemDownloader::class, [ 'scrape', 'getItem' ] );
            $item_downloader
                ->method( 'getItem' )
                ->willReturn( $site_item );

            $mock
                ->method( 'getItemDownloader' )
                ->willReturn( $item_downloader );

            $mock
                ->expects( $this->once() )
                ->method( 'getItems' )
                ->willReturn( $items );

            $break_event = null;
            $event_expectations = [
                [ new ItemBeginEvent( 'id1', 'my.item1.com' ), null ],
                [ new ItemBeginEvent( 'id2', 'my.item2.com' ), function ( ItemBeginEvent $event ) { $event->skip(); } ],
                [
                    new ItemBeginEvent( 'id3', 'my.item3.com' ),
                    function ( ItemBeginEvent $event ) use ( &$break_event ) {

                        $event->break();
                        $break_event = $event;
                    }
                ],
            ];

            $mock->on( AbstractItemListDownloader::EVENT_ON_ITEM_BEGIN, function ( ItemBeginEvent $event ) use ( &$event_expectations ) {

                $this->assertNotEmpty( $event_expectations, 'Should not be called more than 3 times' );

                $item = array_shift( $event_expectations );
                list( $item, $closure ) = $item;

                $this->assertSame( $item->getUrl(), $event->getUrl() );
                $this->assertSame( $item->getId(), $event->getId() );

                if ( $closure !== null ) {

                    call_user_func( $closure, $event );
                }
            } );

            $is_break_list_event_called = false;
            $mock->on( AbstractItemListDownloader::EVENT_ON_BREAK_LIST, function ( BreakListEvent $event ) use ( &$break_event, &$is_break_list_event_called ) {

                $this->assertSame( $break_event, $event->getCausingEvent() );
                $is_break_list_event_called = true;
            } );

            $log_container = $this->createPartialMock( LogContainer::class, [ 'logItemBegin', 'logItemEnd', 'logBreakList' ] );
            $log_container
                ->expects( $this->exactly( 3 ) )
                ->method( 'logItemBegin' )
                ->withConsecutive(
                    [ 'id1', 'my.item1.com' ],
                    [ 'id2', 'my.item2.com' ],
                    [ 'id3', 'my.item3.com' ],
                );

            $log_container
                ->expects( $this->exactly( 3 ) )
                ->method( 'logItemEnd' )
                ->withConsecutive(
                    [ $site_item, AbstractItemListDownloader::STATE_OK ],
                    [ null, AbstractItemListDownloader::STATE_SKIP ],
                    [ null, AbstractItemListDownloader::STATE_SKIP ],
                );

            $log_container
                ->expects( $this->once() )
                ->method( 'logBreakList' );

            $mock->__construct( SiteItem::class, $transport, $log_container );
            $mock->scrape();

            $this->assertEmpty( $event_expectations, 'Should be called 3 times' );
            $this->assertTrue( $is_break_list_event_called, 'Must be called' );
        }

        public function testMissingItemUrlAndBreakability() {

            $transport = $this->setUpMockTransport();

            $mock = $this->createPartialMock( ItemListDownloader::class, [ 'getItems', 'getItemDownloader', 'hasNextPage', 'getItemUrl', 'getItemId' ] );
            $mock
                ->expects( $this->once() )
                ->method( 'hasNextPage' )
                ->willReturn( false );
            $mock
                ->method( 'getItemUrl' )
                ->willReturn( null );

            $items = [];
            $items[] = new \DOMElement( 'a' );
            $items[] = new \DOMElement( 'a' );

            $mock
                ->method( 'getItemId' )
                ->willReturnOnConsecutiveCalls( 'id1', 'id2', 'id3', 'id4', 'id5' );

            $site_item = new SiteItem();
            $item_downloader = $this->createPartialMock( ItemDownloader::class, [ 'scrape', 'getItem' ] );
            $item_downloader
                ->method( 'getItem' )
                ->willReturn( $site_item );

            $mock
                ->expects( $this->once() )
                ->method( 'getItemDownloader' )
                ->with( 'my.test.com', 'id1' )
                ->willReturn( $item_downloader );

            $mock
                ->expects( $this->once() )
                ->method( 'getItems' )
                ->willReturn( $items );

            $expectations = [
                [ new ItemMissingUrlEvent( $items[0] ), null ],
                [
                    new ItemMissingUrlEvent( $items[1] ),
                    function ( ItemMissingUrlEvent $event ) {

                        $event->setUrl( 'my.test.com' );
                    }
                ]
            ];

            $mock->on( AbstractItemListDownloader::EVENT_ON_ITEM_MISSING_URL, function ( ItemMissingUrlEvent $event ) use ( &$expectations ) {

                $this->assertNotEmpty( $expectations, 'Should not be called more than 2 times' );

                $item = array_shift( $expectations );
                list( $item, $closure ) = $item;

                $this->assertSame( $item->getUrl(), $event->getUrl() );
                if ( $closure !== null ) {

                    call_user_func( $closure, $event );
                }
            } );

            $log_container = $this->createPartialMock( LogContainer::class, [ 'logItemBegin', 'logItemEnd' ] );
            $log_container
                ->expects( $this->exactly( 2 ) )
                ->method( 'logItemBegin' )
                ->withConsecutive(
                    [ null, null ],
                    [ 'id1', 'my.test.com' ],
                );

            $log_container
                ->expects( $this->exactly( 2 ) )
                ->method( 'logItemEnd' )
                ->withConsecutive(
                    [ null, AbstractItemListDownloader::STATE_MISSING_DATA ],
                    [ $site_item, AbstractItemListDownloader::STATE_OK ],
                );

            $mock->__construct( SiteItem::class, $transport, $log_container );
            $mock->scrape();

            $this->assertEmpty( $expectations, 'Should be called 2 times' );
        }
    }