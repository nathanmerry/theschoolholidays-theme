<?php

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
     * @var string $_GET to filter the posts by the term
     */
    public $termFilter = null;

    /**
     * Set camp data based on the given postcode and maximum distance.
     *
     * @param string|null $givenPostcode The postcode for filtering or null for no filtering.
     * @param float|null $maxDistance The maximum distance for filtering or null for no filtering.
     */
    public function setCampData(?string $givenPostcode, ?float $maxDistance): void
    {
        $errors = $this->validate($givenPostcode);

        $this->termFilter = $_GET['term'] ?? null;

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

        $transformed = $this->transform($campData, $givenCoordinates, $maxDistance);

        $this->campData = $this->filter($transformed, $maxDistance);
    }

    private function filter(array $campData, $maxDistance): array
    {
        $filteredByDistance = $maxDistance ? array_filter($campData, fn ($item) => $item['distance'] <= $maxDistance) : $campData;

        usort($filteredByDistance, fn ($a, $b) => ($a['distance'] ?? null) <=> ($b['distance'] ?? null));

        if (!$this->termFilter) return $filteredByDistance;

        return array_filter($filteredByDistance, function ($camp) {
            $filteredTerms = array_filter($camp['opening_months'] ?? [], function ($term) {
                return in_array(strtolower($term), $this->termFilter);
            });


            return !!$filteredTerms;
        });
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


        return $transformed;
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

        if ($postcode && !$this->validateUKPostcode($postcode)) {
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
        $url = "https://api.postcodes.io/postcodes/$postcode";

        $response = wp_safe_remote_get($url);

        if (is_wp_error($response) || 200 !== wp_remote_retrieve_response_code($response)) return null;

        $data = json_decode(wp_remote_retrieve_body($response), true);

        if (!isset($data['result'])) return null;

        $locationData = $data['result'];

        if (!$locationData) return null;

        return [
            'latitude' => $locationData['latitude'],
            'longitude' => $locationData['longitude'],
        ];
    }

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
}
