<?php
/**
 * Plugin Name: 84EM Local Pages Generator
 * Description: Generates SEO-optimized Local Pages for each US state using Claude AI
 * Version: 1.0.0
 * Author: 84EM
 * Requires at least: 6.8
 * Requires PHP: 8.2
 * Text Domain: 84em-local-pages
 */

defined( 'ABSPATH' ) or die;

const EIGHTYFOUREM_LOCAL_PAGES_VERSION = '1.0.0';

class EightyFourEM_Local_Pages_Generator {

    private ?string $claude_api_key;
    private ?array $us_states_data;
    private ?array $service_keywords;

    /**
     * Constructor - initializes the plugin if requirements are met
     */
    public function __construct() {
        // Check requirements
        if ( ! $this->check_requirements() ) {
            return;
        }

        $this->init();
        $this->setup_hooks();
        $this->init_data();
    }

    /**
     * Initializes the plugin by setting up hooks, actions, and WP-CLI commands.
     *
     * @return void
     */
    private function init(): void {
        // Initialize plugin
        add_action( 'init', [ $this, 'register_custom_post_type' ] );
        add_action( 'init', [ $this, 'add_rewrite_rules' ] );
        add_filter( 'post_type_link', [ $this, 'remove_slug_from_permalink' ], 10, 2 );

        // WP-CLI command registration
        if ( defined( 'WP_CLI' ) && WP_CLI ) {
            WP_CLI::add_command( '84em local-pages', [ $this, 'wp_cli_handler' ] );
        }
    }

    /**
     * Checks if the system meets the required WordPress and PHP versions.
     *
     * @return bool True if requirements are met, false otherwise.
     */
    private function check_requirements(): bool {
        global $wp_version;

        // Check WordPress version
        if ( version_compare( $wp_version, '6.8', '<' ) ) {
            add_action( 'admin_notices', function () {
                echo '<div class="notice notice-error"><p><strong>84EM Local Pages Generator:</strong> This plugin requires WordPress 6.8 or higher. You are running version ' . get_bloginfo( 'version' ) . '. The plugin has been deactivated.</p></div>';
            } );
            deactivate_plugins( plugin_basename( __FILE__ ) );
            return false;
        }

        // Check PHP version
        if ( version_compare( PHP_VERSION, '8.2', '<' ) ) {
            add_action( 'admin_notices', function () {
                echo '<div class="notice notice-error"><p><strong>84EM Local Pages Generator:</strong> This plugin requires PHP 8.2 or higher. You are running version ' . PHP_VERSION . '. The plugin has been deactivated.</p></div>';
            } );
            deactivate_plugins( plugin_basename( __FILE__ ) );
            return false;
        }

        // Check site URL
        $site_url = get_site_url();
        if ( $site_url !== 'https://84em.com' ) {
            add_action( 'admin_notices', function () {
                echo '<div class="notice notice-error"><p><strong>84EM Local Pages Generator:</strong> This plugin can only be used on 84em.com. The plugin has been deactivated.  Want a version that you can run on your own site? <a href="https://84em.com/contact/" target="_blank">Contact 84EM</a>.</p></div>';
            } );
            deactivate_plugins( plugin_basename( __FILE__ ) );
            return false;
        }

        return true;
    }

    /**
     * Sets up activation and deactivation hooks for the plugin.
     *
     * @return void
     */
    private function setup_hooks(): void {
        register_activation_hook( __FILE__, [ $this, 'activate' ] );
        register_deactivation_hook( __FILE__, [ $this, 'deactivate' ] );
    }

    /**
     * Initializes the data for US states and their six largest cities.
     *
     * @return void
     */
    private function init_data(): void {
        $this->us_states_data = [
            'Alabama'        => [ 'cities' => [ 'Birmingham', 'Montgomery', 'Mobile', 'Huntsville', 'Tuscaloosa', 'Hoover' ] ],
            'Alaska'         => [ 'cities' => [ 'Anchorage', 'Fairbanks', 'Juneau', 'Sitka', 'Ketchikan', 'Wasilla' ] ],
            'Arizona'        => [ 'cities' => [ 'Phoenix', 'Tucson', 'Mesa', 'Chandler', 'Scottsdale', 'Glendale' ] ],
            'Arkansas'       => [ 'cities' => [ 'Little Rock', 'Fayetteville', 'Fort Smith', 'Springdale', 'Jonesboro', 'North Little Rock' ] ],
            'California'     => [ 'cities' => [ 'Los Angeles', 'San Diego', 'San Jose', 'San Francisco', 'Fresno', 'Sacramento' ] ],
            'Colorado'       => [ 'cities' => [ 'Denver', 'Colorado Springs', 'Aurora', 'Fort Collins', 'Lakewood', 'Thornton' ] ],
            'Connecticut'    => [ 'cities' => [ 'Bridgeport', 'New Haven', 'Hartford', 'Stamford', 'Waterbury', 'Norwalk' ] ],
            'Delaware'       => [ 'cities' => [ 'Wilmington', 'Dover', 'Newark', 'Middletown', 'Smyrna', 'Milford' ] ],
            'Florida'        => [ 'cities' => [ 'Jacksonville', 'Miami', 'Tampa', 'Orlando', 'St. Petersburg', 'Hialeah' ] ],
            'Georgia'        => [ 'cities' => [ 'Atlanta', 'Augusta', 'Columbus', 'Macon', 'Savannah', 'Athens' ] ],
            'Hawaii'         => [ 'cities' => [ 'Honolulu', 'East Honolulu', 'Pearl City', 'Hilo', 'Waipahu', 'Kailua' ] ],
            'Idaho'          => [ 'cities' => [ 'Boise', 'Meridian', 'Nampa', 'Idaho Falls', 'Pocatello', 'Caldwell' ] ],
            'Illinois'       => [ 'cities' => [ 'Chicago', 'Aurora', 'Peoria', 'Rockford', 'Joliet', 'Naperville' ] ],
            'Indiana'        => [ 'cities' => [ 'Indianapolis', 'Fort Wayne', 'Evansville', 'South Bend', 'Carmel', 'Fishers' ] ],
            'Iowa'           => [ 'cities' => [ 'Des Moines', 'Cedar Rapids', 'Davenport', 'Sioux City', 'Iowa City', 'Waterloo' ] ],
            'Kansas'         => [ 'cities' => [ 'Wichita', 'Overland Park', 'Kansas City', 'Olathe', 'Topeka', 'Lawrence' ] ],
            'Kentucky'       => [ 'cities' => [ 'Louisville', 'Lexington', 'Bowling Green', 'Owensboro', 'Covington', 'Hopkinsville' ] ],
            'Louisiana'      => [ 'cities' => [ 'New Orleans', 'Baton Rouge', 'Shreveport', 'Lafayette', 'Lake Charles', 'Kenner' ] ],
            'Maine'          => [ 'cities' => [ 'Portland', 'Lewiston', 'Bangor', 'South Portland', 'Auburn', 'Biddeford' ] ],
            'Maryland'       => [ 'cities' => [ 'Baltimore', 'Frederick', 'Rockville', 'Gaithersburg', 'Bowie', 'Hagerstown' ] ],
            'Massachusetts'  => [ 'cities' => [ 'Boston', 'Worcester', 'Springfield', 'Cambridge', 'Lowell', 'Brockton' ] ],
            'Michigan'       => [ 'cities' => [ 'Detroit', 'Grand Rapids', 'Warren', 'Sterling Heights', 'Lansing', 'Ann Arbor' ] ],
            'Minnesota'      => [ 'cities' => [ 'Minneapolis', 'Saint Paul', 'Rochester', 'Duluth', 'Bloomington', 'Brooklyn Park' ] ],
            'Mississippi'    => [ 'cities' => [ 'Jackson', 'Gulfport', 'Southaven', 'Hattiesburg', 'Biloxi', 'Meridian' ] ],
            'Missouri'       => [ 'cities' => [ 'Kansas City', 'Saint Louis', 'Springfield', 'Columbia', 'Independence', 'Lee\'s Summit' ] ],
            'Montana'        => [ 'cities' => [ 'Billings', 'Missoula', 'Great Falls', 'Bozeman', 'Butte', 'Helena' ] ],
            'Nebraska'       => [ 'cities' => [ 'Omaha', 'Lincoln', 'Bellevue', 'Grand Island', 'Kearney', 'Fremont' ] ],
            'Nevada'         => [ 'cities' => [ 'Las Vegas', 'Henderson', 'Reno', 'North Las Vegas', 'Sparks', 'Carson City' ] ],
            'New Hampshire'  => [ 'cities' => [ 'Manchester', 'Nashua', 'Concord', 'Derry', 'Rochester', 'Salem' ] ],
            'New Jersey'     => [ 'cities' => [ 'Newark', 'Jersey City', 'Paterson', 'Elizabeth', 'Edison', 'Woodbridge' ] ],
            'New Mexico'     => [ 'cities' => [ 'Albuquerque', 'Las Cruces', 'Rio Rancho', 'Santa Fe', 'Roswell', 'Farmington' ] ],
            'New York'       => [ 'cities' => [ 'New York City', 'Buffalo', 'Yonkers', 'Albany', 'Syracuse', 'Rochester' ] ],
            'North Carolina' => [ 'cities' => [ 'Charlotte', 'Raleigh', 'Greensboro', 'Durham', 'Winston-Salem', 'Fayetteville' ] ],
            'North Dakota'   => [ 'cities' => [ 'Fargo', 'Bismarck', 'Grand Forks', 'Minot', 'West Fargo', 'Williston' ] ],
            'Ohio'           => [ 'cities' => [ 'Columbus', 'Cleveland', 'Cincinnati', 'Toledo', 'Akron', 'Dayton' ] ],
            'Oklahoma'       => [ 'cities' => [ 'Oklahoma City', 'Tulsa', 'Norman', 'Broken Arrow', 'Lawton', 'Edmond' ] ],
            'Oregon'         => [ 'cities' => [ 'Portland', 'Salem', 'Eugene', 'Gresham', 'Hillsboro', 'Bend' ] ],
            'Pennsylvania'   => [ 'cities' => [ 'Philadelphia', 'Pittsburgh', 'Allentown', 'Erie', 'Reading', 'Scranton' ] ],
            'Rhode Island'   => [ 'cities' => [ 'Providence', 'Warwick', 'Cranston', 'Pawtucket', 'East Providence', 'Woonsocket' ] ],
            'South Carolina' => [ 'cities' => [ 'Charleston', 'Columbia', 'North Charleston', 'Mount Pleasant', 'Rock Hill', 'Greenville' ] ],
            'South Dakota'   => [ 'cities' => [ 'Sioux Falls', 'Rapid City', 'Aberdeen', 'Brookings', 'Watertown', 'Mitchell' ] ],
            'Tennessee'      => [ 'cities' => [ 'Nashville', 'Memphis', 'Knoxville', 'Chattanooga', 'Clarksville', 'Murfreesboro' ] ],
            'Texas'          => [ 'cities' => [ 'Houston', 'San Antonio', 'Dallas', 'Austin', 'Fort Worth', 'El Paso' ] ],
            'Utah'           => [ 'cities' => [ 'Salt Lake City', 'West Valley City', 'Provo', 'West Jordan', 'Orem', 'Sandy' ] ],
            'Vermont'        => [ 'cities' => [ 'Burlington', 'Essex', 'South Burlington', 'Colchester', 'Rutland', 'Montpelier' ] ],
            'Virginia'       => [ 'cities' => [ 'Virginia Beach', 'Norfolk', 'Chesapeake', 'Richmond', 'Newport News', 'Alexandria' ] ],
            'Washington'     => [ 'cities' => [ 'Seattle', 'Spokane', 'Tacoma', 'Vancouver', 'Bellevue', 'Kent' ] ],
            'West Virginia'  => [ 'cities' => [ 'Charleston', 'Huntington', 'Morgantown', 'Parkersburg', 'Wheeling', 'Martinsburg' ] ],
            'Wisconsin'      => [ 'cities' => [ 'Milwaukee', 'Madison', 'Green Bay', 'Kenosha', 'Racine', 'Appleton' ] ],
            'Wyoming'        => [ 'cities' => [ 'Cheyenne', 'Casper', 'Laramie', 'Gillette', 'Rock Springs', 'Sheridan' ] ],
        ];

        // Keywords extracted from 84EM services
        $this->service_keywords = [
            'WordPress development',
            'custom plugin development',
            'API integrations',
            'security audits',
            'white-label development',
            'WordPress maintenance',
            'WordPress support',
            'data migration',
            'platform transfers',
            'WordPress troubleshooting',
            'custom WordPress themes',
            'WordPress security',
            'web development',
            'WordPress migrations',
            'digital agency services',
            'WordPress plugin development',
        ];
    }

    /**
     * Registers the 'local' custom post type for local pages
     *
     * @return void
     */
    public function register_custom_post_type(): void {
        $args = [
            'labels'             => [
                'name'               => 'Local Pages',
                'singular_name'      => 'Local Page',
                'menu_name'          => 'Local Pages',
                'add_new'            => 'Add New Local Page',
                'add_new_item'       => 'Add New Local Page',
                'edit_item'          => 'Edit Local Page',
                'new_item'           => 'New Local Page',
                'view_item'          => 'View Local Page',
                'search_items'       => 'Search Local Pages',
                'not_found'          => 'No Local Pages found',
                'not_found_in_trash' => 'No Local Pages found in trash',
            ],
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'show_in_rest'       => true,
            'query_var'          => true,
            'rewrite'            => false,
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => 5,
            'menu_icon'          => 'dashicons-location-alt',
            'supports'           => [ 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields' ],
        ];

        register_post_type( 'local', $args );

        // Flush rewrite rules on activation
        if ( get_option( '84em_local_pages_flush_rewrite_rules' ) ) {
            flush_rewrite_rules();
            delete_option( '84em_local_pages_flush_rewrite_rules' );
        }
    }

    /**
     * Plugin activation hook - registers post type and flushes rewrite rules
     *
     * @return void
     */
    public function activate(): void {
        $this->register_custom_post_type();
        add_option( '84em_local_pages_flush_rewrite_rules', true );
        flush_rewrite_rules();
    }

    /**
     * Plugin deactivation hook - flushes rewrite rules
     *
     * @return void
     */
    public function deactivate(): void {
        flush_rewrite_rules();
    }

    /**
     * Adds custom rewrite rules for clean local page URLs
     *
     * @return void
     */
    public function add_rewrite_rules(): void {
        add_rewrite_rule( '^wordpress-development-services-([a-zA-Z-]+)/?$', 'index.php?post_type=local&name=$matches[1]', 'top' );
    }

    /**
     * Removes post type slug from local page permalinks
     *
     * @param  string  $post_link  The original post link
     * @param  WP_Post  $post  The post object
     *
     * @return string Modified permalink without slug
     */
    public function remove_slug_from_permalink( string $post_link, \WP_Post $post ): string {
        if ( 'local' === $post->post_type && 'publish' === $post->post_status ) {
            $post_link = home_url( '/' . $post->post_name . '/' );
        }

        return $post_link;
    }

    /**
     * Main WP-CLI command handler for all plugin commands
     *
     * @param  array  $args  Positional arguments
     * @param  array  $assoc_args  Associative arguments (flags)
     *
     * @return void
     * @throws \Random\RandomException
     */
    public function wp_cli_handler( array $args, array $assoc_args ): void {

        // Handle API key configuration
        if ( isset( $assoc_args['set-api-key'] ) ) {
            $this->prompt_and_set_api_key();
            return;
        }

        // Handle API key validation
        if ( isset( $assoc_args['validate-api-key'] ) ) {
            $this->validate_stored_api_key();
            return;
        }

        // Handle sitemap generation (doesn't require API key)
        if ( isset( $assoc_args['generate-sitemap'] ) ) {
            $this->generate_sitemap();
            return;
        }

        // Handle index page generation (doesn't require API key)
        if ( isset( $assoc_args['generate-index'] ) ) {
            $this->generate_index_page();
            return;
        }

        // Load API key using secure storage
        $this->claude_api_key = $this->get_secure_claude_api_key();

        if ( empty( $this->claude_api_key ) ) {
            WP_CLI::error( 'Claude API key not found. Please set it first using --set-api-key' );
            return;
        }

        // Handle delete command
        if ( isset( $assoc_args['delete'] ) ) {
            $this->handle_delete_command( $assoc_args );
            return;
        }

        // Handle update command
        if ( isset( $assoc_args['update'] ) ) {
            $this->handle_update_command( $assoc_args );
            return;
        }

        // Handle state-specific command
        if ( isset( $assoc_args['state'] ) ) {
            $this->handle_state_command( $assoc_args );
            return;
        }

        // Default: show help
        $this->show_help();
    }

    /**
     * Securely encrypts and stores Claude API key using WordPress salts
     *
     * @param  string  $api_key  The Claude API key to encrypt and store
     *
     * @return void
     * @throws \Random\RandomException
     */
    private function set_claude_api_key( string $api_key ): void {
        $sanitized_key = sanitize_text_field( $api_key );

        // Create encryption key using WordPress salts with fallbacks
        $encryption_key = $this->get_encryption_key();

        // Generate cryptographically secure IV
        $iv = random_bytes( 16 );

        // Encrypt the API key using AES-256-CBC
        $encrypted_key = openssl_encrypt( $sanitized_key, 'AES-256-CBC', $encryption_key, 0, $iv );

        // Store only encrypted data - no plaintext
        update_option( '84em_claude_api_key_encrypted', base64_encode( $encrypted_key ) );
        update_option( '84em_claude_api_key_iv', base64_encode( $iv ) );

        WP_CLI::success( 'Claude API key securely encrypted using WordPress salts and AES-256-CBC.' );
    }

    /**
     * Prompts user for API key input securely without shell history
     *
     * @return void
     * @throws \Random\RandomException
     */
    private function prompt_and_set_api_key(): void {
        WP_CLI::line( 'Setting Claude API Key' );
        WP_CLI::line( '=====================' );
        WP_CLI::line( '' );
        WP_CLI::line( 'For security reasons, please paste your Claude API key when prompted.' );
        WP_CLI::line( 'The key will not be visible as you type and will not appear in your shell history.' );
        WP_CLI::line( '' );

        // Disable echo for secure input
        if ( function_exists( 'system' ) ) {
            system( 'stty -echo' );
        }

        WP_CLI::out( 'Paste your Claude API key: ' );
        $handle  = fopen( 'php://stdin', 'r' );
        $api_key = trim( fgets( $handle ) );
        fclose( $handle );

        // Re-enable echo
        if ( function_exists( 'system' ) ) {
            system( 'stty echo' );
        }

        WP_CLI::line( '' ); // New line after hidden input

        if ( empty( $api_key ) ) {
            WP_CLI::error( 'No API key provided. Operation cancelled.' );
            return;
        }

        // Validate API key format (Claude keys start with 'sk-ant-')
        if ( ! str_starts_with( $api_key, 'sk-ant-' ) ) {
            WP_CLI::warning( 'API key format may be invalid. Claude API keys typically start with "sk-ant-".' );
            if ( ! WP_CLI::confirm( 'Continue anyway?' ) ) {
                WP_CLI::line( 'Operation cancelled.' );
                return;
            }
        }

        // Validate the API key before storing
        if ( ! $this->validate_claude_api_key( $api_key ) ) {
            WP_CLI::error( 'API key validation failed. Please check your key and try again.' );
            return;
        }

        $this->set_claude_api_key( $api_key );
    }

    /**
     * Validates the currently stored API key
     *
     * @return void
     */
    private function validate_stored_api_key(): void {
        WP_CLI::line( 'Validating Stored API Key' );
        WP_CLI::line( '========================' );
        WP_CLI::line( '' );

        $api_key = $this->get_secure_claude_api_key();

        if ( ! $api_key ) {
            WP_CLI::error( 'No API key found. Please set one first using --set-api-key' );
            return;
        }

        WP_CLI::log( 'Found stored API key. Testing...' );

        if ( $this->validate_claude_api_key( $api_key ) ) {
            WP_CLI::success( 'Stored API key is valid and working!' );
        }
        else {
            WP_CLI::error( 'Stored API key is invalid. Please update it using --set-api-key' );
        }
    }

    /**
     * Retrieves and decrypts the stored Claude API key
     *
     * @return string|false The decrypted API key or false if not found/invalid
     */
    private function get_secure_claude_api_key(): string|false {
        $encrypted_key = get_option( '84em_claude_api_key_encrypted' );
        $iv            = get_option( '84em_claude_api_key_iv' );

        if ( empty( $encrypted_key ) || empty( $iv ) ) {
            return false;
        }

        // Recreate encryption key using WordPress salts with fallbacks
        $encryption_key = $this->get_encryption_key();

        // Decrypt the API key
        $decrypted_key = openssl_decrypt(
            base64_decode( $encrypted_key ),
            'AES-256-CBC',
            $encryption_key,
            0,
            base64_decode( $iv )
        );

        return $decrypted_key !== false ? $decrypted_key : false;
    }

    /**
     * Generates encryption key from WordPress salts with fallbacks
     *
     * @return string SHA-256 hash of combined WordPress salts
     */
    private function get_encryption_key(): string {
        // Collect WordPress salts with fallbacks
        $salts = [];

        // Primary WordPress salts
        $salts[] = defined( 'AUTH_KEY' ) ? AUTH_KEY : 'fallback-auth-key';
        $salts[] = defined( 'SECURE_AUTH_KEY' ) ? SECURE_AUTH_KEY : 'fallback-secure-auth-key';
        $salts[] = defined( 'LOGGED_IN_KEY' ) ? LOGGED_IN_KEY : 'fallback-logged-in-key';
        $salts[] = defined( 'NONCE_KEY' ) ? NONCE_KEY : 'fallback-nonce-key';

        // Additional salts for more entropy
        $salts[] = defined( 'AUTH_SALT' ) ? AUTH_SALT : 'fallback-auth-salt';
        $salts[] = defined( 'SECURE_AUTH_SALT' ) ? SECURE_AUTH_SALT : 'fallback-secure-auth-salt';
        $salts[] = defined( 'LOGGED_IN_SALT' ) ? LOGGED_IN_SALT : 'fallback-logged-in-salt';
        $salts[] = defined( 'NONCE_SALT' ) ? NONCE_SALT : 'fallback-nonce-salt';

        // Add WordPress-specific constants for uniqueness
        $salts[] = defined( 'ABSPATH' ) ? ABSPATH : __DIR__;
        $salts[] = defined( 'WP_HOME' ) ? WP_HOME : get_site_url();

        // Create encryption key from combined salts
        return hash( 'sha256', implode( '|', $salts ) );
    }

    /**
     * Handles delete commands for local pages
     *
     * @param  array  $assoc_args  WP-CLI associative arguments
     *
     * @return void
     */
    private function handle_delete_command( array $assoc_args ): void {
        $state_arg = $assoc_args['state'] ?? 'all';

        if ( $state_arg === 'all' ) {
            $posts = get_posts( [
                'post_type'   => 'local',
                'post_status' => 'any',
                'numberposts' => - 1,
            ] );

            $deleted_count = 0;
            foreach ( $posts as $post ) {
                if ( wp_delete_post( $post->ID, true ) ) {
                    $deleted_count ++;
                    $state = get_post_meta( $post->ID, '_local_page_state', true );
                    WP_CLI::log( "Deleted Local Page for {$state}" );
                }
            }

            WP_CLI::success( "Deleted {$deleted_count} Local Pages." );
        }
        else {
            $state_names   = $this->parse_state_names( $state_arg );
            $deleted_count = 0;

            foreach ( $state_names as $state_name ) {
                // Validate state name
                if ( ! isset( $this->us_states_data[ $state_name ] ) ) {
                    WP_CLI::warning( "Invalid state name: {$state_name}. Skipping." );
                    continue;
                }

                // Find existing post for this state
                $existing_posts = get_posts( [
                    'post_type'   => 'local',
                    'meta_key'    => '_local_page_state',
                    'meta_value'  => $state_name,
                    'numberposts' => 1,
                ] );

                if ( ! empty( $existing_posts ) ) {
                    if ( wp_delete_post( $existing_posts[0]->ID, true ) ) {
                        $deleted_count ++;
                        WP_CLI::log( "Deleted Local Page for {$state_name}" );
                    }
                    else {
                        WP_CLI::warning( "Failed to delete Local Page for {$state_name}" );
                    }
                }
                else {
                    WP_CLI::warning( "No existing page found for {$state_name}. Nothing to delete." );
                }
            }

            WP_CLI::success( "Deleted {$deleted_count} Local Pages." );
        }
    }

    /**
     * Handles update commands for existing local pages
     *
     * @param  array  $assoc_args  WP-CLI associative arguments
     *
     * @return void
     */
    private function handle_update_command( array $assoc_args ): void {
        $state_arg = $assoc_args['state'] ?? 'all';

        if ( $state_arg === 'all' ) {
            $posts = get_posts( [
                'post_type'   => 'local',
                'post_status' => 'any',
                'numberposts' => - 1,
            ] );

            $updated_count = 0;
            foreach ( $posts as $post ) {
                $state = get_post_meta( $post->ID, '_local_page_state', true );
                if ( $state && $this->update_local_page( $post->ID, $state ) ) {
                    $updated_count ++;
                }
            }

            WP_CLI::success( "Updated {$updated_count} Local Pages." );
        }
        else {
            $state_names   = $this->parse_state_names( $state_arg );
            $updated_count = 0;

            foreach ( $state_names as $state_name ) {
                // Validate state name
                if ( ! isset( $this->us_states_data[ $state_name ] ) ) {
                    WP_CLI::warning( "Invalid state name: {$state_name}. Skipping." );
                    continue;
                }

                // Find existing post for this state
                $existing_posts = get_posts( [
                    'post_type'   => 'local',
                    'meta_key'    => '_local_page_state',
                    'meta_value'  => $state_name,
                    'numberposts' => 1,
                ] );

                if ( ! empty( $existing_posts ) ) {
                    if ( $this->update_local_page( $existing_posts[0]->ID, $state_name ) ) {
                        $updated_count ++;
                        WP_CLI::log( "Updated Local Page for {$state_name}" );
                    }
                    else {
                        WP_CLI::warning( "Failed to update Local Page for {$state_name}" );
                    }
                }
                else {
                    WP_CLI::warning( "No existing page found for {$state_name}. Use --state to create it." );
                }
            }

            WP_CLI::success( "Updated {$updated_count} Local Pages." );
        }
    }

    /**
     * Handles state-based commands for creating/updating local pages
     *
     * @param  array  $assoc_args  WP-CLI associative arguments
     *
     * @return void
     */
    private function handle_state_command( array $assoc_args ): void {
        $state_arg = $assoc_args['state'];

        // Handle 'all' states
        if ( $state_arg === 'all' ) {
            $this->generate_all_local_pages();

            return;
        }

        $state_names   = $this->parse_state_names( $state_arg );
        $created_count = 0;
        $updated_count = 0;

        foreach ( $state_names as $state_name ) {
            // Validate state name
            if ( ! isset( $this->us_states_data[ $state_name ] ) ) {
                WP_CLI::warning( "Invalid state name: {$state_name}. Skipping." );
                continue;
            }

            // Check if page already exists
            $existing_posts = get_posts( [
                'post_type'   => 'local',
                'meta_key'    => '_local_page_state',
                'meta_value'  => $state_name,
                'numberposts' => 1,
            ] );

            if ( ! empty( $existing_posts ) ) {
                if ( $this->update_local_page( $existing_posts[0]->ID, $state_name ) ) {
                    $updated_count ++;
                    WP_CLI::log( "Updated Local Page for {$state_name} (ID: {$existing_posts[0]->ID})" );
                }
                else {
                    WP_CLI::warning( "Failed to update Local Page for {$state_name}" );
                }
            }
            else {
                if ( $this->create_local_page( $state_name ) ) {
                    $created_count ++;
                    WP_CLI::log( "Created Local Page for {$state_name}" );
                }
                else {
                    WP_CLI::warning( "Failed to create Local Page for {$state_name}" );
                }
            }

            // Add small delay to avoid API rate limits
            if ( count( $state_names ) > 1 ) {
                sleep( 1 );
            }
        }

        if ( count( $state_names ) === 1 ) {
            // For single states, show success/error as before
            if ( $created_count > 0 ) {
                WP_CLI::success( "Created Local Page for {$state_names[0]}" );
            }
            elseif ( $updated_count > 0 ) {
                WP_CLI::success( "Updated Local Page for {$state_names[0]}" );
            }
            else {
                WP_CLI::error( "Failed to process Local Page for {$state_names[0]}" );
            }
        }
        else {
            // For multiple states, show summary
            WP_CLI::success( "Process completed. Created: {$created_count}, Updated: {$updated_count}" );
        }
    }


    /**
     * Parses comma-delimited state names into array
     *
     * @param  string  $state_arg  State name(s) - single or comma-delimited
     *
     * @return array Array of trimmed state names
     */
    private function parse_state_names( string $state_arg ): array {
        if ( strpos( $state_arg, ',' ) !== false ) {
            return array_map( 'trim', explode( ',', $state_arg ) );
        }
        else {
            return [ trim( $state_arg ) ];
        }
    }

    /**
     * Generates or updates local pages for all states in the dataset.
     *
     * This method iterates through the list of US states and checks if a local page already
     * exists for each state. If a page exists, it updates the page's content. If no page exists,
     * it creates a new local page for the state. A progress bar is displayed during the process.
     *
     * @return void
     */
    private function generate_all_local_pages(): void {
        $created_count = 0;
        $updated_count = 0;
        $total_states  = count( $this->us_states_data );

        // Initialize progress bar
        $progress = \WP_CLI\Utils\make_progress_bar( 'Processing states', $total_states );

        foreach ( $this->us_states_data as $state => $data ) {
            // Check if page already exists
            $existing_posts = get_posts( [
                'post_type'   => 'local',
                'meta_key'    => '_local_page_state',
                'meta_value'  => $state,
                'numberposts' => 1,
            ] );

            if ( ! empty( $existing_posts ) ) {
                if ( $this->update_local_page( $existing_posts[0]->ID, $state ) ) {
                    $updated_count ++;
                    WP_CLI::log( "Updated Local Page for {$state}" );
                }
            }
            else {
                if ( $this->create_local_page( $state ) ) {
                    $created_count ++;
                    WP_CLI::log( "Created Local Page for {$state}" );
                }
            }

            // Add small delay to avoid API rate limits
            sleep( 1 );

            // Update progress bar
            $progress->tick();
        }

        // Finish progress bar
        $progress->finish();

        WP_CLI::success( "Process completed. Created: {$created_count}, Updated: {$updated_count}" );
    }

    /**
     * Creates a new local page for the specified state
     *
     * @param  string  $state  State name to create page for
     *
     * @return bool True if page created successfully, false otherwise
     */
    private function create_local_page( string $state ): bool {
        WP_CLI::log( "📝 Creating page for {$state}..." );

        $cities  = $this->us_states_data[ $state ]['cities'];
        $content = $this->generate_content_with_claude( $state, $cities );

        if ( $content === false ) {
            WP_CLI::warning( "❌ Failed to generate content for {$state}" );

            return false;
        }

        $post_data = [
            'post_title'   => "WordPress Development Services in {$state} | 84EM",
            'post_name'    => 'wordpress-development-services-' . sanitize_title( $state ),
            'post_content' => $content,
            'post_status'  => 'publish',
            'post_type'    => 'local',
            'post_author'  => 1,
            'meta_input'   => [
                '_local_page_state'    => $state,
                '_local_page_cities'   => implode( ',', $cities ),
                '_genesis_title'       => "Expert WordPress Development Services in {$state} | 84EM",
                '_genesis_description' => "Professional WordPress development, custom plugins, and web solutions for businesses in {$state}. White-label services for agencies in " . implode( ', ', array_slice( $cities, 0, 3 ) ) . ".",
                'schema'               => $this->generate_ld_json_schema( $state, $cities ),
            ],
        ];

        $post_id = wp_insert_post( $post_data );

        if ( $post_id && ! is_wp_error( $post_id ) ) {
            return true;
        }

        return false;
    }

    /**
     * Updates an existing local page with new content
     *
     * @param  int  $post_id  WordPress post ID to update
     * @param  string  $state  State name for content generation
     *
     * @return bool True if page updated successfully, false otherwise
     */
    private function update_local_page( int $post_id, string $state ): bool {
        WP_CLI::log( "🔄 Updating page for {$state}..." );

        $cities  = $this->us_states_data[ $state ]['cities'];
        $content = $this->generate_content_with_claude( $state, $cities );

        if ( $content === false ) {
            return false;
        }

        $post_data = [
            'ID'                => $post_id,
            'post_content'      => $content,
            'post_modified'     => current_time( 'mysql' ),
            'post_modified_gmt' => current_time( 'mysql', 1 ),
        ];

        $result = wp_update_post( $post_data );

        if ( $result && ! is_wp_error( $result ) ) {
            // Update meta fields
            update_post_meta( $post_id, '_local_page_cities', implode( ',', $cities ) );
            update_post_meta( $post_id, '_genesis_description', "Professional WordPress development, custom plugins, and web solutions for businesses in {$state}. White-label services for agencies in " . implode( ', ', array_slice( $cities, 0, 3 ) ) . "." );
            update_post_meta( $post_id, 'schema', $this->generate_ld_json_schema( $state, $cities ) );

            return true;
        }

        return false;
    }

    /**
     * Generates content for a state using Claude AI API
     *
     * @param  string  $state  State name for content generation
     * @param  array  $cities  Array of major cities in the state
     *
     * @return string|false Generated content or false on failure
     */
    private function generate_content_with_claude( string $state, array $cities ): string|false {
        $main_cities = array_slice( $cities, 0, 4 );
        $city_list   = implode( ', ', $main_cities );

        // Get relevant keywords for content generation
        $service_keywords_list = implode( ', ', $this->service_keywords );

        $prompt = "Write a concise, SEO-optimized landing page for 84EM's WordPress development services specifically for businesses in {$state}. 

IMPORTANT: Create unique, original content that is different from other state pages. Focus on local relevance through city mentions and state-specific benefits.

84EM is a 100% FULLY REMOTE WordPress development company. Do NOT mention on-site visits, in-person consultations, local offices, or physical presence. All work is done remotely.

Include the following key elements:
1. A professional opening paragraph mentioning {$state} and cities like {$city_list}
2. WordPress development services including: {$service_keywords_list}
3. Why businesses in {$state} choose 84EM (remote expertise, proven track record, reliable delivery)
4. Call-to-action for {$state} businesses
5. Include naturally-placed keywords: 'WordPress development {$state}', 'custom plugins {$state}', 'web development {$city_list}'

Write approximately 300-400 words in a professional, factual tone. Avoid hyperbole and superlatives. Focus on concrete services, technical expertise, and actual capabilities. Make it locally relevant through geographic references while emphasizing 84EM's remote-first approach serves clients nationwide.

CRITICAL: Format the content using WordPress block editor syntax (Gutenberg blocks). Use the following format:
- Paragraphs: <!-- wp:paragraph --><p>Your paragraph text here.</p><!-- /wp:paragraph -->
- Headings: <!-- wp:heading {\"level\":2} --><h2><strong>Your Heading</strong></h2><!-- /wp:heading -->
- Sub-headings: <!-- wp:heading {\"level\":3} --><h3><strong>Your Sub-heading</strong></h3><!-- /wp:heading -->
- Call-to-action links: <a href=\"/contact/\">contact us today</a> or <a href=\"/contact/\">get started</a>

IMPORTANT: 
- All headings (h2, h3) must be wrapped in <strong> tags to ensure they appear bold.
- Include 2-3 call-to-action links throughout the content that link to /contact/ using phrases like \"contact us today\", \"get started\", \"reach out\", \"discuss your project\", etc.
- Make the call-to-action links natural and contextual within the content.
- Insert this exact CTA block BEFORE every H2 heading:

<!-- wp:group {\"className\":\"get-started-local\",\"style\":{\"spacing\":{\"margin\":{\"top\":\"0\"},\"padding\":{\"bottom\":\"var:preset|spacing|40\",\"top\":\"var:preset|spacing|40\",\"right\":\"0\"}}},\"layout\":{\"type\":\"constrained\",\"contentSize\":\"1280px\"}} -->
<div class=\"wp-block-group get-started-local\" style=\"margin-top:0;padding-top:var(--wp--preset--spacing--40);padding-right:0;padding-bottom:var(--wp--preset--spacing--40)\"><!-- wp:buttons {\"className\":\"animated bounceIn\",\"layout\":{\"type\":\"flex\",\"justifyContent\":\"center\"}} -->
<div class=\"wp-block-buttons animated bounceIn\"><!-- wp:button {\"style\":{\"border\":{\"radius\":{\"topLeft\":\"0px\",\"topRight\":\"30px\",\"bottomLeft\":\"30px\",\"bottomRight\":\"0px\"}},\"shadow\":\"var:preset|shadow|crisp\"},\"fontSize\":\"large\"} -->
<div class=\"wp-block-button\"><a class=\"wp-block-button__link has-large-font-size has-custom-font-size wp-element-button\" href=\"/contact/\" style=\"border-top-left-radius:0px;border-top-right-radius:30px;border-bottom-left-radius:30px;border-bottom-right-radius:0px;box-shadow:var(--wp--preset--shadow--crisp)\">Start Your WordPress Project</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:group -->

Do NOT use markdown syntax or plain HTML. Use proper WordPress block markup for all content.";

        return $this->call_claude_api( $prompt );
    }

    /**
     * Generates LD JSON schema for SEO purposes
     *
     * @param string $state State name
     * @param array $cities Array of cities in the state
     * @return string JSON-encoded LD schema
     */
    private function generate_ld_json_schema( string $state, array $cities ): string {
        $main_cities = array_slice( $cities, 0, 4 );

        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'LocalBusiness',
            'name' => '84EM WordPress Development Services',
            'description' => "Professional WordPress development, custom plugins, and web solutions for businesses in {$state}",
            'url' => home_url( '/wordpress-development-services-' . sanitize_title( $state ) . '/' ),
            'serviceArea' => [
                '@type' => 'State',
                'name' => $state,
                'containsPlace' => array_map( function( $city ) use ( $state ) {
                    return [
                        '@type' => 'City',
                        'name' => $city,
                        'addressRegion' => $state
                    ];
                }, $main_cities )
            ],
            'hasOfferCatalog' => [
                '@type' => 'OfferCatalog',
                'name' => 'WordPress Development Services',
                'itemListElement' => array_map( function( $service, $index ) {
                    return [
                        '@type' => 'Offer',
                        'position' => $index + 1,
                        'itemOffered' => [
                            '@type' => 'Service',
                            'name' => $service,
                            'serviceType' => 'WordPress Development'
                        ]
                    ];
                }, $this->service_keywords, array_keys( $this->service_keywords ) )
            ],
            'contactPoint' => [
                '@type' => 'ContactPoint',
                'contactType' => 'customer service',
                'url' => home_url( '/contact/' )
            ],
            'address' => [
                '@type' => 'PostalAddress',
                'addressRegion' => $state,
                'addressCountry' => 'US'
            ],
            'areaServed' => [
                '@type' => 'State',
                'name' => $state
            ]
        ];

        return wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
    }

    /**
     * Generates XML sitemap for all published local pages
     *
     * @return void
     */
    public function generate_sitemap(): void {
        WP_CLI::log( '🗺️ Generating XML sitemap for Local Pages...' );

        // Initialize WordPress filesystem
        global $wp_filesystem;
        if ( empty( $wp_filesystem ) ) {
            require_once ABSPATH . '/wp-admin/includes/file.php';
            WP_Filesystem();
        }

        // Get all published local pages using WP_Query
        $query = new WP_Query( [
            'post_type'      => 'local',
            'post_status'    => 'publish',
            'posts_per_page' => - 1,
            'orderby'        => 'title',
            'order'          => 'ASC',
        ] );

        if ( ! $query->have_posts() ) {
            WP_CLI::warning( 'No published Local Pages found. Nothing to add to sitemap.' );

            return;
        }

        // Build XML sitemap content
        $xml_content = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml_content .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        $page_count = 0;
        while ( $query->have_posts() ) {
            $query->the_post();
            $post_id       = get_the_ID();
            $permalink     = get_permalink( $post_id );
            $modified_date = get_the_modified_date( 'c', $post_id ); // ISO 8601 format

            $xml_content .= '  <url>' . "\n";
            $xml_content .= '    <loc>' . esc_url( $permalink ) . '</loc>' . "\n";
            $xml_content .= '    <lastmod>' . $modified_date . '</lastmod>' . "\n";
            $xml_content .= '    <changefreq>monthly</changefreq>' . "\n";
            $xml_content .= '    <priority>0.7</priority>' . "\n";
            $xml_content .= '  </url>' . "\n";

            $page_count ++;
        }

        $xml_content .= '</urlset>' . "\n";

        // Reset post data
        wp_reset_postdata();

        // Define sitemap file path (root directory)
        $sitemap_file = ABSPATH . 'sitemap-local.xml';

        // Write sitemap to file using WordPress filesystem
        if ( $wp_filesystem->put_contents( $sitemap_file, $xml_content, FS_CHMOD_FILE ) ) {
            WP_CLI::success( "✅ Sitemap generated successfully! Added {$page_count} pages to sitemap-local.xml" );
            WP_CLI::log( "📄 Sitemap saved to: {$sitemap_file}" );
        }
        else {
            WP_CLI::error( '❌ Failed to write sitemap file. Check file permissions.' );
        }
    }

    /**
     * Generates or updates the index page with an alphabetized list of states
     *
     * @return void
     */
    public function generate_index_page(): void {
        WP_CLI::log( '📄 Generating index page for WordPress Development Services in USA...' );

        $page_slug = 'wordpress-development-services-usa';
        $page_title = 'WordPress Development Services in USA | 84EM';

        // Check if page already exists
        $existing_page = get_page_by_path( $page_slug );

        // Get all published local pages using WP_Query
        $query = new WP_Query( [
            'post_type'      => 'local',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'orderby'        => 'title',
            'order'          => 'ASC',
            'meta_query'     => [
                [
                    'key'     => '_local_page_state',
                    'compare' => 'EXISTS'
                ]
            ]
        ] );

        if ( ! $query->have_posts() ) {
            WP_CLI::warning( 'No published Local Pages found. Cannot generate index page.' );
            return;
        }

        // Build content with alphabetized list of states
        $content_data = $this->build_index_page_content( $query );
        $content = $content_data['content'];
        $states_data = $content_data['states_data'];

        // Generate LD-JSON schema
        $schema = $this->generate_index_page_ld_json_schema( $states_data );

        // Reset post data
        wp_reset_postdata();

        if ( $existing_page ) {
            // Update existing page
            $post_data = [
                'ID'           => $existing_page->ID,
                'post_content' => $content,
                'post_modified' => current_time( 'mysql' ),
                'post_modified_gmt' => current_time( 'mysql', 1 ),
            ];

            $result = wp_update_post( $post_data );

            if ( $result && ! is_wp_error( $result ) ) {
                // Update meta fields including schema
                update_post_meta( $existing_page->ID, '_genesis_description', 'Professional WordPress development services across all 50 states in the USA. Expert custom plugins, API integrations, and web solutions for businesses nationwide.' );
                update_post_meta( $existing_page->ID, 'schema', $schema );

                WP_CLI::success( "✅ Updated index page '{$page_title}' (ID: {$existing_page->ID})" );
            }
            else {
                WP_CLI::error( '❌ Failed to update index page.' );
            }
        }
        else {
            // Create new page
            $post_data = [
                'post_title'   => $page_title,
                'post_name'    => $page_slug,
                'post_content' => $content,
                'post_status'  => 'publish',
                'post_type'    => 'page',
                'post_author'  => 1,
                'meta_input'   => [
                    '_genesis_title'       => $page_title,
                    '_genesis_description' => 'Professional WordPress development services across all 50 states in the USA. Expert custom plugins, API integrations, and web solutions for businesses nationwide.',
                    'schema'               => $schema,
                ],
            ];

            $post_id = wp_insert_post( $post_data );

            if ( $post_id && ! is_wp_error( $post_id ) ) {
                WP_CLI::success( "✅ Created index page '{$page_title}' (ID: {$post_id})" );
            }
            else {
                WP_CLI::error( '❌ Failed to create index page.' );
            }
        }
    }

    /**
     * Builds the content for the index page with alphabetized state list
     *
     * @param WP_Query $query Query object containing local pages
     * @return array Array with 'content' and 'states_data' keys
     */
    private function build_index_page_content( WP_Query $query ): array {
        $states_data = [];

        // Extract state data from local pages
        while ( $query->have_posts() ) {
            $query->the_post();
            $post_id = get_the_ID();
            $state = get_post_meta( $post_id, '_local_page_state', true );
            $permalink = get_permalink( $post_id );

            if ( $state && $permalink ) {
                $states_data[] = [
                    'name' => $state,
                    'url'  => $permalink
                ];
            }
        }

        // Sort states alphabetically
        usort( $states_data, function( $a, $b ) {
            return strcmp( $a['name'], $b['name'] );
        });

        // Build the content using WordPress block editor syntax
        $content = '<!-- wp:paragraph -->
<p>84EM provides professional WordPress development services across all 50 states in the USA. Our remote-first approach enables us to deliver expert WordPress solutions, custom plugins, API integrations, and comprehensive web development services to businesses nationwide.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":2} -->
<h2><strong>WordPress Development Services by State</strong></h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Click on your state below to learn more about our WordPress development services in your area:</p>
<!-- /wp:paragraph -->

<!-- wp:list -->
<ul>';

        // Add each state as a list item
        foreach ( $states_data as $state ) {
            $content .= '<li><a href="' . esc_url( $state['url'] ) . '">' . esc_html( $state['name'] ) . '</a></li>';
        }

        $content .= '</ul>
<!-- /wp:list -->

<!-- wp:heading {"level":2} -->
<h2><strong>Why Choose 84EM for WordPress Development?</strong></h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>As a fully remote WordPress development company, 84EM serves clients across the United States with:</p>
<!-- /wp:paragraph -->

<!-- wp:list -->
<ul>
<li>Custom WordPress development and plugin creation</li>
<li>API integrations and third-party service connections</li>
<li>WordPress security audits and hardening</li>
<li>White-label development services for agencies</li>
<li>WordPress maintenance and ongoing support</li>
<li>Data migration and platform transfers</li>
<li>WordPress troubleshooting and optimization</li>
</ul>
<!-- /wp:list -->

<!-- wp:paragraph -->
<p>Our experienced team delivers reliable, scalable WordPress solutions regardless of your location. <a href="/contact/">Contact us today</a> to discuss your WordPress development needs.</p>
<!-- /wp:paragraph -->';

        return [
            'content' => $content,
            'states_data' => $states_data
        ];
    }

    /**
     * Generates LD JSON schema for the index page
     *
     * @param array $states_data Array of state data with names and URLs
     * @return string JSON-encoded LD schema
     */
    private function generate_index_page_ld_json_schema( array $states_data ): string {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'WebPage',
            'name' => 'WordPress Development Services in USA | 84EM',
            'description' => 'Professional WordPress development services across all 50 states in the USA. Expert custom plugins, API integrations, and web solutions for businesses nationwide.',
            'url' => home_url( '/wordpress-development-services-usa/' ),
            'mainEntity' => [
                '@type' => 'Service',
                'name' => '84EM WordPress Development Services',
                'description' => 'Comprehensive WordPress development services including custom plugins, API integrations, security audits, and web solutions for businesses across the United States.',
                'provider' => [
                    '@type' => 'Organization',
                    'name' => '84EM',
                    'description' => 'Remote-first WordPress development company serving clients nationwide',
                    'url' => home_url( '/' )
                ],
                'serviceType' => 'WordPress Development',
                'areaServed' => [
                    '@type' => 'Country',
                    'name' => 'United States',
                    'containsPlace' => array_map( function( $state ) {
                        return [
                            '@type' => 'State',
                            'name' => $state['name'],
                            'url' => $state['url']
                        ];
                    }, $states_data )
                ],
                'hasOfferCatalog' => [
                    '@type' => 'OfferCatalog',
                    'name' => 'WordPress Development Services',
                    'itemListElement' => array_map( function( $service, $index ) {
                        return [
                            '@type' => 'Offer',
                            'position' => $index + 1,
                            'itemOffered' => [
                                '@type' => 'Service',
                                'name' => $service,
                                'serviceType' => 'WordPress Development'
                            ]
                        ];
                    }, $this->service_keywords, array_keys( $this->service_keywords ) )
                ]
            ],
            'breadcrumb' => [
                '@type' => 'BreadcrumbList',
                'itemListElement' => [
                    [
                        '@type' => 'ListItem',
                        'position' => 1,
                        'name' => 'Home',
                        'item' => home_url( '/' )
                    ],
                    [
                        '@type' => 'ListItem',
                        'position' => 2,
                        'name' => 'WordPress Development Services USA',
                        'item' => home_url( '/wordpress-development-services-usa/' )
                    ]
                ]
            ],
            'about' => [
                '@type' => 'ItemList',
                'name' => 'WordPress Development Services by State',
                'description' => 'State-specific WordPress development services across all 50 US states',
                'numberOfItems' => count( $states_data ),
                'itemListElement' => array_map( function( $state, $index ) {
                    return [
                        '@type' => 'ListItem',
                        'position' => $index + 1,
                        'name' => "WordPress Development Services in {$state['name']}",
                        'url' => $state['url']
                    ];
                }, $states_data, array_keys( $states_data ) )
            ]
        ];

        return wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
    }

    /**
     * Makes API call to Claude with the provided prompt using WordPress HTTP API
     *
     * @param  string  $prompt  The prompt to send to Claude API
     *
     * @return string|false API response content or false on failure
     */
    private function call_claude_api( string $prompt ): string|false {
        $start_time = microtime( true );
        WP_CLI::log( '🤖 Sending request to Claude API...' );

        $api_url = 'https://api.anthropic.com/v1/messages';

        $headers = [
            'Content-Type'      => 'application/json',
            'x-api-key'         => $this->claude_api_key,
            'anthropic-version' => '2023-06-01',
        ];

        $body = [
            'model'      => 'claude-sonnet-4-20250514',
            'max_tokens' => 4000,
            'messages'   => [
                [
                    'role'    => 'user',
                    'content' => $prompt,
                ],
            ],
        ];

        $args = [
            'method'     => 'POST',
            'headers'    => $headers,
            'body'       => wp_json_encode( $body ),
            'timeout'    => 60,
            'user-agent' => '84EM Local Pages Generator/' . EIGHTYFOUREM_LOCAL_PAGES_VERSION,
            'sslverify'  => true,
        ];

        WP_CLI::log( '⏳ Waiting for Claude response...' );
        $response = wp_remote_request( $api_url, $args );

        $duration = round( microtime( true ) - $start_time, 2 );

        // Check for WordPress HTTP API errors
        if ( is_wp_error( $response ) ) {
            WP_CLI::warning( "❌ Claude API request failed: " . $response->get_error_message() . " (took {$duration}s)" );
            return false;
        }

        $http_code = wp_remote_retrieve_response_code( $response );
        if ( $http_code !== 200 ) {
            $error_body = wp_remote_retrieve_body( $response );
            WP_CLI::warning( "❌ Claude API request failed with HTTP code: {$http_code} (took {$duration}s)" );
            WP_CLI::log( "Response body: " . $error_body );
            return false;
        }

        $response_body = wp_remote_retrieve_body( $response );
        $response_data = json_decode( $response_body, true );

        if ( json_last_error() !== JSON_ERROR_NONE ) {
            WP_CLI::warning( "❌ Failed to parse Claude API response JSON (took {$duration}s)" );
            return false;
        }

        if ( isset( $response_data['content'][0]['text'] ) ) {
            WP_CLI::log( "✅ Content generated successfully! (took {$duration}s)" );
            return $response_data['content'][0]['text'];
        }

        WP_CLI::warning( "❌ Unexpected Claude API response format (took {$duration}s)" );

        return false;
    }

    /**
     * Validates Claude API key by making a minimal test request
     *
     * @param  string  $api_key  The API key to validate
     *
     * @return bool True if API key is valid, false otherwise
     */
    private function validate_claude_api_key( string $api_key ): bool {
        WP_CLI::log( '🔑 Validating API key...' );

        $api_url = 'https://api.anthropic.com/v1/messages';

        $headers = [
            'Content-Type'      => 'application/json',
            'x-api-key'         => $api_key,
            'anthropic-version' => '2023-06-01',
        ];

        // Minimal test request with very low token count
        $body = [
            'model'      => 'claude-sonnet-4-20250514',
            'max_tokens' => 10,
            'messages'   => [
                [
                    'role'    => 'user',
                    'content' => 'Hi',
                ],
            ],
        ];

        $args = [
            'method'     => 'POST',
            'headers'    => $headers,
            'body'       => wp_json_encode( $body ),
            'timeout'    => 15, // Shorter timeout for validation
            'user-agent' => '84EM Local Pages Generator/' . EIGHTYFOUREM_LOCAL_PAGES_VERSION,
            'sslverify'  => true,
        ];

        $response = wp_remote_request( $api_url, $args );

        // Check for WordPress HTTP API errors
        if ( is_wp_error( $response ) ) {
            WP_CLI::warning( '❌ API validation failed: ' . $response->get_error_message() );
            return false;
        }

        $http_code = wp_remote_retrieve_response_code( $response );

        if ( $http_code === 200 ) {
            WP_CLI::success( '✅ API key is valid!' );
            return true;
        }
        elseif ( $http_code === 401 ) {
            WP_CLI::warning( '❌ API key is invalid or unauthorized.' );
            return false;
        }
        elseif ( $http_code === 403 ) {
            WP_CLI::warning( '❌ API key does not have permission to access this resource.' );
            return false;
        }
        else {
            $error_body = wp_remote_retrieve_body( $response );
            WP_CLI::warning( "❌ API validation failed with HTTP code: {$http_code}" );
            WP_CLI::log( "Response: " . $error_body );
            return false;
        }
    }

    /**
     * Displays help information for available WP-CLI commands
     *
     * @return void
     */
    private function show_help(): void {
        WP_CLI::line( '84EM Local Pages Generator Commands:' );
        WP_CLI::line( '' );
        WP_CLI::line( 'Set Claude API Key (interactive prompt):' );
        WP_CLI::line( '  wp 84em local-pages --set-api-key' );
        WP_CLI::line( '' );
        WP_CLI::line( 'Validate Stored API Key:' );
        WP_CLI::line( '  wp 84em local-pages --validate-api-key' );
        WP_CLI::line( '' );
        WP_CLI::line( 'Generate/Update All Local Pages:' );
        WP_CLI::line( '  wp 84em local-pages --state=all' );
        WP_CLI::line( '' );
        WP_CLI::line( 'Generate/Update Specific State:' );
        WP_CLI::line( '  wp 84em local-pages --state="California"' );
        WP_CLI::line( '  wp 84em local-pages --state="New York"' );
        WP_CLI::line( '' );
        WP_CLI::line( 'Update Specific Local Pages by State:' );
        WP_CLI::line( '  wp 84em local-pages --update --state="California"' );
        WP_CLI::line( '  wp 84em local-pages --update --state="California,New York,Texas"' );
        WP_CLI::line( '  wp 84em local-pages --update --state=all' );
        WP_CLI::line( '' );
        WP_CLI::line( 'Delete Local Pages by State:' );
        WP_CLI::line( '  wp 84em local-pages --delete --state="California"' );
        WP_CLI::line( '  wp 84em local-pages --delete --state="California,New York,Texas"' );
        WP_CLI::line( '  wp 84em local-pages --delete --state=all' );
        WP_CLI::line( '' );
        WP_CLI::line( 'Generate XML Sitemap:' );
        WP_CLI::line( '  wp 84em local-pages --generate-sitemap' );
        WP_CLI::line( '' );
        WP_CLI::line( 'Generate Index Page (alphabetized state list):' );
        WP_CLI::line( '  wp 84em local-pages --generate-index' );
    }
}

// Initialize the plugin
new EightyFourEM_Local_Pages_Generator();
