<?php
    namespace unique\scraper\interfaces;

    /**
     * Interface DomEventInterface
     * An interface for the Event, marking that it has a corresponding DOMElement.
     *
     * @package unique\scraper\interfaces
     */
    interface DomEventInterface {

        /**
         * Returns the corresponding \DOMElement
         * @return \DOMElement
         */
        public function getDomElement(): \DOMElement;

        /**
         * Sets the corresponding \DOMElement.
         * @param \DOMElement $dom_element
         */
        public function setDomElement( \DOMElement $dom_element ): void;
    }