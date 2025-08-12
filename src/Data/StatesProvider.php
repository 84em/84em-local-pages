<?php
/**
 * US States Data Provider
 *
 * @package EightyFourEM\LocalPages\Data
 */

namespace EightyFourEM\LocalPages\Data;

use EightyFourEM\LocalPages\Contracts\DataProviderInterface;

/**
 * Provides US states data
 */
class StatesProvider implements DataProviderInterface {
    /**
     * States data cache
     *
     * @var array|null
     */
    private ?array $data = null;

    /**
     * Get all states data
     *
     * @return array
     */
    public function getAll(): array {
        if ( null === $this->data ) {
            $this->loadData();
        }
        return $this->data;
    }

    /**
     * Get data for a specific state
     *
     * @param  string  $key  State name
     *
     * @return mixed|null
     */
    public function get( string $key ): mixed {
        if ( null === $this->data ) {
            $this->loadData();
        }
        return $this->data[ $key ] ?? null;
    }

    /**
     * Check if state exists
     *
     * @param  string  $key  State name
     *
     * @return bool
     */
    public function has( string $key ): bool {
        if ( null === $this->data ) {
            $this->loadData();
        }
        return isset( $this->data[ $key ] );
    }

    /**
     * Get state names
     *
     * @return array
     */
    public function getKeys(): array {
        if ( null === $this->data ) {
            $this->loadData();
        }
        return array_keys( $this->data );
    }

    /**
     * Load states data from config file
     */
    private function loadData(): void {
        $config_file = dirname( __DIR__, 2 ) . '/config/us-states-cities.php';

        if ( file_exists( $config_file ) ) {
            $this->data = require $config_file;
        }
        else {
            // Fallback to minimal data if config file not found
            $this->data = $this->getDefaultData();
        }
    }

    /**
     * Get default states data
     *
     * @return array
     */
    private function getDefaultData(): array {
        return [
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
            'New York'       => [ 'cities' => [ 'New York City', 'Buffalo', 'Rochester', 'Yonkers', 'Syracuse', 'Albany' ] ],
            'North Carolina' => [ 'cities' => [ 'Charlotte', 'Raleigh', 'Greensboro', 'Durham', 'Winston-Salem', 'Fayetteville' ] ],
            'North Dakota'   => [ 'cities' => [ 'Fargo', 'Bismarck', 'Grand Forks', 'Minot', 'West Fargo', 'Dickinson' ] ],
            'Ohio'           => [ 'cities' => [ 'Columbus', 'Cleveland', 'Cincinnati', 'Toledo', 'Akron', 'Dayton' ] ],
            'Oklahoma'       => [ 'cities' => [ 'Oklahoma City', 'Tulsa', 'Norman', 'Broken Arrow', 'Lawton', 'Edmond' ] ],
            'Oregon'         => [ 'cities' => [ 'Portland', 'Eugene', 'Salem', 'Gresham', 'Hillsboro', 'Beaverton' ] ],
            'Pennsylvania'   => [ 'cities' => [ 'Philadelphia', 'Pittsburgh', 'Allentown', 'Erie', 'Reading', 'Scranton' ] ],
            'Rhode Island'   => [ 'cities' => [ 'Providence', 'Warwick', 'Cranston', 'Pawtucket', 'East Providence', 'Woonsocket' ] ],
            'South Carolina' => [ 'cities' => [ 'Charleston', 'Columbia', 'North Charleston', 'Mount Pleasant', 'Rock Hill', 'Greenville' ] ],
            'South Dakota'   => [ 'cities' => [ 'Sioux Falls', 'Rapid City', 'Aberdeen', 'Brookings', 'Watertown', 'Mitchell' ] ],
            'Tennessee'      => [ 'cities' => [ 'Memphis', 'Nashville', 'Knoxville', 'Chattanooga', 'Clarksville', 'Murfreesboro' ] ],
            'Texas'          => [ 'cities' => [ 'Houston', 'San Antonio', 'Dallas', 'Austin', 'Fort Worth', 'El Paso' ] ],
            'Utah'           => [ 'cities' => [ 'Salt Lake City', 'West Valley City', 'Provo', 'West Jordan', 'Orem', 'Sandy' ] ],
            'Vermont'        => [ 'cities' => [ 'Burlington', 'Essex', 'South Burlington', 'Colchester', 'Rutland', 'Bennington' ] ],
            'Virginia'       => [ 'cities' => [ 'Virginia Beach', 'Norfolk', 'Chesapeake', 'Richmond', 'Newport News', 'Alexandria' ] ],
            'Washington'     => [ 'cities' => [ 'Seattle', 'Spokane', 'Tacoma', 'Vancouver', 'Bellevue', 'Kent' ] ],
            'West Virginia'  => [ 'cities' => [ 'Charleston', 'Huntington', 'Morgantown', 'Parkersburg', 'Wheeling', 'Weirton' ] ],
            'Wisconsin'      => [ 'cities' => [ 'Milwaukee', 'Madison', 'Green Bay', 'Kenosha', 'Racine', 'Appleton' ] ],
            'Wyoming'        => [ 'cities' => [ 'Cheyenne', 'Casper', 'Laramie', 'Gillette', 'Rock Springs', 'Sheridan' ] ],
        ];
    }

    /**
     * Get cities for a specific state
     *
     * @param  string  $state  State name
     *
     * @return array
     */
    public function getCities( string $state ): array {
        $state_data = $this->get( $state );
        return $state_data['cities'] ?? [];
    }

    /**
     * Get state abbreviation
     *
     * @param  string  $state  State name
     *
     * @return string|null
     */
    public function getAbbreviation( string $state ): ?string {
        $state_data = $this->get( $state );
        return $state_data['abbreviation'] ?? null;
    }

    /**
     * Get state capital
     *
     * @param  string  $state  State name
     *
     * @return string|null
     */
    public function getCapital( string $state ): ?string {
        $state_data = $this->get( $state );
        return $state_data['capital'] ?? null;
    }
}
