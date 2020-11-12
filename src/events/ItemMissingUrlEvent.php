<?php
    namespace unique\scraper\events;

    use unique\events\interfaces\EventObjectInterface;
    use unique\events\traits\EventObjectTrait;
    use unique\scraper\AbstractItemListDownloader;
    use unique\scraper\interfaces\DomEventInterface;
    use unique\scraper\traits\DomEventTrait;

    /**
     * Class ItemMissingUrlEvent.
     * Triggered when while processing a DOMElement, that's supposed to return an item url, got null.
     * Handler can set the `url` attribute, to continue item processing.
     *
     * @package unique\scraper\events
     */
    class ItemMissingUrlEvent implements EventObjectInterface, DomEventInterface {

        use EventObjectTrait, DomEventTrait;

        /**
         * Stores the new url, for item processing.
         * @var string|null
         */
        protected ?string $url = null;

        /**
         * ItemMissingUrlEvent constructor.
         * @param \DOMElement $item - DOM element that had to return an item url.
         */
        public function __construct( \DOMElement $item ) {

            $this->setDomElement( $item );
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
    }