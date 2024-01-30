<?php
function enqueue_font_awesome()
{
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css', [], null);
}

function dump(...$variables)
{
    echo '<pre>';
    foreach ($variables as $variable) {
        print_r($variable);
    }
    echo '</pre>';
}

add_action('wp_enqueue_scripts', 'enqueue_font_awesome');

function hide_editor()
{
    // Check if you are on the post editing screen for the desired post type(s).
    global $post;

    // Replace 'post' with the post type you want to hide the editor for (e.g., 'page' for pages).
    if ($post->post_type === 'camps') {
        echo '<style>#postdivrich { display: none; }</style>';
    }
}

add_action('edit_form_after_editor', 'hide_editor');

function validateUKPostcode($postcode)
{
    // Remove any whitespace from the input string
    $postcode = preg_replace('/\s+/', '', $postcode);

    // Define the regex pattern for UK postcodes
    $pattern = '/^(GIR 0AA|[A-PR-UWYZ]([0-9]{1,2}|([A-HK-Y][0-9]|[A-HK-Y][0-9]([0-9]|[ABEHMNPRV-Y]))|[0-9][A-HJKPS-UW])[0-9][ABD-HJLNP-UW-Z]{2})$/i';

    // Use preg_match to check if the postcode matches the pattern
    if (preg_match($pattern, $postcode)) {
        return true; // Valid UK postcode
    } else {
        return false; // Invalid UK postcode
    }
}

function slugify($text)
{
    $text = strip_tags($text);
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    setlocale(LC_ALL, 'en_US.utf8');
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);
    if (empty($text)) {
        return 'n-a';
    }
    return $text;
}

function getPostcodeTransientKey($postcode)
{
    return slugify('location ' . $postcode);
}

function fetchPostcodeLocationData(mixed $postcode): ?array
{
    $url = "https://api.postcodes.io/postcodes/$postcode";

    $response = wp_safe_remote_get($url);

    if (is_wp_error($response) || 200 !== wp_remote_retrieve_response_code($response)) return null;

    $data = json_decode(wp_remote_retrieve_body($response), true);

    if (!isset($data['result'])) return null;

    return $data['result'];
}

/**
 * Camps class for managing camp data.
 */
class Camps
{
    /**
     * @var array An array to store camp data.
     */
    public $campData = [];

    /**
     * @var array An array to store validation errors.
     */
    public $errors = [];

    /**
     * Set camp data based on the given postcode and maximum distance.
     *
     * @param string|null $givenPostcode The postcode for filtering or null for no filtering.
     * @param float|null $maxDistance The maximum distance for filtering or null for no filtering.
     */
    public function setCampData(?string $givenPostcode, ?float $maxDistance): void
    {
        $errors = $this->validate($givenPostcode);

        if (count($errors)) {
            $this->errors = $errors;
            return;
        }

        $campData = $this->fetchPosts();

        if (!$givenPostcode) {
            $this->campData = $campData;
            return;
        }

        $givenCoordinates = $this->fetchPostcodeLocationData($givenPostcode);

        if (!$givenCoordinates) {
            $this->errors = ['postcode' => 'Please provide a valid postcode'];
            return;
        }

        $this->campData = $this->transform($campData, $givenCoordinates, $maxDistance);
    }

    private function transform(array $campData, $givenCoordinates, $maxDistance): array
    {
        $transformed = [];

        foreach ($campData as $item) {
            if (!($item['latitude'] ?? null) || !($item['longitude'] ?? null)) continue;

            $coordinates = [
                'latitude' => $item['latitude'],
                'longitude' => $item['longitude'],
            ];

            $item['distance'] = $this->calculateHaversineDistance($givenCoordinates, $coordinates);

            $transformed[] = $item;
        }

        $filtered = $maxDistance ? array_filter($transformed, fn ($item) => $item['distance'] <= $maxDistance) : $campData;

        usort($filtered, fn ($a, $b) => $a['distance'] <=> $b['distance']);

        return $filtered;
    }

    /**
     * Validate a UK postcode.
     *
     * @param string $postcode The postcode to validate.
     *
     * @return array An array of validation errors.
     */
    private function validate(string|null $postcode): array
    {
        $errors = [];

        if ($postcode && !validateUKPostcode($postcode)) {
            $errors['postcode'] = 'Please provide a valid UK postcode';
        }

        return $errors;
    }

    /**
     * Fetch camp data from a data source.
     *
     * @return array An array of camp data.
     */
    private function fetchPosts(): array
    {
        $args = [
            'post_type' => 'camps',
            'posts_per_page' => -1,
        ];

        $query = new WP_Query($args);

        $campData = [];

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $fields = get_fields();
                if ($fields) {
                    $campData[] = $fields;
                }
            }
            wp_reset_postdata();
        }

        return $campData;
    }

    /**
     * Calculate the great-circle distance between two points on the Earth's surface using the Haversine formula.
     *
     * @param array $coord1 An associative array containing latitude and longitude of the first point.
     * @param array $coord2 An associative array containing latitude and longitude of the second point.
     *
     * @return float The calculated distance in miles.
     */
    private function calculateHaversineDistance(array $coord1, array $coord2): float
    {
        $earthRadiusMiles = 3959; // Earth's radius in miles (mean value)

        $lat1 = deg2rad($coord1['latitude']);
        $lon1 = deg2rad($coord1['longitude']);
        $lat2 = deg2rad($coord2['latitude']);
        $lon2 = deg2rad($coord2['longitude']);

        $dlat = $lat2 - $lat1;
        $dlon = $lon2 - $lon1;

        $a = sin($dlat / 2) * sin($dlat / 2) + cos($lat1) * cos($lat2) * sin($dlon / 2) * sin($dlon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        $distance = $earthRadiusMiles * $c; // Distance in miles

        return $distance;
    }


    /**
     * Fetch coordinates for a given postcode.
     *
     * @param string $postcode The postcode to fetch coordinates for.
     *
     * @return array|null An associative array containing latitude and longitude, or null if coordinates cannot be retrieved.
     */
    private function fetchPostcodeLocationData(string $postcode): ?array
    {
        $locationData = fetchPostcodeLocationData($postcode);

        if (!$locationData) return null;

        return [
            'latitude' => $locationData['latitude'],
            'longitude' => $locationData['longitude'],
        ];
    }
}

function handleImportPages($request)
{
    // Get the data from the request
    $campData = $request->get_json_params();

    if (empty($campData)) {
        return new WP_REST_Response(array('error' => 'Invalid data'), 400);
    }

    $transformed = [];

    foreach ($campData as $item) {
        $openingTimes = explode('-', $item['Hours']);
        $extendedOpeningTimes = explode('-', $item['Extended Hours']);
        $ages = explode('-', explode('year', $item['Ages?'])[0]);

        $transformedItem = [
            'name' => $item['Camp Name'] . ' - ' . explode(',', $item['Location'])[0],
            'location' => $item['Location'],
            'postcode' => $item['Postcode'],
            'opening_time' => $openingTimes[0],
            'closing_time' => $openingTimes[1],
            'extended_opening_time' => $extendedOpeningTimes[0],
            'extended_closing_time' => $extendedOpeningTimes[1],
            'opening_months' => [],
            'min_age' => trim($ages[0]),
            'max_age' => trim($ages[1]),
            'childcare_vouchers_tax_free' => $item['Childcare vouchers '][' Tax free?'] === 'Yes' ? true : false,
            'url' => $item['URL'],
            'logo' => attachment_url_to_postid($item['Logo']),
        ];

        $openingMonthsKeys = ['Feb', 'Easter', 'May', 'Summer', 'Oct', 'Christmas'];

        foreach ($openingMonthsKeys as $month) {
            if ($item[$month] === 'Yes') {
                array_push($transformedItem['opening_months'], $month);
            }
        }

        $transformed[] = $transformedItem;

        insertPost($transformedItem);
    }

    foreach ($transformed as $item) {
        if (!$item['closing_time']) {
            print_r($item);
        }
    }

    return $transformed;
}

function insertPost($data)
{
    $newPost = array(
        'post_title' => $data['name'],
        'post_type' => 'camps',
        'post_status' => 'publish',
    );

    $postId = wp_insert_post($newPost);

    foreach ($data as $fieldKey => $fieldValue) {
        update_field($fieldKey, $fieldValue, $postId);
    }
}

// Register a custom REST API route
function register_custom_import_route()
{
    register_rest_route('/v1', '/import', array(
        'methods' => 'POST',
        'callback' => 'handleImportPages',
    ));
}

add_action('rest_api_init', 'register_custom_import_route');


function handleScrapLongLat($request)
{
    $query = $request->get_query_params();
    $overwrite = $query['overwrite'] ?? false;

    $args = [
        'post_type' => 'camps',
        'posts_per_page' => -1,
    ];

    $query = new WP_Query($args);

    $updated = [];

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $postId = get_the_ID();
            $fields = get_fields($postId);

            if (!$fields['latitude'] || !$fields['longitude'] || $overwrite) {
                $locationData = fetchPostcodeLocationData($fields['postcode']);
                $updated[] = $fields;

                update_field('latitude', $locationData['latitude'] ?? null, $postId);
                update_field('longitude', $locationData['longitude'] ?? null, $postId);
            }
        }
        wp_reset_postdata();
    }

    return $updated;
}

// Register a custom REST API route
function registerScrapeLocationRoute()
{
    register_rest_route('/v1', '/scrape-long-lat', array(
        'methods' => 'POST',
        'callback' => 'handleScrapLongLat',
    ));
}

add_action('rest_api_init', 'registerScrapeLocationRoute');
