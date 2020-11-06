<?php
    namespace unique\scraper\interfaces;

    interface BreakableEventInterface {

        /**
         * Returns true if the script should skip current item, but continue with the list.
         * @return bool
         */
        public function shouldSkip(): bool;

        /**
         * Returns true, if the script should break the current list.
         * @return bool
         */
        public function shouldBreak(): bool;

        /**
         * Marks that the script should proceed with the item.
         */
        public function continue();

        /**
         * Marks that the script should skip the current item, but continue with the list.
         */
        public function skip();

        /**
         * Marks that the script should break the current list.
         */
        public function break();
    }