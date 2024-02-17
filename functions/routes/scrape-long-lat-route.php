<?php

class ScrapeLongLatRoute
{
    public function registerRoute()
    {
        add_action('rest_api_init', function () {
            register_rest_route('/v1', '/scrape-long-lat', array(
                'methods' => 'POST',
                'callback' => [$this, 'handleScrapLongLat'],
            ));
        });
    }

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
}
