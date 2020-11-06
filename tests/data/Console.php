<?php

    namespace unique\scraperunit\data;
    use unique\scraper\interfaces\ConsoleInterface;

    class Console implements ConsoleInterface {

        public $stdout = '';
        public $stderr = '';

        public function stdout( string $string ) {

            $this->stdout .= $string;
        }

        public function stderr( string $string ) {

            $this->stderr .= $string;
        }
    }