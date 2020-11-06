<?php
    namespace unique\scraper\events;

    use unique\events\interfaces\EventObjectInterface;
    use unique\events\traits\EventObjectTrait;
    use unique\scraper\interfaces\SiteItemInterface;
    use unique\scraper\ItemCount;

    /**
     * Class ItemEndEvent.
     * Triggered when the handling of a site item has finished and provides created $site_item object, if it was a success.
     *
     * @package unique\scraper\events
     */
    class ItemEndEvent implements EventObjectInterface {

        use EventObjectTrait;

        /**
         * If no errors where found, provides a SiteItem for saving.
         * @var SiteItemInterface|null
         */
        protected ?SiteItemInterface $site_item = null;

        protected ItemCount $item_count;

        /**
         * One of the state constants found in {@see AbstractItemListDownloader}::STATE_*
         * @var int
         */
        protected int $state;

        /**
         * ItemEndEvent constructor.
         * @param SiteItemInterface|null $site_item - If no errors where found, provides data for item, that was scraped.
         * @param ItemCount $item_count
         * @param int $state - One of the state constants found in {@see AbstractItemListDownloader}::STATE_*
         */
        public function __construct( ?SiteItemInterface $site_item, ItemCount $item_count, int $state ) {

            $this->site_item = $site_item;
            $this->item_count = $item_count;
            $this->state = $state;
        }

        /**
         * One of the state constants found in {@see AbstractItemListDownloader}::STATE_*
         * @return int
         */
        public function getState(): int {

            return $this->state;
        }

        /**
         * Sets a new state for the item.
         * @param int $state
         */
        public function setState( int $state ) {

            $this->state = $state;
        }

        /**
         * If no errors where found, provides data for item, that was scraped.
         * @return SiteItemInterface|null
         */
        public function getSiteItem(): ?SiteItemInterface {

            return $this->site_item;
        }

        /**
         * Returns information about page number, size and total amount of items.
         * @return ItemCount
         */
        public function getItemCount(): ItemCount {

            return $this->item_count;
        }
    }