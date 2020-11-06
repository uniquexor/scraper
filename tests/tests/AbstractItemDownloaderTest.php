<?php
    namespace unique\scraperunit\tests;

    use GuzzleHttp\Client;
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
        public function testAssignOfDataOnCreation() {

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
            $mock->__construct( 'my.url.com', 'id', $this->createMock( AbstractItemListDownloader::class ), $site_item );
            $this->assertSame( $site_item, $mock->getItem() );
        }

        /**
         * @covers \unique\scraper\AbstractItemDownloader::createCrawlerFromUrl
         */
        public function testCreateCrawlerFromUrl() {

            $transport = $this->createPartialMock( Client::class, [ 'request' ] );
            $transport
                ->expects( $this->once() )
                ->method( 'request' )
                ->with( 'GET', 'my.url.com' );

            $list_downloader = $this->createMock( AbstractItemListDownloader::class );
            $list_downloader
                ->expects( $this->once() )
                ->method( 'getTransport' )
                ->willReturn( $transport );

            new ItemDownloader( 'my.url.com', 'id', $list_downloader, new SiteItem() );
        }
    }