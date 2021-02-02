<?php
    namespace unique\scraper\events;

    use unique\events\interfaces\EventObjectInterface;
    use unique\events\traits\EventObjectTrait;
    use unique\scraper\components\Request;
    use unique\scraper\interfaces\BreakableEventInterface;
    use unique\scraper\interfaces\DomEventInterface;
    use unique\scraper\traits\BreakableEventTrait;
    use unique\scraper\traits\DomEventTrait;

    /**
     * Class ItemBeginEvent.
     * Triggered before beginning to scrape an item page.
     *
     * @package unique\scraper\events
     */
    class ItemBeginEvent implements EventObjectInterface, BreakableEventInterface, DomEventInterface {

        use EventObjectTrait, BreakableEventTrait, DomEventTrait;

        /**
         * Contains an item id.
         * @var int|string|null
         */
        protected $id;

        /**
         * Contains an item page url.
         * @var string|Request|null
         */
        protected $url;

        /**
         * ItemBeginEvent constructor.
         * @param int|string|null $id - Item id.
         * @param string|Request|null $url - An item page url.
         */
        public function __construct( $id, $url, \DOMElement $dom_element ) {

            $this->id = $id;
            $this->url = $url;
            $this->setDomElement( $dom_element );
        }

        /**
         * Returns an item ID.
         * @return mixed
         */
        public function getId() {

            return $this->id;
        }

        /**
         * Returns an item page url.
         * @return string|Request|null
         */
        public function getUrl() {

            return $this->url;
        }
    }