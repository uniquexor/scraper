<?php
    namespace unique\scraperunit\tests;

    use GuzzleHttp\Client;
    use GuzzleHttp\Exception\ConnectException;
    use GuzzleHttp\Psr7\Request;
    use GuzzleHttp\Psr7\Response;
    use PHPUnit\Framework\MockObject\MockObject;
    use PHPUnit\Framework\TestCase;
    use Symfony\Component\DomCrawler\Crawler;
    use unique\scraper\exceptions\BadJsonFileException;
    use unique\scraperunit\data\ItemJsonDownloader;
    use unique\scraperunit\data\SiteItem;

    class AbstractJsonItemDownloaderTest extends TestCase {

        /**
         * @covers \unique\scraper\AbstractJsonItemDownloader
         */
        public function testScrape() {

            $site_item = new SiteItem();
            $mock = $this->createPartialMock( ItemJsonDownloader::class, [ 'assignItemData', 'createJsonFromUrl' ] );

            $json = array( 'id' => 1 );
            $mock
                ->expects( $this->once() )
                ->method( 'createJsonFromUrl' )
                ->with( 'my.url.com' )
                ->willReturn( $json );

            $mock
                ->expects( $this->once() )
                ->method( 'assignItemData' )
                ->with( $json );

            /**
             * @var ItemJsonDownloader|MockObject $mock
             */
            $mock->__construct( 'my.url.com', 'id', $this->createMock( Client::class ), $site_item );
            $mock->scrape();
            $this->assertSame( $site_item, $mock->getItem() );
        }

        /**
         * @covers \unique\scraper\AbstractJsonItemDownloader::createJsonFromUrl
         */
        public function testCreateJsonFromUrl() {

            $transport = $this->createPartialMock( Client::class, [ 'request' ] );
            $transport
                ->expects( $this->once() )
                ->method( 'request' )
                ->with( 'GET', 'my.url.com' )
                ->willReturn( new Response( 200, [], json_encode( [ 'id' => 1 ] ) ) );

            $item = $this->createPartialMock( ItemJsonDownloader::class, [ 'assignItemData' ] );
            $item->expects( $this->once() )
                ->method( 'assignItemData' )
                ->with( [ 'id' => 1 ] );

            /**
             * @var ItemJsonDownloader|MockObject $mock
             */
            $item->__construct( 'my.url.com', 'id', $transport, new SiteItem() );
            $item->scrape();
        }

        public function testCreateJsonFromUrlBadJsonException() {

            $transport = $this->createPartialMock( Client::class, [ 'request' ] );
            $transport
                ->expects( $this->once() )
                ->method( 'request' )
                ->with( 'GET', 'my.url.com' )
                ->willReturn( new Response( 200, [], 'some bad json' ) );

            $item = new ItemJsonDownloader( 'my.url.com', 'id', $transport, new SiteItem() );

            $this->expectException( BadJsonFileException::class );
            $item->scrape();
        }

        public function testCreateJsonFromUrlRetry() {

            // Retry request 2 times, 3rd - success

            $retry = 0;
            $transport = $this->createPartialMock( Client::class, [ 'request' ] );
            $transport
                ->expects( $this->exactly( 3 ) )
                ->method( 'request' )
                ->with( 'GET', 'my.url.com' )
                ->willReturnCallback( function () use ( &$retry ) {

                    if ( ++$retry === 3 ) {

                        return new Response( 200, [], json_encode( [ 'id' => 2 ] ) );
                    } else {

                        throw new ConnectException( 'Test', new Request( 'GET', 'url' ) );
                    }
                } );

            /**
             * @var ItemJsonDownloader|MockObject $item
             */
            $item = $this->createPartialMock( ItemJsonDownloader::class, [ 'assignItemData' ] );
            $item->expects( $this->once() )
                ->method( 'assignItemData' )
                ->with( $this->callback( function ( array $json ) {

                    return $json === [ 'id' => 2 ];
                } ) );

            $item->__construct( 'my.url.com', 'id', $transport, new SiteItem() );
            $item->on_connect_exception_retry_timeout = 0;
            $item->scrape();


            // Retry request 3 times, all fail

            $exception = new ConnectException( 'Test', new Request( 'GET', 'url' ) );

            $transport = $this->createPartialMock( Client::class, [ 'request' ] );
            $transport
                ->expects( $this->exactly( 4 ) )
                ->method( 'request' )
                ->willThrowException( $exception );

            /**
             * @var ItemJsonDownloader|MockObject $item
             */
            $this->expectExceptionObject( $exception );
            $item = new ItemJsonDownloader( 'url', 'id', $transport, new SiteItem() );
            $item->on_connect_exception_retry_timeout = 0;
            $item->scrape();
        }
    }