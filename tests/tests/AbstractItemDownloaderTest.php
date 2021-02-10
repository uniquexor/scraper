<?php
    namespace unique\scraperunit\tests;

    use GuzzleHttp\Client;
    use GuzzleHttp\Exception\ConnectException;
    use GuzzleHttp\Psr7\Request;
    use GuzzleHttp\Psr7\Response;
    use PHPUnit\Framework\MockObject\MockObject;
    use PHPUnit\Framework\TestCase;
    use Symfony\Component\DomCrawler\Crawler;
    use unique\scraper\AbstractItemListDownloader;
    use unique\scraperunit\data\ItemDownloader;
    use unique\scraperunit\data\SiteItem;

    class AbstractItemDownloaderTest extends TestCase {

        /**
         * @covers \unique\scraper\AbstractItemDownloader
         */
        public function testScrape() {

            $site_item = new SiteItem();
            $mock = $this->createPartialMock( ItemDownloader::class, [ 'assignItemData', 'createCrawlerFromUrl' ] );

            $crawler = new Crawler();
            $mock
                ->expects( $this->once() )
                ->method( 'createCrawlerFromUrl' )
                ->with( 'my.url.com' )
                ->willReturn( $crawler );

            $mock
                ->expects( $this->once() )
                ->method( 'assignItemData' )
                ->with( $crawler );

            /**
             * @var ItemDownloader|MockObject $mock
             */
            $mock->__construct( 'my.url.com', 'id', $this->createMock( Client::class ), $site_item );
            $mock->scrape();
            $this->assertSame( $site_item, $mock->getItem() );
        }

        /**
         * @covers \unique\scraper\AbstractItemDownloader::createCrawlerFromUrl
         * @covers \unique\scraper\traits\RetryableRequestTrait::retryRequest
         */
        public function testCreateCrawlerFromUrl() {

            $transport = $this->createPartialMock( Client::class, [ 'request' ] );
            $transport
                ->expects( $this->once() )
                ->method( 'request' )
                ->with( 'GET', 'my.url.com' );

            $item = new ItemDownloader( 'my.url.com', 'id', $transport, new SiteItem() );
            $item->scrape();

            // Retry request 2 times, 3rd - success

            $retry = 0;
            $transport = $this->createPartialMock( Client::class, [ 'request' ] );
            $transport
                ->expects( $this->exactly( 3 ) )
                ->method( 'request' )
                ->with( 'GET', 'my.url.com' )
                ->willReturnCallback( function () use ( &$retry ) {

                    if ( ++$retry === 3 ) {

                        return new Response( 200, [], '<h1>Hello World!</h1>' );
                    } else {

                        throw new ConnectException( 'Test', new Request( 'GET', 'url' ) );
                    }
                } );

            /**
             * @var ItemDownloader|MockObject $item
             */
            $item = $this->createPartialMock( ItemDownloader::class, [ 'assignItemData' ] );
            $item->expects( $this->once() )
                ->method( 'assignItemData' )
                ->with( $this->callback( function ( Crawler $crawler ) {

                    return $crawler->html() === '<body><h1>Hello World!</h1></body>';
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
             * @var ItemDownloader|MockObject $item
             */
            $this->expectExceptionObject( $exception );
            $item = new ItemDownloader( 'url', 'id', $transport, new SiteItem() );
            $item->on_connect_exception_retry_timeout = 0;
            $item->scrape();
        }
    }