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

function create_custom_post_type()
{
    register_post_type(
        'camps',
        array(
            'labels' => array(
                'name' => __('Camps'),
                'singular_name' => __('Camp'),
            ),
            'public' => true,
            'has_archive' => true,
            'rewrite' => array('slug' => 'camps'),
        )
    );
}

add_action('init', 'create_custom_post_type');

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

$errors = [];

class Camps
{
    public $campData = [];
    public $errors = [];

    /**
     * Set camp data based on the given postcode and maximum distance.
     *
     * @param string|null $givenPostcode The postcode for filtering or null for no filtering.
     * @param float|null $maxDistance The maximum distance for filtering or null for no filtering.
     */
    public function setCampData($givenPostcode, $maxDistance)
    {
        $errors = $this->validate($givenPostcode);

        if (count($errors)) {
            $this->errors = $errors;
            return;
        };

        $campData = $this->fetchPosts();

        if (!$givenPostcode) {
            $this->campData = $campData;
            return;
        }

        $givenCoordinates = $this->fetchPostcodeCoordinates($givenPostcode);

        if (!$givenCoordinates) {
            $this->errors = ['postcode' => 'Please provide a valid postcode'];
            return;
        }

        foreach ($campData as $key => $item) {
            $coordinates = $this->fetchPostcodeCoordinates($item['postcode']);
            $campData[$key]['distance'] = $this->calculateHaversineDistance($givenCoordinates, $coordinates);
        }

        $filtered = $maxDistance ? array_filter($campData, function ($item) use ($maxDistance) {
            return $item['distance'] <= $maxDistance;
        }) : $campData;

        usort($filtered, function ($a, $b) {
            return $a['distance'] - $b['distance'];
        });

        $this->campData = $filtered;
    }

    public function validate($postcode)
    {
        $errors = [];

        if ($postcode && !validateUKPostcode($postcode)) {
            $errors['postcode'] = 'Please provide a valid UK postcode';
        }

        return $errors;
    }

    public function fetchPosts()
    {
        $args = array(
            'post_type' => 'camps', // Replace 'camps' with your custom post type name
            'posts_per_page' => -1, // Retrieve all posts of the custom post type
        );

        $query = new WP_Query($args);

        $campData = [];

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $fields = get_fields();
                $campData[] = $fields;
            }
            wp_reset_postdata();
        }

        return $campData;
    }

    /**
     * Calculate the great-circle distance between two points on the Earth's surface using the Haversine formula.
     *
     * This function computes the distance between two geographic coordinates (latitude and longitude)
     * on the Earth's surface. The result is the shortest distance (in kilometers) over the Earth's
     * surface, following the curvature of the Earth.
     *
     * @param array $coord1 An associative array containing latitude and longitude of the first point.
     * @param array $coord2 An associative array containing latitude and longitude of the second point.
     * @return float The calculated distance in kilometers.
     */
    function calculateHaversineDistance($coord1, $coord2)
    {
        $earthRadius = 6371; // Earth's radius in kilometers (mean value)

        $lat1 = deg2rad($coord1['latitude']);
        $lon1 = deg2rad($coord1['longitude']);
        $lat2 = deg2rad($coord2['latitude']);
        $lon2 = deg2rad($coord2['longitude']);

        $dlat = $lat2 - $lat1;
        $dlon = $lon2 - $lon1;

        $a = sin($dlat / 2) * sin($dlat / 2) + cos($lat1) * cos($lat2) * sin($dlon / 2) * sin($dlon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        $distance = $earthRadius * $c; // Distance in kilometers

        return $distance;
    }

    function fetchPostcodeCoordinates($postcode)
    {
        // https://api.postcodes.io/postcodes/rm126ah
        $apiKey = '3lM3xPOQJNCssr6Jwn5GLL9ePHd96I3v'; // Replace with your Ordnance Survey API key
        $url = "https://api.postcodes.io/postcodes/$postcode";

        $response = wp_safe_remote_get($url);

        if (is_wp_error($response) || 200 !== wp_remote_retrieve_response_code($response)) return null;

        $data = json_decode(wp_remote_retrieve_body($response), true);

        if (!isset($data['result'])) return null;

        $coordinates = [
            'latitude' => $data['result']['latitude'],
            'longitude' => $data['result']['longitude'],
        ];

        return $coordinates;
    }
}
