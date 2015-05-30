<?php

require('tokens.php');

function get_content($url) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_URL, $url);
	$data = curl_exec($ch);
	curl_close($ch);
	return $data;
}

function get_title($text) {
	return trim(preg_split('/(\||-)/', $text)[0]);
}

$token = filter_input(INPUT_GET, 'token', FILTER_SANITIZE_STRING);
$command = filter_input(INPUT_GET, 'command', FILTER_SANITIZE_STRING);

if ($token !== $tokens[$command]) {
	http_response_code(403);
	exit;
}

switch ($command) {
	case '/notes':
		$show_number = filter_input(INPUT_GET, 'text', FILTER_VALIDATE_INT, ['min_range' => 1]);
		$domain = ($show_number < 577) ? 'nashownotes.com' : 'noagendanotes.com';
		echo "<http://$show_number.$domain|No Agenda Show #$show_number>";
		break;

	case '/nasearch':
		$term = filter_input(INPUT_GET, 'text', FILTER_SANITIZE_STRING);
		$results = json_decode(get_content('http://search.nashownotes.com/api/search?string=' . $term . '&limit=3'), true);
		$output = [];

		foreach ($results['notes'] as $i => $link) {
			$title = get_title($link['title']);
			$url = $link['urls'][1];
			$output[] = ($i + 1) . ". <$url|$title>";
		}

		echo implode("\n", $output);
		break;
}
