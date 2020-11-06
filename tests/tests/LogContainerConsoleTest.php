<?php
    namespace unique\scraperunit\tests;

    use PHPUnit\Framework\TestCase;
    use unique\scraper\AbstractItemListDownloader;
    use unique\scraper\ItemCount;
    use unique\scraper\LogContainerConsole;
    use unique\scraperunit\data\Console;
    use unique\scraperunit\data\SiteItem;

    /**
     * Class LogContainerConsoleTest
     *
     * @covers \unique\scraper\LogContainerConsole
     * @package unique\scraperunit\tests
     */
    class LogContainerConsoleTest extends TestCase {

        public function testLogListBegin() {

            $console = $this->createPartialMock( Console::class, [ 'stdout' ] );
            $console
                ->expects( $this->once() )
                ->method( 'stdout' )
                ->with( '1: ' );

            $logger = new LogContainerConsole( $console );
            $logger->logListBegin( 1 );
        }

        public function testLogListEnd() {

            $item_count = new ItemCount();

            $console = $this->createPartialMock( Console::class, [ 'stdout' ] );
            $console
                ->expects( $this->exactly( 6 ) )
                ->method( 'stdout' )
                ->withConsecutive(
                    [ ' ...done.' . "\r\n" ],
                    [ '' . "\r\n" ],
                    [ '' . "\r\n" ],
                    [ ' (0 / 100)' . "\r\n" ],
                    [ ' (50 / 100)' . "\r\n" ],
                    [ ' (100 / 100)' . "\r\n" ],
                );

            $logger = new LogContainerConsole( $console );
            $logger->logListEnd( $item_count, false );
            $logger->logListEnd( $item_count, true );

            $item_count->setNumberOfItemsInPage( 10 );
            $logger->logListEnd( $item_count, true );

            $item_count->setTotalItems( 100 );
            $logger->logListEnd( $item_count, true );

            $item_count->setCurrentPage( 5 );
            $logger->logListEnd( $item_count, true );

            $item_count->setCurrentPage( 15 );
            $logger->logListEnd( $item_count, true );
        }

        public function testLogItemEnd() {

            $site_item = new SiteItem();

            $console = $this->createPartialMock( Console::class, [ 'stdout' ] );
            $console
                ->expects( $this->exactly( 5 ) )
                ->method( 'stdout' )
                ->withConsecutive(
                    [ '.' ],
                    [ 's' ],
                    [ 'f' ],
                    [ 'x' ],
                    [ 'x' ],
                );

            $logger = new LogContainerConsole( $console );

            $logger->logItemEnd( $site_item, AbstractItemListDownloader::STATE_OK );
            $logger->logItemEnd( $site_item, AbstractItemListDownloader::STATE_SKIP );
            $logger->logItemEnd( $site_item, AbstractItemListDownloader::STATE_FAIL );
            $logger->logItemEnd( $site_item, AbstractItemListDownloader::STATE_MISSING_DATA );

            $logger->logItemEnd( null, AbstractItemListDownloader::STATE_MISSING_DATA );
        }

        public function testGetTextFromStateDefault() {

            $this->assertSame( '123', LogContainerConsole::getTextFromState( 123 ) );
        }

        public function testLogBreakList() {

            $console = $this->createPartialMock( Console::class, [ 'stdout' ] );
            $console
                ->expects( $this->exactly( 1 ) )
                ->method( 'stdout' )
                ->withConsecutive(
                    [ ' ...break' . "\r\n" ],
                );

            $logger = new LogContainerConsole( $console );

            $logger->logBreakList();
        }

        public function testLog() {

            $console = $this->createPartialMock( Console::class, [ 'stdout' ] );
            $console
                ->expects( $this->exactly( 1 ) )
                ->method( 'stdout' )
                ->withConsecutive(
                    [ 'test', 1, 2, 3 ],
                );

            $logger = new LogContainerConsole( $console );

            $logger->log( 'test', 1, 2, 3 );
        }

        public function testLogException() {

            $console = $this->createPartialMock( Console::class, [ 'stderr' ] );
            $console
                ->expects( $this->exactly( 2 ) )
                ->method( 'stderr' )
                ->withConsecutive(
                    [ 'exception' ],
                    [ 'error' ]
                );

            $logger = new LogContainerConsole( $console );

            $logger->logException( new \Exception( 'exception' ), 'my.url.com' );
            $logger->logException( new \Error( 'error' ), 'my.url.com' );
        }
    }