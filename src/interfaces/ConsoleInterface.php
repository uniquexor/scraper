<?php
    namespace unique\scraper\interfaces;

    interface ConsoleInterface {

        public function stdout( string $string );

        public function stderr( string $string );
    }