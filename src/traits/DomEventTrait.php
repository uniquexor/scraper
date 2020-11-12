<?php
    namespace unique\scraper\traits;

    /**
     * Trait DomEventTrait
     * Provides \DOMElement object assignment for the event.
     * @package unique\scraper\traits
     */
    trait DomEventTrait {

        /**
         * Assigned \DOMElement object.
         * @var \DOMElement
         */
        protected \DOMElement $dom_element;

        /**
         * Returns the assigned \DOMElement object.
         * @return \DOMElement
         */
        public function getDomElement(): \DOMElement {

            return $this->dom_element;
        }

        /**
         * Assigns \DOMElement object.
         * @param \DOMElement $dom_element
         */
        public function setDomElement( \DOMElement $dom_element ): void {

            $this->dom_element = $dom_element;
        }
    }