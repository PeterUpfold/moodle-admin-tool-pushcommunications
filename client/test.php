<?php

/**
 * Very barebones example client for calling web service.
 *
 * JSON responses but seems to want URL encoded form data as input.
 */

if (php_sapi_name() != 'cli') {
	header('HTTP/1.1 403 Forbidden');
	die('Forbidden');
}

if ($argc != 2) {
	die('Pass the token on the CLI.' . PHP_EOL);
}

$token = $argv[1];
$domainname = 'https://moodle2.testvalley.hants.sch.uk/moodle';

$function = 'tool_pushcommunications_send_push_communication';

$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, $domainname . '/webservice/rest/server.php?moodlewsrestformat=json&wsfunction=' . urlencode($function) . '&wstoken=' . urlencode($token) );
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query( [ 'email' => 'upfoldp@testvalley.hants.sch.uk', 'content' => 'API test' ] ));

curl_exec($ch);

curl_close($ch);
