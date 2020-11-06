<?php
    namespace unique\scraperunit\helper;

    use PHPUnit\Framework\TestCase;

    /**
     * Class ClosureMocker.
     * Provides an invokable object which asserts that all parameters passed to it are as they expected.
     * Each time an object is invoked, the first array entry is removed from expectations and checked against the parameters that the invoke was passed.
     * Therefore, don't forget to assert, that the $expectations array is empty after all the calls, which ensures that the object was indeed invoked.
     *
     * @package unique\scraperunit\helper
     */
    class ClosureMocker {

        protected $test_case;
        protected $expectations;

        /**
         * ClosureMocker constructor.
         * $expectations array definition:
         * [
         *      // First invokation parameters passed as array:
         *      [ $closure_param_1, $closure_param_2, ... ],
         *
         *       // Second invokation parameters:
         *      [ $closure_param_1, $closure_param_2, ... ],
         *
         *      ...
         * ]
         *
         * @param TestCase $test_case - Testcase to assert on.
         * @param array $expectation - Expectations for the invokations
         */
        public function __construct( TestCase $test_case, array $expectation ) {

            $this->test_case = $test_case;
            $this->expectations = $expectation;
        }

        /**
         * Removes the first row from the expectations and validates it against the actual parameters.
         * @param mixed ...$args
         */
        public function __invoke( ...$args ) {

            $this->test_case->assertNotEmpty( $this->expectations );
            $expected = array_shift( $this->expectations );

            foreach ( $args as $value ) {

                $expected_value = array_shift( $expected );
                $this->test_case->assertSame( $expected_value, $value );
            }

            $this->test_case->assertEmpty( $expected );
        }

        /**
         * Asserts that all the inokations have been made and therefore no more expectations left.
         */
        public function assertExpectationsEmpty() {

            $this->test_case->assertEmpty( $this->expectations );
        }
    }