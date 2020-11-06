<?php
    namespace unique\scraper\interfaces;

    interface SiteItemInterface {

        public function setItemId( $id );
        public function setUrl( string $url );

        public function getItemId();
        public function getUrl(): string;
    }