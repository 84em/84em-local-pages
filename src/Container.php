<?php
/**
 * Dependency Injection Container
 *
 * @package EightyFourEM\LocalPages
 */

namespace EightyFourEM\LocalPages;

/**
 * Simple dependency injection container for managing class instances
 */
class Container {
    /**
     * Registered service factories
     *
     * @var array<string, callable>
     */
    private array $services = [];

    /**
     * Cached service instances
     *
     * @var array<string, mixed>
     */
    private array $instances = [];

    /**
     * Register a service factory
     *
     * @param  string  $abstract  Service identifier
     * @param  callable  $concrete  Factory function that returns the service instance
     */
    public function register( string $abstract, callable $concrete ): void {
        $this->services[ $abstract ] = $concrete;
    }

    /**
     * Register a singleton service
     *
     * @param  string  $abstract  Service identifier
     * @param  mixed  $instance  Service instance
     */
    public function singleton( string $abstract, mixed $instance ): void {
        $this->instances[ $abstract ] = $instance;
    }

    /**
     * Get a service instance
     *
     * @param  string  $abstract  Service identifier
     *
     * @return mixed Service instance
     * @throws \RuntimeException If service is not registered
     */
    public function get( string $abstract ): mixed {
        if ( ! isset( $this->instances[ $abstract ] ) ) {
            if ( ! isset( $this->services[ $abstract ] ) ) {
                throw new \RuntimeException( "Service {$abstract} is not registered." );
            }
            $this->instances[ $abstract ] = $this->services[$abstract]( $this );
        }
        return $this->instances[ $abstract ];
    }

    /**
     * Check if a service is registered
     *
     * @param  string  $abstract  Service identifier
     *
     * @return bool
     */
    public function has( string $abstract ): bool {
        return isset( $this->services[ $abstract ] ) || isset( $this->instances[ $abstract ] );
    }

    /**
     * Create a new instance without caching
     *
     * @param  string  $abstract  Service identifier
     *
     * @return mixed New service instance
     * @throws \RuntimeException If service is not registered
     */
    public function make( string $abstract ): mixed {
        if ( ! isset( $this->services[ $abstract ] ) ) {
            throw new \RuntimeException( "Service {$abstract} is not registered." );
        }
        return $this->services[$abstract]( $this );
    }
}
