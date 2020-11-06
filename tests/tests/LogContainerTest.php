<?php
    namespace unique\scraperunit\tests;

    use PHPUnit\Framework\TestCase;
    use unique\scraper\AbstractItemListDownloader;
    use unique\scraper\ItemCount;
    use unique\scraper\LogContainer;
    use unique\scraperunit\data\SiteItem;
    use unique\scraperunit\helper\ClosureMocker;

    /**
     * Class LogContainerTest
     *
     * @covers \unique\scraper\LogContainer
     * @package unique\scraperunit\tests
     */
    class LogContainerTest extends TestCase {

        public function testListBegin() {

            $container = new LogContainer();
            $container->logListBegin( null );

            $container->logListBegin( 5 );

            $expectation = [
                null,
                0,
                5
            ];

            $closure = function ( ?int $page_num ) use ( &$expectation ) {

                $expected = array_shift( $expectation );
                $this->assertSame( $expected, $page_num );
            };

            $container->setListBegin( $closure );
            $container->logListBegin( null );
            $container->logListBegin( 0 );
            $container->logListBegin( 5 );

            $this->assertSame( [], $expectation );

            $container->setListBegin( null );
            $container->logListBegin( 5 );
        }

        public function testListEnd() {

            $container = new LogContainer();
            $results = new ItemCount();
            $container->logListEnd( $results, true );

            $expectation = [
                [ $results, true ],
                [ $results, false ],
            ];

            $closure = new ClosureMocker( $this, $expectation );

            $container->setListEnd( $closure );
            $container->logListEnd( $results, true );
            $container->logListEnd( $results, false );

            $closure->assertExpectationsEmpty();

            $container->setListEnd( null );
            $container->logListEnd( $results, true );
        }

        public function testItemBegin() {

            $container = new LogContainer();
            $container->logItemBegin( 'id', 'url' );

            $expectation = [
                [ 'id', 'url' ],
                [ 'id2', null ]
            ];

            $closure = new ClosureMocker( $this, $expectation );

            $container->setItemBegin( $closure );
            $container->logItemBegin( 'id', 'url' );
            $container->logItemBegin( 'id2', null );

            $closure->assertExpectationsEmpty();

            $container->setItemBegin( null );
            $container->logItemBegin( 'id', 'url' );
        }

        public function testItemEnd() {

            $container = new LogContainer();
            $site_item = new SiteItem();
            $container->logItemEnd( $site_item, AbstractItemListDownloader::STATE_OK );

            $expectation = [
                [ $site_item, AbstractItemListDownloader::STATE_OK ],
                [ null, AbstractItemListDownloader::STATE_FAIL ]
            ];

            $closure = new ClosureMocker( $this, $expectation );

            $container->setItemEnd( $closure );
            $container->logItemEnd( $site_item, AbstractItemListDownloader::STATE_OK );
            $container->logItemEnd( null, AbstractItemListDownloader::STATE_FAIL );

            $closure->assertExpectationsEmpty();

            $container->setItemEnd( null );
            $container->logItemEnd( $site_item, AbstractItemListDownloader::STATE_MISSING_DATA );
        }

        public function testBreakUpdate() {

            $container = new LogContainer();
            $container->logBreakList();

            $expectation = [
                []
            ];

            $closure = new ClosureMocker( $this, $expectation );

            $container->setBreakUpdate( $closure );
            $container->logBreakList();

            $closure->assertExpectationsEmpty();

            $container->setBreakUpdate( null );
            $container->logBreakList();
        }

        public function testLog() {

            $container = new LogContainer();
            $container->log( 'param1' );

            $expectation = [
                [],
                [ 'param1' ],
                [ 'param1', 1, true ]
            ];

            $closure = new ClosureMocker( $this, $expectation );

            $container->setLogger( $closure );
            $container->log();
            $container->log( 'param1' );
            $container->log( 'param1', 1, true );

            $closure->assertExpectationsEmpty();

            $container->setLogger( null );
            $container->log();
        }

        public function testLogException() {

            $container = new LogContainer();
            $container->logException( new \Exception( 'test' ), null );

            $exception_1 = new \Exception( 'test' );

            $expectation = [
                [ $exception_1, null ],
                [ $exception_1, 'my.url.com' ]
            ];

            $closure = new ClosureMocker( $this, $expectation );

            $container->setExceptionLogger( $closure );
            $container->logException( $exception_1, null );
            $container->logException( $exception_1, 'my.url.com' );

            $closure->assertExpectationsEmpty();

            $container->setExceptionLogger( null );
            $container->logException( $exception_1, null );
        }
    }