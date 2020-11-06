<?php
    namespace unique\scraper\events;

    use unique\events\interfaces\EventObjectInterface;
    use unique\events\traits\EventObjectTrait;

    /**
     * Class BreakListEvent.
     * Triggered when handling of the list is broken. For instance, if ItemBeginEvent returns break.
     *
     * @package unique\scraper\events
     */
    class BreakListEvent implements EventObjectInterface {

        use EventObjectTrait;

        /**
         * An event, that's causing to break the list, if any.
         * @var EventObjectInterface|null
         */
        protected ?EventObjectInterface $causing_event = null;

        /**
         * BreakListEvent constructor.
         * @param EventObjectInterface|null $causing_event - An event, that's causing to break the list, if any.
         */
        public function __construct( ?EventObjectInterface $causing_event = null ) {

            $this->causing_event = $causing_event;
        }

        /**
         * Returns an event, that's causing to break the list, if any.
         * @return EventObjectInterface|null
         */
        public function getCausingEvent(): ?EventObjectInterface {

            return $this->causing_event;
        }
    }