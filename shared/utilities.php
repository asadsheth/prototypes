<?php
// apis for later use
// RELATED CONTENT FOR A UUID:
// http://ga-hr.slingstone.yahoo.com:4080/score/v9/homerun/en-US/unified/get_increased_related_content?enable.relatedstory.feature.cache=false&rs_min_one_cluster=2&storyline_count=20&uuids=aad0dd21-0b10-3b4e-8638-9a64ee752ff5&debug=true&tracelevel=10&storyline_fallback=true

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
function curl_post_stream($vibe_id, $next, $ranking)	{
	// is it a special vibe?
	if(strstr($vibe_id, '@')) {
		if($vibe_id == '@MEGASTREAM')	{
			// get the main stream
			$url = 	'http://mobile-homerun-yql.media.yahoo.com:4080/api/vibe/v1/streams/blended';
		}
		else if($vibe_id == '@MEGASTREAMVIDEO')	{
			// get the voltron main stream
			$url = 	'http://mobile-homerun-yql.media.yahoo.com:4080/api/vibe/debug/v2/streams/blended?lang=en-US&region=US&enableNewsroomOTT=true';
		}
		else if($vibe_id == '@NTKVIDEO')	{
			// get the voltron main stream
			$url = 	'http://mobile-homerun-yql.media.yahoo.com:4080/api/vibe/debug/v2/streams/blended?lang=en-US&region=US&enableNewsroomOTT=true';
		}
		else if($vibe_id == '@MEGASTREAMVIDEODWELLTIME') {
			// DISREGARD ME FOR NOW, THIS IS NOT THOUGHT OUT
			// 'http://mobile-homerun-yql.media.yahoo.com:4080/api/vibe/debug/v2/streams/blended?lang=en-US&region=US&enableNewsroomOTT=true'
		}
	}
	else {
		if($ranking == 'smartChrono') $url = 'http://mobile-homerun-yql.media.yahoo.com:4080/api/vibe/v1/topics/' . $vibe_id . '/smartChronoStream?lang=en-US&region=US&enableNewsroomOTT=true';
		// if($ranking == 'smartChrono') $url = 'https://vibe-yql-burn.media.yahoo.com/api/vibe/v3/topics/' . $vibe_id . '/smartChronoStream?lang=en-US&region=US&enableNewsroomOTT=true';
		else if($ranking == 'ranked') $url = 'http://mobile-homerun-yql.media.yahoo.com:4080/api/vibe/v1/topics/' . $vibe_id . '/rankedStream?lang=en-US&region=US&enableNewsroomOTT=true';		
	}

	// echo $ranking . "\n";

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

	if($vibe_id == '@NTKVIDEO' && isset($next))	{
		return $object;
	}

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
	    return $object;
	}

	// special handling for the special vibes
	if($vibe_id == '@NTKVIDEO') {
		$ntk_items = $object['items']['result'][0]['items'];
		$object['items']['result'] = $ntk_items;
	}
	else if(
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

// function that gets all messages for a given user
function get_user_message_history($guid, $namespace_free = false)	{
	if($namespace_free)	{
		// for asad, but no namespace restriction: http://canvass-yql.media.yahoo.com:4080/api/canvass/debug/v1/users/FI3SFWX5YUMNC57AOOIW2UTAC4/messages?count=10&sortBy=createdAt&region=US&lang=en-US
		$url = 'http://canvass-yql.media.yahoo.com:4080/api/canvass/debug/v1/users/' . $guid . '/messages?count=50&sortBy=createdAt&region=US&lang=en-US';
	}
	else {
		// yahoo content namespace only
		$url = 'http://canvass-yql.media.yahoo.com:4080/api/canvass/debug/v1/ns/yahoo_content/users/' . $guid . '/messages?region=US&lang=en-US&count=30&sortBy=createdAt';

	}

	// for asad: FI3SFWX5YUMNC57AOOIW2UTAC4 - http://canvass-yql.media.yahoo.com:4080/api/canvass/debug/v1/ns/yahoo_content/users/FI3SFWX5YUMNC57AOOIW2UTAC4/messages?region=US&lang=en-US&count=30&sortBy=createdAt
	// no namespace restriction: http://canvass-yql.media.yahoo.com:4080/api/canvass/debug/v1/users/FI3SFWX5YUMNC57AOOIW2UTAC4/messages?count=10&sortBy=createdAt&region=US&lang=en-US
	// $url = 'http://canvass-yql.media.yahoo.com:4080/api/canvass/debug/v1/ns/yahoo_content/users/' . $guid . '/messages?region=US&lang=en-US&count=30&sortBy=createdAt'; // no namespace restriction
	
	$response = file_get_contents($url);
	$response_object = json_decode($response, true);
	$messages = $response_object['canvassMessages'];
	
	$msgs = array();
	for($j = 0; $j < count($messages); $j++) {
		$message_text = $messages[$j]['details']['userText'];
		$message_id = $messages[$j]['messageId']; // when reply id is not null, this is the id of the message that is being replied to. seems really weird. when it is null i guess it's the id of the message itself
		$message_reply_id = $messages[$j]['replyId']; // when this is not null, weirdly this is the id of the newly created reply
		// TODO THIS NEEDS SOME THINKING, DID WE BREAK SOMETHING?
		$message_upvotes = $messages[$j]['reactionStats']['upVoteCount'];
		$message_downvotes = $messages[$j]['reactionStats']['downVoteCount'];
		$message_reports = $messages[$j]['reactionStats']['abuseVoteCount'];
		$message_replies = $messages[$j]['reactionStats']['replyCount'];
		$message_author_name = $messages[$j]['meta']['author']['nickname'];
		$message_author_img = $messages[$j]['meta']['author']['image']['url'];
		$message_author_guid = $messages[$j]['meta']['author']['guid'];
		$message_context_id = $messages[$j]['contextId'];
		// $message_context_meta = json_decode(json_encode($posts[$i]), true);
		$message_context_info = $messages[$j]['meta']['contextInfo'];
		$message_created_at = $messages[$j]['meta']['createdAt'];
		$score = reddit_score($message_upvotes, $message_downvotes, $message_reports);

		$msg = array(
			'text' => $message_text,
			'message_id' => $message_id,
			'message_reply_id' => $message_reply_id,
			'context_id' => $message_context_id,
			'upvotes' => $message_upvotes,
			'downvotes' => $message_downvotes,
			'reports' => $message_reports,
			'replies' => $message_replies,
			'author_name' => $message_author_name,
			'author_img' => $message_author_img,
			'author_guid' => $message_author_guid,
			'score' => $score,
			'context_meta' => null,
			'context_info' => $message_context_info,
			'created_at' => $message_created_at,
			'comment_relative_time' => floor((time() - $message_created_at) / 3600),
			'bot' => false
		);

		array_push($msgs, $msg);
	}

	return $msgs;
}

// function that goes through all my comments and constructs a article-uuid-to-vibes map; can show which articles show up in multiple vibes
function get_uuid_to_vibes($all_comments)	{
	$obj = $all_comments;
	$uuid_to_vibes = array();

	for($i = 0; $i < count($obj); $i++)	{
		$uuid = $obj[$i]['context_meta']['content_id'];
		$guid = $obj[$i]['author_guid'];
		$vibe_name = $obj[$i]['context_meta']['vibe_name'];
		$vibe_id = $obj[$i]['context_meta']['vibe_id'];

		// skip our special vibes
		if(!strstr($vibe_id, '@')) {
			array_push($uuids[$uuid], $obj[$i]);

			// build the article to vibe mapping
			if(!isset($uuid_to_vibes[$uuid]))	{
				$uuid_to_vibes[$uuid] = array();
			}
			$uuid_to_vibes[$uuid][$vibe_id] = $vibe_name;
		}
	}

	return $uuid_to_vibes;
}

// function that goes through all comments and figures out who posted multiple comments
function derive_multi_posters($all_comments)	{
	$obj = $all_comments;
	$authors = array();

	for($i = 0; $i < count($obj); $i++)	{
		$uuid = $obj[$i]['context_meta']['content_id'];
		$guid = $obj[$i]['author_guid'];
		$vibe_name = $obj[$i]['context_meta']['vibe_name'];
		$vibe_id = $obj[$i]['context_meta']['vibe_id'];

		// skip our special vibes
		if(!strstr($vibe_id, '@')) {
			// build a map of article uuids with comments by author
			if($guid != '1')	{
				if(!isset($authors[$guid]))	{
					$authors[$guid] = array();
				}

				$authors[$guid][$uuid] = true;
			}
		}
	}

	// go through and identify all the authors with multiple contributions
	$multi_poster_guids = array();
	foreach($authors as $guid => $bunch)	{
		$uuid_count = 0;
		
		// count the uuids in the bunch
		foreach($bunch as $uuid)	{
			$uuid_count++;
		}
		
		if($uuid_count > 1) array_push($multi_poster_guids, $guid);
	}

	return $multi_poster_guids;	
}

function create_post_from_posts_response($obj)	{

}

// takes a vibe obj (id, name, ranking algo to be used) as input, sends back posts
function get_vibe_posts($vibe_obj) {
	global $logp;
	global $ADDITIONAL_VIBE_PAGES;
	global $NUM_POSTS_TO_KEEP_PER_VIBE;

	$vibe_id = $vibe_obj['id'];
	$vibe_name = $vibe_obj['name'];
	$vibe_ranking = isset($vibe_obj['ranking']) ? $vibe_obj['ranking'] : 'smartChrono'; // default to smartChrono; other choice is 'ranked'
	// $vibe_ranking = isset($vibe_obj['ranking']) ? $vibe_obj['ranking'] : 'ranked'; // default to ranked

	// get local posts if they exist
	// (currently disabled - no local post caching)
	if(
		false 
		&& file_exists($CACHE_DIR . "$vibe_id.json")
	)	{
		// it's on disk!
		$posts = json_decode(file_get_contents($CACHE_DIR . "$vibe_id.json"), true);
	}
	else {
		// start fresh
		$posts = array();
	}

	// get the first page of posts - id, next token, ranking
	$object = curl_post_stream($vibe_id, null, $vibe_ranking);
	// log what we found
	// fwrite($logp, 'initial remote count: ' . count($object['items']['result']) . "\n");

	// recursively get the next page, as many times as config'd
	$next_token = json_encode($object['meta']['result'][0]);
	for($recurs = 0; $recurs < $ADDITIONAL_VIBE_PAGES; $recurs++)	{
		// get the next items
		$next_obj = (curl_post_stream($vibe_id, $next_token, $vibe_ranking)); 

		// add them to the original object
		for($i = 0; $i < count($next_obj['items']['result']); $i++) {
			array_push($object['items']['result'], json_decode(json_encode($next_obj['items']['result'][$i]), true));
		}

		// log what we found again
		fwrite($logp, 'revised remote count: ' . count($object['items']['result']) . "\n");

		// store the next token from this result in case this loop runs again
		$next_token = json_encode($next_obj['meta']['result'][0]);
	}

	if($vibe_name != 'Top Stories') {
		// echo json_encode($object); exit;
	}

	// go through all the posts and pre-parse
	for($i = 0; $i < count($object['items']['result']); $i++)	{
		// make it easier to parse
		$obj = $object['items']['result'][$i];

		// pull out the meta we need
		$post_id = $obj['id'];
		$post_url = $obj['postUrl'];
		$author = $obj['author']['name'];
		$lead_attribution = $obj['leadAttribution'];
		$link = $obj['content']['url'];
		$img = $obj['content']['images'][0]['originalUrl'];
		$resolutions = $obj['content']['images'][0]['resolutions'];
		$imgh = $obj['content']['images'][0]['originalHeight'];
		$imgw = $obj['content']['images'][0]['originalWidth'];		
		$title = $obj['content']['title'];
		$content_id = $obj['content']['uuid'];
		$summary = $obj['content']['summary'];
		$published_at = $obj['publishedAt'];
		$topic = $obj['topics'][0]['name'];
		$provider = $obj['content']['provider']['name'];
		$provider_url = $obj['content']['provider']['url'];
		$provider_image = $obj['content']['provider']['image'] ? $obj['content']['provider']['image']['originalUrl'] : '';
		$comments_count = $obj['comments']['count'];
		$content_url = $obj['content']['url'];
		$content_published_at = $obj['content']['publishedAt'];
		$content_type = $obj['content']['type'];
		$content_aspect = $obj['content']['aspectRatio'];

		// go through all the saved posts and see if this post already exists - this will dedupe if needed
		$already_exists = false;
		for($j = 0; $j < count($posts); $j++)	{
			if(
				$posts[$j]['post_id'] == $post_id ||
				$posts[$j]['title'] == $title || 
				$posts[$j]['content_id'] == $content_id
			)	{
				// already seen this one
				$already_exists = true;
				fwrite($logp, 'dupe found: ' . $posts[$j]['post_id'] . "\n");
			}
		}

		if(
			!$already_exists
			&& $lead_attribution == 'provider'
			&& (
				$provider != 'PR Newswire'
				&& $provider != 'Business Wire'
				&& $provider != 'GlobeNewswire'
				&& $provider != 'ACCESSWIRE'
				&& $provider != 'Newsfile'
			)
		)	{
			// add it to the front of the list!
			array_push($posts, array(
				'post_id' => $post_id,
				'post_url' => $post_url,
				'author' => $author,
				'provider' => $provider,
				'provider_url' => $provider_url,
				'provider_image' => $provider_image,
				'lead_attribution' => $lead_attribution,
				'link' => $link,
				'imgresolutions' => $resolutions,
				'img' => $img,
				'imgw' => $imgw,
				'imgh' => $imgh,
				'title' => $title,
				'summary' => $summary,
				'published_at' => $published_at,
				'content_id' => $content_id,
				'content_url' => $content_url,
				'content_published_at' => $content_published_at,
				'content_time' => $content_published_at,
				'content_relative_time' => floor((time() - $content_published_at) / 3600),
				'reaction_count' => $comments_count,
				'vibe_name' => $vibe_name,
				'vibe_id' => $vibe_id,
				'content_type' => $content_type,
				'aspect' => $content_aspect
			));
		}
		else {
			// fwrite($logp, 'found an old post (or a ugc post): ' . "\n");
			// fwrite($logp, $title . "\n");
		}
	}

	// ok if we got here things went ok. trim the post list:
	$posts = array_slice($posts, 0, $NUM_POSTS_TO_KEEP_PER_VIBE);

	return $posts;
}

// for a given context, gets the messages; as written only works for article uuids and yahoo-content (not vibe canvass namespace)
function get_context_comments($message_context) {
	global $COMMENTS_PER_POST;
	if(!isset($COMMENTS_PER_POST)) {
		$COMMENTS_PER_POST = 5;
	}

	// canvass request
	$canvass_request_url = 'http://canvass-yql.media.yahoo.com:4080/api/canvass/debug/v1/ns/yahoo_content/contexts/' . $message_context . '/messages?count=' . $COMMENTS_PER_POST . '&sortBy=popular&region=US&lang=en-US&rankingProfile=canvassHalfLifeDecayProfile&userActivity=true';
	// fwrite($logp, $canvass_request_url . "\n");
	$canvass_response = file_get_contents($canvass_request_url);
	$canvass_response_object = json_decode($canvass_response, true);
	$messages = $canvass_response_object['canvassMessages'];

	return $messages;
}

// takes the raw canvass json and transforms it into our msg things
function create_msg_from_canvass_response($message, $postcontext)	{
	global $WHITELIST_COMMENTER_GUIDS;

	$message_text = $message['details']['userText'];
	$message_id = $message['messageId'];
	$message_upvotes = $message['reactionStats']['upVoteCount'];
	$message_downvotes = $message['reactionStats']['downVoteCount'];
	$message_reports = $message['reactionStats']['abuseVoteCount'];
	$message_replies = $message['reactionStats']['replyCount'];
	$message_author_name = $message['meta']['author']['nickname'];
	$message_author_img = $message['meta']['author']['image']['url'];
	$message_author_guid = $message['meta']['author']['guid'];
	$message_context_id = $message['contextId'];
	$message_context_meta = json_decode(json_encode($postcontext), true);
	$message_created_at = $message['meta']['createdAt'];
	$score = reddit_score($message_upvotes, $message_downvotes, $message_reports);

	$msg = array(
		'text' => $message_text,
		'message_id' => $message_id,
		'context_id' => $message_context_id,
		'upvotes' => $message_upvotes,
		'downvotes' => $message_downvotes,
		'reports' => $message_reports,
		'replies' => $message_replies,
		'author_name' => $message_author_name,
		'author_img' => $message_author_img,
		'author_guid' => $message_author_guid,
		'score' => $score,
		'context_meta' => $message_context_meta,
		'created_at' => $message_created_at,
		'comment_time' => $message_created_at,
		'comment_relative_time' => floor((time() - $message_created_at) / 3600),
		'bot' => false,
		'whitelisted_commenter' => isset($WHITELIST_COMMENTER_GUIDS[$message_author_guid])
	);

	return $msg;		
}

// given a context id and a message id, goes and gets the message meta
function get_message_from_message_and_context_ids($message_id, $context_id) {
	$url = 'http://canvass-yql.media.yahoo.com:4080/api/canvass/debug/v1/ns/yahoo_content/contexts/' . $context_id . '/messages/' . $message_id . '?region=US&lang=en-US';

	$response = file_get_contents($url);
	$response_object = json_decode($response, true);
	$message_meta = $response_object['canvassMessage'];

	return create_msg_from_canvass_response($message_meta, array());
}

// get all the whitelisted comments; returns a map of [context] => [msg object]
function get_whitelisted_comments() {
	global $WHITELIST_COMMENTER_GUIDS;
	$whitelisted_message_id_map = array();

	foreach($WHITELIST_COMMENTER_GUIDS as $guid => $val)	{
		// go get the messages that this user has ever posted
		$messages = get_user_message_history($guid);

		// debug out the guids and their counts
		// echo $guid . "\t" . count($messages) . "\n";

		// add them to our list
		for($i = 0; $i < count($messages); $i++)	{
			// if this is asad's comments, we'll add the replied-to comment as a whitelisted one, instead of the comment itself (if it's a reply)
			if(
				// check if asad's
				$guid == 'FI3SFWX5YUMNC57AOOIW2UTAC4'
				// and check if it's a reply
				&& strlen($messages[$i]['message_reply_id']) == 36
			)	{
				// ok so now we know this was a reply to message id $messages[$i]['message_id'] in $messages[$i]['context_id']. go get the message meta:
				$message_meta = get_message_from_message_and_context_ids($messages[$i]['message_id'], $messages[$i]['context_id']);
				// add the retrieved parent message to our list
				$whitelisted_message_id_map[$messages[$i]['context_id']] = $message_meta;

				
			}
			else {
				// it's just a comment by a whitelisted user
				$whitelisted_message_id_map[$messages[$i]['context_id']] = $messages[$i];
			}
		}
	}

	return $whitelisted_message_id_map;
}

// TODO USE THIS FOR SUGGESTED VIBES
// go get the list of suggested vibes from our editorial team
function get_suggested_vibes() {
	// featured by editorial?
	$url = 'http://mobile-homerun-yql.vibe.production.omega.gq1.yahoo.com:4080/api/vibe/v1/featured/topics';
	$response = file_get_contents($url);
	$obj = json_decode($response, true);
	$topics = $obj['topics']['result'];

	return $topics;
}

?>