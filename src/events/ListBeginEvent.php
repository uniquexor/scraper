<?php
    namespace unique\scraper\events;

    use unique\events\interfaces\EventObjectInterface;
    use unique\events\traits\EventObjectTrait;
    use unique\scraper\interfaces\BreakableEventInterface;
    use unique\scraper\traits\BreakableEventTrait;

    /**
     * Class ListBeginEvent.
     * Event triggered before starting a new category page.
     *
     * @package unique\scraper\events
     */
    class ListBeginEvent implements EventObjectInterface, BreakableEventInterface {

        use EventObjectTrait, BreakableEventTrait;

        /**
         * Stores the current page number.
         * @var int|null
         */
        protected ?int $page_num = null;

        /**
         * ListBeginEvent constructor.
         * @param int|null $page_num - Current page number
         */
        public function __construct( ?int $page_num = null ) {

            $this->page_num = $page_num;
        }

        /**
         * Returns the current page number.
         * @return int|null
         */
        public function getPageNum(): ?int {

            return $this->page_num;
        }
    }