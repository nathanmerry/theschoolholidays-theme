<?php

require_once __DIR__ . '/functions/utils.php';
require_once __DIR__ . '/functions/enqueue.php';
require_once __DIR__ . '/functions/camp-fields.php';
require_once __DIR__ . '/functions/search-camps.php';
require_once __DIR__ . '/functions/routes/insert-camps-route.php';
require_once __DIR__. '/functions/routes/scrape-long-lat-route.php';

add_action('init', function () {
    (new CampFields)->registerFields();
    (new InsertCampsRoute)->registerRoute();
    (new ScrapeLongLatRoute)->registerRoute();
});
