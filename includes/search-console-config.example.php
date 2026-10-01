<?php
/*
 * Copy to includes/search-console-config.php for local development.
 * Do not commit the copied file or a service-account JSON key.
 */
return [
    'property' => 'https://www.iucedu.com/',
    'credentials_file' => 'C:/secure/iuc-search-console-service-account.json',
    'cache_ttl' => 600,
    'row_limit' => 5000,
    'brand_terms' => ['iuc', 'iuc edu', 'iuc computers', 'iuc computer education'],
    'ga4_measurement_id' => 'G-H9L990V9Z2',
    'ga4_property_id' => '123456789',
    'ga4_realtime_cache_ttl' => 15,
];
