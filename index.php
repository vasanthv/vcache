<?php
include 'vcache.class.php';
$cache = new VCache(dirname(__FILE__).'\cache', 'Asia/Kolkata');

//comment this line after you completed
$cache->debug();

//Writing to cache
$cache->write('example1', 'Sample data written in Cache');
$cache->write('example2', array( 'I can cache array too!', 'Suberb na? :)' ), '+10 minutes');

//Getting and printing the data that was cached.
echo $cache->get('example1'); echo '<br />';
echo '<pre>'; print_r($cache->get('example2')); echo '</pre>';

//Deleting a cache.
$cache->delete('example');

//Deleting all expired cache.
$cache->clean();

//Deleting the entire all cache dir.
$cache->purge();
?>
