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
];
