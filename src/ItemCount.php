<?php
    namespace unique\scraper;

    /**
     * Class ItemCount.
     * Stores the number of items in a list, the current page number and the number items in a page.
     *
     * @package unique\scraper
     */
    class ItemCount {

        protected ?int $total_items = null;
        protected ?int $current_page = null;
        protected ?int $page_size = null;

        /**
         * Sets the total number of items.
         * @param int|null $total_items
         */
        public function setTotalItems( ?int $total_items ) {

            $this->total_items = $total_items;
        }

        /**
         * Returns the total number of items.
         * @return int|null
         */
        public function getTotalItems(): ?int {

            return $this->total_items;
        }

        /**
         * Sets the current page number.
         * @param int|null $page
         */
        public function setCurrentPage( ?int $page ) {

            $this->current_page = $page;
        }

        /**
         * Returns the curreng page number.
         * @return int|null
         */
        public function getCurrentPage(): ?int {

            return $this->current_page;
        }

        /**
         * Sets the number of items in a page.
         * @param int|null $page_size
         */
        public function setNumberOfItemsInPage( ?int $page_size = null ) {

            $this->page_size = $page_size;
        }

        /**
         * Returns the number of items in a page.
         * @return int|null
         */
        public function getNumberOfItemsInPage(): ?int {

            return $this->page_size;
        }
    }