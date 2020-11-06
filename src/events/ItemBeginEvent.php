<?php
    namespace unique\scraper\events;

    use unique\events\interfaces\EventObjectInterface;
    use unique\events\traits\EventObjectTrait;
    use unique\scraper\interfaces\BreakableEventInterface;
    use unique\scraper\traits\BreakableEventTrait;

    /**
     * Class ItemBeginEvent.
     * Triggered before beginning to scrape an item page.
     *
     * @package unique\scraper\events
     */
    class ItemBeginEvent implements EventObjectInterface, BreakableEventInterface {

        use EventObjectTrait, BreakableEventTrait;

        /**
         * Contains an item id.
         * @var int|string|null
         */
        protected $id;

        /**
         * Contains an item page url.
         * @var string|null
         */
        protected $url;

        /**
         * ItemBeginEvent constructor.
         * @param int|string|null $id - Item id.
         * @param string|null $url - An item page url.
         */
        public function __construct( $id, $url ) {

            $this->id = $id;
            $this->url = $url;
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
         * @return mixed
         */
        public function getUrl() {

            return $this->url;
        }
    }