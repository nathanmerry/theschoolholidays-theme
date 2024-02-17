<?php

class InsertCampsRoute
{
    public function registerRoute()
    {
        add_action('rest_api_init', function () {
            register_rest_route('/v1', '/import', array(
                'methods' => 'POST',
                'callback' => [$this, 'handleImportPages'],
            ));
        });
    }

    public function handleImportPages($request)
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

            $openingMonthsKeys = [
                'Feb',
                'Easter',
                'May',
                'Summer',
                'Oct',
                'Christmas',
                'EasterSelect',
                'MaySelect',
                'SummerSelect',
                'OctSelect',
                'ChristmasSelect',
            ];

            foreach ($openingMonthsKeys as $month) {
                if ($item[$month] === 'Yes') {
                    array_push($transformedItem['opening_months'], $month);
                }
            }

            $transformed[] = $transformedItem;

            $this->insertPost($transformedItem);
        }

        return $transformed;
    }

    public function insertPost($data)
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
}
