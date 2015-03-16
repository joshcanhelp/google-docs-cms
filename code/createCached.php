<?php

// cURL test

$ch = curl_init($feedName);

$fh = fopen($cachefile, "w");

curl_setopt($ch, CURLOPT_FILE, $fh);
curl_setopt($ch, CURLOPT_HEADER, 0);

curl_exec($ch);
curl_close($ch);
fclose($fh);

?>