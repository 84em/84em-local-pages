<?php
/**
 * Base test case for WP-CLI testing
 *
 * @package EightyFourEM_Local_Pages
 */

/**
 * Simple test case base class that mimics PHPUnit for WP-CLI testing
 */
class TestCase {

    /**
     * Set up before each test
     */
    public function setUp(): void {
        // Override in child classes
    }

    /**
     * Tear down after each test
     */
    public function tearDown(): void {
        // Override in child classes
    }

    /**
     * Assert that two values are equal
     */
    protected function assertEquals( $expected, $actual, $message = '' ) {
        if ( $expected !== $actual ) {
            throw new Exception( $message ?: "Expected $expected but got $actual" );
        }
    }

    /**
     * Assert that a value is true
     */
    protected function assertTrue( $value, $message = '' ) {
        if ( $value !== true ) {
            throw new Exception( $message ?: "Expected true but got " . var_export( $value, true ) );
        }
    }

    /**
     * Assert that a value is false
     */
    protected function assertFalse( $value, $message = '' ) {
        if ( $value !== false ) {
            throw new Exception( $message ?: "Expected false but got " . var_export( $value, true ) );
        }
    }

    /**
     * Assert that a value is null
     */
    protected function assertNull( $value, $message = '' ) {
        if ( $value !== null ) {
            throw new Exception( $message ?: "Expected null but got " . var_export( $value, true ) );
        }
    }

    /**
     * Assert that a value is not null
     */
    protected function assertNotNull( $value, $message = '' ) {
        if ( $value === null ) {
            throw new Exception( $message ?: "Expected non-null value but got null" );
        }
    }

    /**
     * Assert that an array has a key
     */
    protected function assertArrayHasKey( $key, $array, $message = '' ) {
        if ( ! is_array( $array ) || ! array_key_exists( $key, $array ) ) {
            throw new Exception( $message ?: "Array does not have key: $key" );
        }
    }

    /**
     * Assert that values are not equal
     */
    protected function assertNotEquals( $expected, $actual, $message = '' ) {
        if ( $expected === $actual ) {
            throw new Exception( $message ?: "Expected values to be different but both are: " . var_export( $expected, true ) );
        }
    }

    /**
     * Assert that a string contains another string
     */
    protected function assertStringContainsString( $needle, $haystack, $message = '' ) {
        if ( strpos( $haystack, $needle ) === false ) {
            throw new Exception( $message ?: "String does not contain '$needle'" );
        }
    }

    /**
     * Assert that a string does not contain another string
     */
    protected function assertStringNotContainsString( $needle, $haystack, $message = '' ) {
        if ( strpos( $haystack, $needle ) !== false ) {
            throw new Exception( $message ?: "String contains '$needle' but should not" );
        }
    }

    /**
     * Assert that a value is an array
     */
    protected function assertIsArray( $value, $message = '' ) {
        if ( ! is_array( $value ) ) {
            throw new Exception( $message ?: "Expected array but got " . gettype( $value ) );
        }
    }

    /**
     * Assert that a value is a string
     */
    protected function assertIsString( $value, $message = '' ) {
        if ( ! is_string( $value ) ) {
            throw new Exception( $message ?: "Expected string but got " . gettype( $value ) );
        }
    }

    /**
     * Assert that an array has a specific count
     */
    protected function assertCount( $expected, $array, $message = '' ) {
        if ( ! is_array( $array ) && ! ( $array instanceof Countable ) ) {
            throw new Exception( "Value is not countable" );
        }
        $actual = count( $array );
        if ( $actual !== $expected ) {
            throw new Exception( $message ?: "Expected count $expected but got $actual" );
        }
    }

    /**
     * Assert that a string matches a regular expression
     */
    protected function assertMatchesRegularExpression( $pattern, $string, $message = '' ) {
        if ( ! preg_match( $pattern, $string ) ) {
            throw new Exception( $message ?: "String does not match pattern: $pattern" );
        }
    }

    /**
     * Assert that an array contains a value
     */
    protected function assertContains( $needle, $haystack, $message = '' ) {
        if ( ! in_array( $needle, $haystack, true ) ) {
            throw new Exception( $message ?: "Array does not contain value: " . var_export( $needle, true ) );
        }
    }

    /**
     * Assert that an array does not contain a value
     */
    protected function assertNotContains( $needle, $haystack, $message = '' ) {
        if ( in_array( $needle, $haystack, true ) ) {
            throw new Exception( $message ?: "Array contains value but should not: " . var_export( $needle, true ) );
        }
    }

    /**
     * Assert that two values are identical (===)
     */
    protected function assertSame( $expected, $actual, $message = '' ) {
        if ( $expected !== $actual ) {
            throw new Exception( $message ?: "Values are not identical" );
        }
    }

    /**
     * Assert that a string starts with another string
     */
    protected function assertStringStartsWith( $prefix, $string, $message = '' ) {
        if ( strpos( $string, $prefix ) !== 0 ) {
            throw new Exception( $message ?: "String does not start with: $prefix" );
        }
    }

    /**
     * Assert that a string ends with another string
     */
    protected function assertStringEndsWith( $suffix, $string, $message = '' ) {
        if ( substr( $string, -strlen( $suffix ) ) !== $suffix ) {
            throw new Exception( $message ?: "String does not end with: $suffix" );
        }
    }

    /**
     * Assert that a value is empty
     */
    protected function assertEmpty( $value, $message = '' ) {
        if ( ! empty( $value ) ) {
            throw new Exception( $message ?: "Value is not empty" );
        }
    }

    /**
     * Assert that a value is not empty
     */
    protected function assertNotEmpty( $value, $message = '' ) {
        if ( empty( $value ) ) {
            throw new Exception( $message ?: "Value is empty" );
        }
    }

    /**
     * Assert that a value is greater than another
     */
    protected function assertGreaterThan( $expected, $actual, $message = '' ) {
        if ( $actual <= $expected ) {
            throw new Exception( $message ?: "Expected value greater than $expected but got $actual" );
        }
    }

    /**
     * Assert that a value is less than another
     */
    protected function assertLessThan( $expected, $actual, $message = '' ) {
        if ( $actual >= $expected ) {
            throw new Exception( $message ?: "Expected value less than $expected but got $actual" );
        }
    }

    /**
     * Assert that a value is an instance of a class
     */
    protected function assertInstanceOf( $expected, $actual, $message = '' ) {
        if ( ! ( $actual instanceof $expected ) ) {
            $actualType = is_object( $actual ) ? get_class( $actual ) : gettype( $actual );
            throw new Exception( $message ?: "Expected instance of $expected but got $actualType" );
        }
    }

    /**
     * Assert that a file exists
     */
    protected function assertFileExists( $path, $message = '' ) {
        if ( ! file_exists( $path ) ) {
            throw new Exception( $message ?: "File does not exist: $path" );
        }
    }
}
