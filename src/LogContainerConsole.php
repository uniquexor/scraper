<?php
    namespace unique\scraper;

    use unique\scraper\interfaces\ConsoleInterface;
    use unique\scraper\interfaces\SiteItemInterface;

    class LogContainerConsole extends LogContainer {

        protected $console;

        public function __construct( ConsoleInterface $console ) {

            $this->console = $console;
        }

        /**
         * @inheritdoc
         */
        public function logListBegin( ?int $page_num ) {

            parent::logListBegin( $page_num );

            $this->console->stdout( $page_num . ': ' );
        }

        /**
         * @inheritdoc
         */
        public function logListEnd( ItemCount $results, bool $continue ) {

            parent::logListEnd( $results, $continue );

            $count = '';
            if ( $results->getNumberOfItemsInPage() !== null && $results->getTotalItems() ) {

                $cur = min( $results->getCurrentPage() * $results->getNumberOfItemsInPage(), $results->getTotalItems() );
                $count = ' (' . $cur . ' / ' . $results->getTotalItems() . ')';
            }
            $this->console->stdout( ( !$continue ? ' ...done.' : $count ) . "\r\n" );
        }

        /**
         * @inheritdoc
         */
        public function logItemEnd( ?SiteItemInterface $item, int $status ) {

            parent::logItemEnd( $item, $status );

            $this->console->stdout( self::getTextFromState( $status ) );
        }

        /**
         * @inheritdoc
         */
        public function logBreakList() {

            parent::logBreakList();

            $this->console->stdout( ' ...break' . "\r\n" );
        }

        /**
         * @inheritdoc
         */
        public function log( ...$args ) {

            parent::log( $args );

            $this->console->stdout( ...$args );
        }

        /**
         * @inheritdoc
         */
        public function logException( \Exception $exception, ?string $url ) {

            parent::logException( $exception, $url );

            $this->console->stderr( $exception->getMessage() );
        }

        /**
         * Converts a state constant to a symbol to be output to the console.
         * @param int $state - One of the {@see AbstractItemListDownloader}::STATE_* constants
         * @return string
         */
        public static function getTextFromState( int $state ): string {

            $text = $state;
            switch ( $state ) {

                case AbstractItemListDownloader::STATE_OK:
                    $text = '.';
                    break;
                case AbstractItemListDownloader::STATE_SKIP:
                    $text = 's';
                    break;
                case AbstractItemListDownloader::STATE_FAIL:
                    $text = 'f';
                    break;
                case AbstractItemListDownloader::STATE_MISSING_DATA:
                    $text = 'x';
                    break;
            }

            return $text;
        }
    }