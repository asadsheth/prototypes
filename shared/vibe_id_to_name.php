<?php
// search api
// http://mobile-homerun-yql.vibe.production.omega.gq1.yahoo.com:4080/api/vibe/v1/search/topics?query=health&enforcePostAcl=false

// vibe meta api with id
// http://mobile-homerun-yql.media.yahoo.com:4080/api/vibe/v1/editorial/topics/944d17a2-7cd5-337f-af95-2c7dd5e431f4

$vibes = file('tmp.csv');

echo 'Vibe ID' . "\t" . 'Vibe Name' . "\t" . 'Uniques' . "\t" . 'Followers' . "\t" . 'Views (Week)' . "\n";

$vibe_array = array();
for($i = 1; $i < count($vibes); $i++) {
	$vibe_info = explode(',', trim($vibes[$i]));

	try {
		$url = 'http://mobile-homerun-yql.media.yahoo.com:4080/api/vibe/v1/editorial/topics/' . $vibe_info[0];
		$resp = @file_get_contents($url);

		if(
			strlen($resp) > 20
			|| strlen($vibe_info[0]) != 36
		) {
			$obj = json_decode($resp, true);

			$vibe_name = $obj['topics']['result'][0]['name'];
			// $vibe_id = $obj['topics']['result'][0]['id'];
			$vibe_id = $vibe_info[0];
			$vibe_uniques = $vibe_info[1];
			$vibe_subs = $obj['topics']['result'][0]['userSubscriberCount'];
			$vibe_views = $vibe_info[2];

			echo $vibe_id . "\t" . $vibe_name . "\t" . $vibe_uniques . "\t" . $vibe_subs . "\t" . $vibe_views ."\n";
			// echo 'array( \'name\' => \'' . $vibe_name . '\', \'id\' => \'' . $vibe_id . '\' ),' . "\n";
		}
		else {
			echo $vibe_info[0] . "\t" . '?' . "\t" . $vibe_info[1] . "\t" .  '?' . "\t"  . $vibe_info[2] . "\n";
		}
	} catch(Exception $e) {
		continue;
	}
}

?>
