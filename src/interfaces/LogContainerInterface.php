<?php
    namespace unique\scraper\interfaces;

    use unique\scraper\ItemCount;

    interface LogContainerInterface {

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
        public function setListBegin( ?callable $callable ): LogContainerInterface;

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
        public function setListEnd( ?callable $callable ): LogContainerInterface;

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
        public function setItemBegin( ?callable $callable ): LogContainerInterface;

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
        public function setItemEnd( ?callable $callable ): LogContainerInterface;

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
        public function setBreakUpdate( ?callable $callable ): LogContainerInterface;

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
        public function setLogger( ?callable $callable ): LogContainerInterface;

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
        public function setExceptionLogger( ?callable  $callable ): LogContainerInterface;

        /**
         * Logs the begining of processing for the list of items.
         * @param int|null $page_num - The page number of the list, if applicable.
         */
        public function logListBegin( ?int $page_num );

        /**
         * Logs the end of processing for the list of items.
         * @param ItemCount $results
         * @param bool $continue - if true, means that the end of the list has not been reached and will continue on to the next page.
         */
        public function logListEnd( ItemCount $results, bool $continue );

        /**
         * Logs the begining of processing for the item.
         * @param string|null $id - Item ID
         * @param string|null $url - Item URL
         */
        public function logItemBegin( ?string $id, ?string $url );

        /**
         * Logs the end of processing for the list of items.
         *
         * @param SiteItemInterface $item - The created site item
         * @param int $status - One of the {@see AbstractItemListDownloader}::STATUS_* constants.
         */
        public function logItemEnd( ? SiteItemInterface $item, int $status );

        /**
         * Logs when the list of items processing breaks.
         */
        public function logBreakList();

        /**
         * A generic logger function. Passes all of the parameters to the handler.
         * @param mixed ...$args
         */
        public function log( ...$args );

        /**
         * Logs an Exception.
         * @param \Throwable $exception - The exception thrown.
         * @param string|null $url - A causing url.
         */
        public function logException( \Throwable $exception, ?string $url );
    }