<?php
/**
 * Health Check Endpoint
 *
 * @package EightyFourEM\LocalPages\Api
 */

namespace EightyFourEM\LocalPages\Api;

use WP_REST_Response;
use WP_REST_Request;

/**
 * Provides health check endpoint for deployment verification
 */
class HealthCheckEndpoint {
    /**
     * API namespace
     */
    private const NAMESPACE = '84em-local-pages/v1';

    /**
     * Register the health check endpoint
     */
    public function register(): void {
        add_action( 'rest_api_init', [ $this, 'registerRoutes' ] );
    }

    /**
     * Register REST API routes
     */
    public function registerRoutes(): void {
        register_rest_route( self::NAMESPACE, '/health', [
            'methods'             => 'GET',
            'callback'            => [ $this, 'healthCheck' ],
            'permission_callback' => '__return_true',
        ] );
    }

    /**
     * Health check endpoint callback
     *
     * @param  WP_REST_Request  $request  The request object
     *
     * @return WP_REST_Response
     */
    public function healthCheck( WP_REST_Request $request ): WP_REST_Response {
        return new WP_REST_Response( [
            'status' => 'ok'
        ], 200 );
    }
}
