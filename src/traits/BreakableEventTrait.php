<?php
    namespace unique\scraper\traits;

    /**
     * Trait BreakableEventTrait.
     * Provides functionality of specifying how the handling of the list and list item should proceed.
     * Can be set to continue:
     * - proceed as normal
     * - skip current item, but continue with the list
     * - break the list
     *
     * @package unique\scraper\traits
     */
    trait BreakableEventTrait {

        /**
         * Marks the current flow control.
         * 0 - continue with the item
         * 1 - skip item, continue with the list
         * 2 - break the list.
         *
         * @var int
         */
        private int $flow_control = 0;

        /**
         * Returns true if the script should skip current item, but continue with the list.
         * @return bool
         */
        public function shouldSkip(): bool {

            return $this->flow_control === 1;
        }

        /**
         * Returns true, if the script should break the current list.
         * @return bool
         */
        public function shouldBreak(): bool {

            return $this->flow_control === 2;
        }

        /**
         * Marks that the script should proceed with the item.
         */
        public function continue() {

            $this->flow_control = 0;
        }

        /**
         * Marks that the script should skip the current item, but continue with the list.
         */
        public function skip() {

            $this->flow_control = 1;
        }

        /**
         * Marks that the script should break the current list.
         */
        public function break() {

            $this->flow_control = 2;
        }
    }