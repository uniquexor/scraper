<?php
    namespace unique\scraperunit\tests;

    use PHPUnit\Framework\TestCase;
    use unique\scraperunit\data\BreakableEvent;

    class BreakableEventTraitTest extends TestCase {

        public function test() {

            $event = new BreakableEvent();
            $this->assertFalse( $event->shouldBreak() );
            $this->assertFalse( $event->shouldSkip() );

            $event->skip();
            $this->assertFalse( $event->shouldBreak() );
            $this->assertTrue( $event->shouldSkip() );

            $event->break();
            $this->assertTrue( $event->shouldBreak() );
            $this->assertFalse( $event->shouldSkip() );

            $event->continue();
            $this->assertFalse( $event->shouldBreak() );
            $this->assertFalse( $event->shouldSkip() );
        }
    }