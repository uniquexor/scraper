<?php
    namespace unique\scraperunit\tests;

    use PHPUnit\Framework\TestCase;
    use unique\scraper\ItemCount;

    class ItemCountTest extends TestCase {

        /**
         * @covers \unique\scraper\ItemCount::setTotalItems
         * @covers \unique\scraper\ItemCount::getTotalItems
         */
        public function testTotalItems() {

            $obj = new ItemCount();
            $this->assertNull( $obj->getTotalItems() );

            $obj->setTotalItems( 0 );
            $this->assertSame( 0, $obj->getTotalItems() );

            $obj->setTotalItems( 10 );
            $this->assertSame( 10, $obj->getTotalItems() );

            $obj->setTotalItems( null );
            $this->assertNull( $obj->getTotalItems() );
        }

        /**
         * @covers \unique\scraper\ItemCount::setCurrentPage
         * @covers \unique\scraper\ItemCount::getCurrentPage
         */
        public function testCurrentPage() {

            $obj = new ItemCount();
            $this->assertNull( $obj->getCurrentPage() );

            $obj->setCurrentPage( 0 );
            $this->assertSame( 0, $obj->getCurrentPage() );

            $obj->setCurrentPage( 10 );
            $this->assertSame( 10, $obj->getCurrentPage() );

            $obj->setCurrentPage( null );
            $this->assertNull( $obj->getCurrentPage() );
        }

        /**
         * @covers \unique\scraper\ItemCount::setNumberOfItemsInPage
         * @covers \unique\scraper\ItemCount::getNumberOfItemsInPage
         */
        public function testNumberOfItemsInPage() {

            $obj = new ItemCount();
            $this->assertNull( $obj->getNumberOfItemsInPage() );

            $obj->setNumberOfItemsInPage( 0 );
            $this->assertSame( 0, $obj->getNumberOfItemsInPage() );

            $obj->setNumberOfItemsInPage( 10 );
            $this->assertSame( 10, $obj->getNumberOfItemsInPage() );

            $obj->setNumberOfItemsInPage( null );
            $this->assertNull( $obj->getNumberOfItemsInPage() );
        }
    }