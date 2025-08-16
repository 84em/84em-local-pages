<?php
/**
 * Unit tests for Dependency Injection Container
 *
 * @package EightyFourEM\LocalPages
 */

// Load autoloader for namespaced classes
require_once dirname( __DIR__, 2 ) . '/vendor/autoload.php';
require_once dirname( __DIR__ ) . '/TestCase.php';

use EightyFourEM\LocalPages\Container;

class Test_Container extends TestCase {
    
    private $container;
    
    /**
     * Set up test environment
     */
    public function setUp(): void {
        $this->container = new Container();
    }
    
    /**
     * Test service registration and retrieval
     */
    public function test_service_registration() {
        // Register a simple service
        $this->container->register( 'test_service', function( $container ) {
            return new TestService();
        });
        
        // Get the service
        $service = $this->container->get( 'test_service' );
        
        $this->assertInstanceOf( TestService::class, $service );
    }
    
    /**
     * Test singleton pattern
     */
    public function test_singleton_pattern() {
        // Register a singleton service
        $this->container->register( 'singleton_service', function( $container ) {
            return new TestService();
        });
        
        // Get the service twice
        $service1 = $this->container->get( 'singleton_service' );
        $service2 = $this->container->get( 'singleton_service' );
        
        // They should be the same instance
        $this->assertSame( $service1, $service2 );
    }
    
    /**
     * Test direct singleton registration
     */
    public function test_direct_singleton_registration() {
        $service = new TestService();
        $service->value = 'test_value';
        
        // Register as singleton directly
        $this->container->singleton( 'direct_singleton', $service );
        
        // Get the service
        $retrieved = $this->container->get( 'direct_singleton' );
        
        $this->assertSame( $service, $retrieved );
        $this->assertEquals( 'test_value', $retrieved->value );
    }
    
    /**
     * Test service with dependencies
     */
    public function test_service_with_dependencies() {
        // Register dependency
        $this->container->register( 'dependency', function( $container ) {
            return new TestDependency();
        });
        
        // Register service that needs the dependency
        $this->container->register( 'dependent_service', function( $container ) {
            return new TestDependentService( $container->get( 'dependency' ) );
        });
        
        // Get the service
        $service = $this->container->get( 'dependent_service' );
        
        $this->assertInstanceOf( TestDependentService::class, $service );
        $this->assertInstanceOf( TestDependency::class, $service->getDependency() );
    }
    
    /**
     * Test has() method
     */
    public function test_has_method() {
        // Before registration
        $this->assertFalse( $this->container->has( 'non_existent' ) );
        
        // Register a service
        $this->container->register( 'existing_service', function( $container ) {
            return new TestService();
        });
        
        // After registration
        $this->assertTrue( $this->container->has( 'existing_service' ) );
        
        // Test with singleton
        $this->container->singleton( 'singleton_service', new TestService() );
        $this->assertTrue( $this->container->has( 'singleton_service' ) );
    }
    
    /**
     * Test make() method for creating new instances
     */
    public function test_make_method() {
        // Register a service
        $this->container->register( 'factory_service', function( $container ) {
            return new TestService();
        });
        
        // Make multiple instances
        $instance1 = $this->container->make( 'factory_service' );
        $instance2 = $this->container->make( 'factory_service' );
        
        // They should be different instances
        $this->assertNotSame( $instance1, $instance2 );
        
        // But both should be correct type
        $this->assertInstanceOf( TestService::class, $instance1 );
        $this->assertInstanceOf( TestService::class, $instance2 );
    }
    
    /**
     * Test exception for unregistered service
     */
    public function test_exception_for_unregistered_service() {
        $exceptionThrown = false;
        $exceptionMessage = '';
        
        try {
            $this->container->get( 'unregistered_service' );
        } catch ( RuntimeException $e ) {
            $exceptionThrown = true;
            $exceptionMessage = $e->getMessage();
        }
        
        $this->assertTrue( $exceptionThrown, 'Expected RuntimeException to be thrown' );
        $this->assertStringContainsString( 'Service unregistered_service is not registered', $exceptionMessage );
    }
    
    /**
     * Test exception for make with unregistered service
     */
    public function test_exception_for_make_unregistered() {
        $exceptionThrown = false;
        $exceptionMessage = '';
        
        try {
            $this->container->make( 'unregistered_service' );
        } catch ( RuntimeException $e ) {
            $exceptionThrown = true;
            $exceptionMessage = $e->getMessage();
        }
        
        $this->assertTrue( $exceptionThrown, 'Expected RuntimeException to be thrown' );
        $this->assertStringContainsString( 'Service unregistered_service is not registered', $exceptionMessage );
    }
    
    /**
     * Test circular dependency detection
     */
    public function test_circular_dependency_detection() {
        // Register services with circular dependency
        $this->container->register( 'service_a', function( $container ) {
            return new CircularServiceA( $container->get( 'service_b' ) );
        });
        
        $this->container->register( 'service_b', function( $container ) {
            return new CircularServiceB( $container->get( 'service_a' ) );
        });
        
        // This should cause infinite recursion or stack overflow
        // In a real implementation, we'd want to detect and throw a specific exception
        $this->markTestIncomplete( 'Circular dependency detection not implemented' );
    }
    
    /**
     * Test complex dependency graph
     */
    public function test_complex_dependency_graph() {
        // Register multiple interdependent services
        $this->container->register( 'logger', function( $container ) {
            return new TestLogger();
        });
        
        $this->container->register( 'database', function( $container ) {
            return new TestDatabase( $container->get( 'logger' ) );
        });
        
        $this->container->register( 'repository', function( $container ) {
            return new TestRepository( 
                $container->get( 'database' ),
                $container->get( 'logger' )
            );
        });
        
        $this->container->register( 'service', function( $container ) {
            return new TestComplexService(
                $container->get( 'repository' ),
                $container->get( 'logger' )
            );
        });
        
        // Get the top-level service
        $service = $this->container->get( 'service' );
        
        $this->assertInstanceOf( TestComplexService::class, $service );
        $this->assertInstanceOf( TestRepository::class, $service->getRepository() );
        $this->assertInstanceOf( TestLogger::class, $service->getLogger() );
    }
    
    /**
     * Test overriding registered services
     */
    public function test_overriding_services() {
        // Register initial service
        $this->container->register( 'override_test', function( $container ) {
            $service = new TestService();
            $service->value = 'original';
            return $service;
        });
        
        // Get original
        $original = $this->container->get( 'override_test' );
        $this->assertEquals( 'original', $original->value );
        
        // Override with new registration
        $this->container->register( 'override_test', function( $container ) {
            $service = new TestService();
            $service->value = 'overridden';
            return $service;
        });
        
        // Note: The instance is already cached, so we need a new container
        $newContainer = new Container();
        $newContainer->register( 'override_test', function( $container ) {
            $service = new TestService();
            $service->value = 'overridden';
            return $service;
        });
        
        $overridden = $newContainer->get( 'override_test' );
        $this->assertEquals( 'overridden', $overridden->value );
    }
    
    /**
     * Test service with closure parameters
     */
    public function test_service_with_parameters() {
        // Register service that uses container parameter
        $this->container->register( 'config', function( $container ) {
            return ['api_key' => 'test_key', 'timeout' => 30];
        });
        
        $this->container->register( 'api_client', function( $container ) {
            $config = $container->get( 'config' );
            $client = new TestApiClient();
            $client->apiKey = $config['api_key'];
            $client->timeout = $config['timeout'];
            return $client;
        });
        
        $client = $this->container->get( 'api_client' );
        
        $this->assertEquals( 'test_key', $client->apiKey );
        $this->assertEquals( 30, $client->timeout );
    }
}

/**
 * Test service classes
 */
class TestService {
    public $value = null;
}

class TestDependency {
    public $name = 'test_dependency';
}

class TestDependentService {
    private $dependency;
    
    public function __construct( $dependency ) {
        $this->dependency = $dependency;
    }
    
    public function getDependency() {
        return $this->dependency;
    }
}

class TestLogger {
    public function log( $message ) {
        // Mock implementation
    }
}

class TestDatabase {
    private $logger;
    
    public function __construct( $logger ) {
        $this->logger = $logger;
    }
}

class TestRepository {
    private $database;
    private $logger;
    
    public function __construct( $database, $logger ) {
        $this->database = $database;
        $this->logger = $logger;
    }
}

class TestComplexService {
    private $repository;
    private $logger;
    
    public function __construct( $repository, $logger ) {
        $this->repository = $repository;
        $this->logger = $logger;
    }
    
    public function getRepository() {
        return $this->repository;
    }
    
    public function getLogger() {
        return $this->logger;
    }
}

class CircularServiceA {
    private $serviceB;
    
    public function __construct( $serviceB ) {
        $this->serviceB = $serviceB;
    }
}

class CircularServiceB {
    private $serviceA;
    
    public function __construct( $serviceA ) {
        $this->serviceA = $serviceA;
    }
}

class TestApiClient {
    public $apiKey;
    public $timeout;
}