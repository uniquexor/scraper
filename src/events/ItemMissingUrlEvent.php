<?php
    namespace unique\scraper\events;

    use unique\events\interfaces\EventObjectInterface;
    use unique\events\traits\EventObjectTrait;
    use unique\scraper\AbstractItemListDownloader;

    /**
     * Class ItemMissingUrlEvent.
     * Triggered when while processing a DOMElement, that's supposed to return an item url, got null.
     * Handler can set the `url` attribute, to continue item processing.
     *
     * @package unique\scraper\events
     */
    class ItemMissingUrlEvent implements EventObjectInterface {

        use EventObjectTrait;

        /**
         * DOM element that had to return an item url.
         * @var \DOMElement|null
         */
        protected ?\DOMElement $item = null;

        /**
         * Stores the new url, for item processing.
         * @var string|null
         */
        protected ?string $url = null;

        /**
         * ItemMissingUrlEvent constructor.
         * @param \DOMElement|null $item - DOM element that had to return an item url.
         */
        public function __construct( ?\DOMElement $item = null ) {

            $this->item = $item;
        }

        /**
         * Returns the new url for item processing.
         * @return string|null
         */
        public function getUrl(): ?string {

            return $this->url;
        }

        /**
         * Sets the new URL for item processing.
         * @param string|null $url
         */
        public function setUrl( ?string $url ): void {

            $this->url = $url;
        }

        /**
         * Returns one of the DOMElement objects, that was returned by the {@see AbstractItemListDownloader::getItems()} method.
         * @return \DOMElement|null
         */
        protected function getItem() {

            return $this->item;
        }
    }