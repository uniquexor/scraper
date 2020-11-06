<?php
    namespace unique\scraperunit\data;

    use unique\scraper\interfaces\SiteItemInterface;

    class SiteItem implements SiteItemInterface {

        protected $id;
        protected $url;

        public function setItemId( $id ) {

            $this->id = $id;
        }

        public function setUrl( string $url ) {

            $this->url = $url;
        }

        public function getItemId() {

            return $this->id;
        }

        public function getUrl(): string {

            return $this->url;
        }
    }