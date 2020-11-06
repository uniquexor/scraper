<?php
    namespace unique\scraperunit\data;

    use Symfony\Component\DomCrawler\Crawler;
    use unique\scraper\AbstractItemDownloader;
    use unique\scraper\AbstractItemListDownloader;

    class ItemListDownloader extends AbstractItemListDownloader {

        public function getListUrl( ?int $page_num ): string {

            return '';
        }

        public function getTotalItems( Crawler $doc ): ?int {

            return 0;
        }

        public function getItems( Crawler $doc ): iterable {
            // TODO: Implement getItems() method.
        }

        public function getItemUrl( \DOMElement $item ): ?string {
            // TODO: Implement getItemUrl() method.
        }

        public function getItemId( string $url, \DOMElement $item ): string {
            // TODO: Implement getItemId() method.
        }

        public function getItemDownloader( string $url, string $id ): ?AbstractItemDownloader {
            // TODO: Implement getItemDownloader() method.
        }

        protected function getNumberOfItemsInPage( Crawler $doc ): ?int {

            return 0;
        }
    }