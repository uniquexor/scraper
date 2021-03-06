<?php
    namespace unique\scraper;

    use unique\scraper\components\Request;
    use unique\scraper\interfaces\LogContainerInterface;
    use unique\scraper\interfaces\SiteItemInterface;

    /**
     * Class LogContainer.
     * A container for all of the logging handlers.
     *
     * @package unique\scraper
     */
    class LogContainer implements LogContainerInterface {

        /**
         * @var callable|null
         */
        protected $list_begin = null;

        /**
         * @var callable|null
         */
        protected $list_end = null;

        /**
         * @var callable|null
         */
        protected $item_begin = null;

        /**
         * @var callable|null
         */
        protected $item_end = null;

        /**
         * @var callable|null
         */
        protected $break_update = null;

        /**
         * @var callable|null
         */
        protected $logger = null;

        /**
         * @var callable|null
         */
        protected $exception_logger = null;

        /**
         * Sets a logger function for when the list begins processing.
         * Logger function definition:
         * ```php```
         * function ( ?int $page_num )
         * ```php```
         * Where `$page_num` is the number of current page in the list.
         *
         * @param callable|null $callable - Logger function
         * @return $this
         */
        public function setListBegin( ?callable $callable ): LogContainer {

            $this->list_begin = $callable;
            return $this;
        }

        /**
         * Sets a logger function for when the list ends processing.
         * Logger function definition:
         * ```php```
         * function ( ItemCount $results, bool $continue )
         * ```php```
         * Where if `$continue`, means that the end of the list has not been reached and it will continue on to the next page.
         *
         * @param callable|null $callable - Logger function
         * @return $this
         */
        public function setListEnd( ?callable $callable ): LogContainer {

            $this->list_end = $callable;
            return $this;
        }

        /**
         * Sets a logger function for when the item begins processing.
         * Logger function definition:
         * ```php```
         * function ( string $id, string $url )
         * ```php```
         *
         * @param callable|null $callable - Logger function
         * @return $this
         */
        public function setItemBegin( ?callable $callable ): LogContainer {

            $this->item_begin = $callable;
            return $this;
        }

        /**
         * Sets a logger function for when the item ends processing.
         * Logger function definition:
         * ```php```
         * function ( ?SiteItemInterface $item, int $status )
         * ```php```
         * Where `$item` is the item created, ready for saving (or null, if the item failed to create)
         * and `$status` is one of the {@see AbstractItemListDownloader}::STATE_* constants.
         *
         * @param callable|null $callable - Logger function
         * @return $this
         */
        public function setItemEnd( ?callable $callable ): LogContainer {

            $this->item_end = $callable;
            return $this;
        }

        /**
         * Sets a logger function for when the list of items processing breaks.
         * Logger function definition:
         * ```php```
         * function ()
         * ```php```
         *
         * @param callable|null $callable - Logger function
         * @return $this
         */
        public function setBreakUpdate( ?callable $callable ): LogContainer {

            $this->break_update = $callable;
            return $this;
        }

        /**
         * Sets a generic logger function.
         * Logger function definition:
         * ```php```
         * function ( ...$args )
         * ```php```
         *
         * Takes any number of arguments and passes them on to the set logger function.
         *
         * @param callable|null $callable - Logger function
         * @return $this
         */
        public function setLogger( ?callable $callable ): LogContainer {

            $this->logger = $callable;
            return $this;
        }

        /**
         * Sets an Exception logger function.
         * Logger function definition:
         * ```php```
         * function ( \Exception $exception, ?string $url )
         * ```php```
         *
         * Where `$exception` is the causing exception
         * and `$url` is the url of the item that's caused the exception
         *
         * @param callable|null $callable - Logger function
         * @return $this
         */
        public function setExceptionLogger( ?callable  $callable ): LogContainer {

            $this->exception_logger = $callable;
            return $this;
        }

        /**
         * Logs the begining of processing for the list of items.
         * @param int|null $page_num - The page number of the list, if applicable.
         */
        public function logListBegin( ?int $page_num ) {

            if ( is_callable( $this->list_begin ) ) {

                call_user_func( $this->list_begin, $page_num );
            }
        }

        /**
         * Logs the end of processing for the list of items.
         * @param ItemCount $results
         * @param bool $continue - if true, means that the end of the list has not been reached and will continue on to the next page.
         */
        public function logListEnd( ItemCount $results, bool $continue ) {

            if ( is_callable( $this->list_end ) ) {

                call_user_func( $this->list_end, $results, $continue );
            }
        }

        /**
         * Logs the begining of processing for the item.
         * @param string|null $id - Item ID
         * @param string|Request|null $url - Item URL
         */
        public function logItemBegin( ?string $id, $url ) {

            if ( is_callable( $this->item_begin ) ) {

                call_user_func( $this->item_begin, $id, $url );
            }
        }

        /**
         * Logs the end of processing for the list of items.
         *
         * @param SiteItemInterface $item - The created site item
         * @param int $status - One of the {@see AbstractItemListDownloader}::STATUS_* constants.
         */
        public function logItemEnd( ?SiteItemInterface $item, int $status ) {

            if ( is_callable( $this->item_end ) ) {

                call_user_func( $this->item_end, $item, $status );
            }
        }

        /**
         * Logs when the list of items processing breaks.
         */
        public function logBreakList() {

            if ( $this->break_update ) {

                call_user_func( $this->break_update );
            }
        }

        /**
         * A generic logger function. Passes all of the parameters to the handler.
         * @param mixed ...$args
         */
        public function log( ...$args ) {

            if ( $this->logger !== null ) {

                call_user_func_array( $this->logger, $args );
            }
        }

        /**
         * Logs an Exception.
         * @param \Throwable $exception - The exception thrown.
         * @param string|null $url - A causing url.
         */
        public function logException( \Throwable $exception, ?string $url ) {

            if ( $this->exception_logger ) {

                call_user_func( $this->exception_logger, $exception, $url );
            }
        }
    }