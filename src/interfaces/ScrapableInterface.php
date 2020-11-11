<?php
    namespace unique\scraper\interfaces;

    interface ScrapableInterface {

        /**
         * Scrapes the page and performs any data handling.
         */
        public function scrape();
    }