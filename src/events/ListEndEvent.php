<?php
    namespace unique\scraper\events;

    use unique\events\interfaces\EventObjectInterface;
    use unique\events\traits\EventObjectTrait;
    use unique\scraper\interfaces\BreakableEventInterface;
    use unique\scraper\traits\BreakableEventTrait;
    use unique\scraper\ItemCount;

    /**
     * Class ListEndEvent.
     *
     * Event triggered after the whole category page has been scrapped.
     *
     * @package unique\scraper\events
     */
    class ListEndEvent implements EventObjectInterface, BreakableEventInterface {

        use EventObjectTrait, BreakableEventTrait;

        /**
         * Information about page number, size and total amount of items.
         * @var ItemCount
         */
        protected ItemCount $item_count;

        /**
         * True, if the scraper will continue to the next page, false - otherwise.
         * @var bool
         */
        protected bool $will_continue;

        /**
         * ListEndEvent constructor.
         * @param ItemCount $result
         * @param bool $will_continue - if the scraper will continue with the category.
         */
        public function __construct( ItemCount $result, bool $will_continue ) {

            $this->item_count = $result;
            $this->will_continue = $will_continue;
        }

        /**
         * Returns information about page number, size and total amount of items.
         * @return ItemCount
         */
        public function getItemCount(): ItemCount {

            return $this->item_count;
        }

        /**
         * Returns true, if the scraper will continue to the next page.
         * @return bool
         */
        public function willContinue(): bool {

            return $this->will_continue;
        }
    }