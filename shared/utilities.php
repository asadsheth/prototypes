<?php
// get a time base weight (a power of 2 here) that can be used to tier-chrono sort things
function time_weighted_power($age, $base = 2)	{
	$exponent = max(10 - floor(log($age + 1) / log($base)), 0);
	// pow can be based on 2 now because score that this gets added to is always between 0 and 1
	return pow(2, $exponent);	
}

function reddit_score($uv, $dv, $reports) {
	$r = $reports;
	// cheat downvotes up for reports and 5 static
	$dv = $dv + 10 * $r + 5;
	$n = $uv + $dv;
	if($n == 0)	{ $score = 0; } else {
		$z = 1.281551565545;
		$p = $uv / $n;
		$left = $p + 1/(2*$n)*$z*$z;
		$right = $z*sqrt($p*(1-$p)/$n + $z*$z/(4*$n*$n));
		$under = 1+1/$n*$z*$z;
		$score = ($left + $right) / $under;
	}

	return $score;
}

// given a vibe id, go get the stream for it. handles some special streams too
function curl_post_stream($vibe_id, $next, $ranking = 'ranked')	{
	// is it a special vibe?
	if(strstr($vibe_id, '@')) {
		if($vibe_id == '@MEGASTREAM')	{
			// get the main stream
			$url = 	'http://mobile-homerun-yql.media.yahoo.com:4080/api/vibe/v1/streams/blended';
		}
		if($vibe_id == '@MEGASTREAMVIDEO')	{
			// get the voltron main stream
			$url = 	'http://mobile-homerun-yql.media.yahoo.com:4080/api/vibe/debug/v2/streams/blended?lang=en-US&region=US&enableNewsroomOTT=true';
		}
	}
	else {
		if($ranking == 'smartChrono') $url = 'http://mobile-homerun-yql.media.yahoo.com:4080/api/vibe/v1/topics/' . $vibe_id . '/smartChronoStream?lang=en-US&region=US';
		else if($ranking == 'ranked') $url = 'http://mobile-homerun-yql.media.yahoo.com:4080/api/vibe/v1/topics/' . $vibe_id . '/rankedStream?lang=en-US&region=US';		
	}

	// looking for a second page?
	if(isset($next))	{
		// get the next page - curl an empty $_POST but add the next param
		$wh = curl_init($url);
		curl_setopt($wh,  CURLOPT_CUSTOMREQUEST, 'POST');
		curl_setopt($wh,  CURLOPT_POSTFIELDS, $next);
		curl_setopt($wh,  CURLOPT_RETURNTRANSFER, true);
		curl_setopt($wh,  CURLOPT_HTTPHEADER, array(
		    'Content-Type: application/json',                                                                                
		    'Content-Length: ' . (strlen($next))
		));
	} else {
		 // curl an empty $_POST
		$wh = curl_init($url);
		curl_setopt($wh,  CURLOPT_CUSTOMREQUEST, 'POST');
		curl_setopt($wh,  CURLOPT_POSTFIELDS, '{}');
		curl_setopt($wh,  CURLOPT_RETURNTRANSFER, true);
		curl_setopt($wh,  CURLOPT_HTTPHEADER, array(
		    'Content-Type: application/json',                                                                                
		    'Content-Length: 2'
		));
	}

	// empty object
	$object = array(
		'items' => array(
			'result' => array()
		)
	);
	$response = curl_exec($wh);
	$httpCode = curl_getinfo($wh, CURLINFO_HTTP_CODE);
	curl_close($wh);           
	if($httpCode != '200') {
		fwrite($logp, 'http request failed for ' . $url . "\n");
	    return $object;
	}		
	
	// try to handle the curl response
	try {
		$object = json_decode($response, true);
	}
	catch(Exception $e) {
		// oops something broke just write back what we had before
		fwrite($logp, 'ooooops hit an exception while requesting new posts' . "\n");
		file_put_contents($CACHE_DIR . "$vibe_id.json", json_encode($posts));
	    return $object;
	}

	// special handling for the special vibes
	if(
		$vibe_id == '@MEGASTREAM'
		|| $vibe_id == '@MEGASTREAMVIDEO'
	)	{
		// filter out non-post items
		$posts = array();
		for($i = 0; $i < count($object['items']['result']); $i++)	{
			if($object['items']['result'][$i]['type'] == 'post')	{
				array_push($posts, $object['items']['result'][$i]);
			}
		}

		$object['items']['result'] = $posts;
	}

	return $object; 
}

// go get the replies for a given comment
function curl_canvass_replies($context_id, $message_id)	{
	$reply_url = 'http://canvass-yql.media.yahoo.com:4080/api/canvass/debug/v1/ns/yahoo_content/contexts/' . $context_id . '/messages/' . $message_id . '/replies?region=US&lang=en-US&count=50';

	$reply_response = file_get_contents($reply_url);
	$reply_response_object = json_decode($reply_response, true);

	return $reply_response_object; 
}

?>