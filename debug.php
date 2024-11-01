<?php

function dump($thing) {
    $format = php_sapi_name() == 'cli' ? "%s\n" : '<pre>%s</pre>';
    echo sprintf($format, print_r($thing, true));
}

?>