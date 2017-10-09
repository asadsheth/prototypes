<?php
//TODO BACKFILL WITH ROLE OF ALGO
//TODO ADD TOP REPLY TO EACH COMMENT

// TODO ADD THE OTEHR TOP COMMENT TO COMPARE

error_reporting(0);

$logp = fopen('log.log', 'a');
fwrite($logp, '----------------------------' . "\n");

$CACHE_DIR = './caches/';
$DEBUG = false;
$NUM_POSTS_TO_KEEP_PER_VIBE = 1000;

$ALL_VIBES = array(
	array( 'name' => 'Finance', 'id' => '338950e1-cae3-359e-bfa3-af403b69d694' ),
	array( 'name' => 'Politics', 'id' => 'dbb2094c-7d9a-37c0-96b9-7f844af62e78', 'slackhook' => 'https://hooks.slack.com/services/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' ),
	array( 'name' => 'United States', 'id' => 'f5504734-2071-32a6-b729-74a9b3141a44' ),
	array( 'name' => 'Transportation', 'id' => '7fa3e636-f5f1-3e5e-bc73-e15fa8fa8d10' ),
	array( 'name' => 'Society & Culture', 'id' => '5f31157d-3f7d-30fd-af79-9f13bf1f304c' ),
	array( 'name' => 'World News', 'id' => '69f70237-124f-3ea9-acd0-fc922af945e2' ),
	array( 'name' => 'Mar-a-Lago', 'id' => '93c20339-94e8-3bc1-92a6-69efcfe7df32' ),
	array( 'name' => 'InfoSec', 'id' => 'b7ceb013-9918-317e-ba58-bdb2987cf440', 'slackhook' => 'https://hooks.slack.com/services/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' ),
	array( 'name' => 'Feminism', 'id' => '86c832e0-078c-312f-95b9-1e77d1a0b8c6' ),
	array( 'name' => 'Bitcoin', 'id' => '98714316-d8b5-30ad-be71-77f8e9a5eb36', 'slackhook' => 'https://hooks.slack.com/services/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' ),
	array( 'name' => 'Personal Finance', 'id' => 'da8561ef-8822-31d3-9509-283a3d2b5223', 'slackhook' => 'https://hooks.slack.com/services/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' ),
	array( 'name' => 'Listen to America', 'id' => 'bfebc5e7-586f-3476-b78a-558b0bfc2f94' ),
	array( 'name' => 'MLB', 'id' => 'aae76c94-9dc9-11e5-a70a-fa163ecf49c3', 'slackhook' => 'https://hooks.slack.com/services/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' ),
	array( 'name' => 'Celebrity', 'id' => 'b7ddaf4b-9395-34b6-9ddc-e32547089110', 'slackhook' => 'https://hooks.slack.com/services/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' ),
	array( 'name' => 'Sports', 'id' => '5c839b50-00d3-37e8-a68f-7c03c48d7104', 'slackhook' => 'https://hooks.slack.com/services/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' ),
	array( 'name' => 'Deep Learning', 'id' => '26680209-25eb-3186-ad86-033a8af16364' ),
	array( 'name' => 'Internet of Things', 'id' => 'c8a104ba-3365-3ebc-b8f2-cc2a332ce724' ),
	array( 'name' => 'Cricket', 'id' => '9831eeef-324e-3d73-8572-0cba84be3693', 'slackhook' => 'https://hooks.slack.com/services/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' ),
	array( 'name' => 'Korean Tensions', 'id' => '0f201ff2-4afb-11e5-a268-fa163e6f4a7e', 'slackhook' => 'https://hooks.slack.com/services/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' ),
	array( 'name' => 'Artificial Intelligence', 'id' => '106dab7c-e883-3cad-a20c-0ba7af1ec7fe', 'slackhook' => 'https://hooks.slack.com/services/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' ),
	array( 'name' => 'Virtual Reality', 'id' => '2ab16973-6dc1-33f7-9434-6c81f38d1eea' ),
	array( 'name' => 'Odd News', 'id' => '4cc44322-c2d9-3f74-a5db-9b00e071574f' ),
	array( 'name' => 'Astronomy', 'id' => '28d52c31-89c5-330f-9d52-61eec9fa77cc' )
);

if($DEBUG)	{
	$ALL_VIBES = array(
		array( 'name' => 'Finance', 'id' => '338950e1-cae3-359e-bfa3-af403b69d694' ),
		array( 'name' => 'Deep Learning', 'id' => '26680209-25eb-3186-ad86-033a8af16364' ),
		array( 'name' => 'Politics', 'id' => 'dbb2094c-7d9a-37c0-96b9-7f844af62e78', 'slackhook' => 'https://hooks.slack.com/services/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' ),		
		array( 'name' => 'Astronomy', 'id' => '28d52c31-89c5-330f-9d52-61eec9fa77cc' )		
	);
}

function curl_ranked_stream($vibe_id, $next)	{
	$url = 'http://mobile-homerun-yql.media.yahoo.com:4080/api/vibe/v1/topics/' . $vibe_id . '/rankedStream?lang=en-US&region=US';
	$url = 'http://mobile-homerun-yql.media.yahoo.com:4080/api/vibe/v1/topics/' . $vibe_id . '/smartChronoStream?lang=en-US&region=US';

	if(isset($next))	{
		// get th enext stream
		 // curl an empty $_POST
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

	$response = curl_exec($wh);
	$httpCode = curl_getinfo($wh, CURLINFO_HTTP_CODE);
	curl_close($wh);           

	if($httpCode != '200') {
		fwrite($logp, 'http request failed for ' . $url . "\n");
	    continue;
	}		
	
	// empty object
	$object = array(
		'items' => array(
			'result' => array()
		)
	);
	// try to handle the curl response
	try {
		$object = json_decode($response, true);
	}
	catch(Exception $e) {
		// oops something broke just write back what we had before
		fwrite($logp, 'ooooops hit an exception while requesting new posts' . "\n");
		file_put_contents($CACHE_DIR . "$vibe_id.json", json_encode($posts));
	    continue;
	}

	return $object; 
}


// content work
for($ind = 0; $ind < count($ALL_VIBES); $ind++)	{
	fwrite($logp, 'working on ' . $ALL_VIBES[$ind]['name'] . " - " . $ALL_VIBES[$ind]['id'] . "\n");

	// shortcuts
	$vibe_id = $ALL_VIBES[$ind]['id'];
	$vibe_name = $ALL_VIBES[$ind]['name'];

	// only check remotely if the file hasn't been updated in the last...?
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

	// log what we had on disk, if any
	fwrite($logp, 'local post count: ' . count($posts) . "\n");

	// since we always check remote; this is not needed
	if(true)	{
		// get the first 15
		$object = curl_ranked_stream($vibe_id);
		fwrite($logp, 'initial remote count: ' . count($object['items']['result']) . "\n");

		// get the next 15
		if(
			// only if this is full
			count($object['items']['result']) == 15
		)	{
			// get the next items
			$next_obj = (curl_ranked_stream($vibe_id, json_encode($object['meta']['result'][0]))); 

			// add them to the original object
			for($i = 0; $i < count($next_obj['items']['result']); $i++) {
				array_push($object['items']['result'], json_decode(json_encode($next_obj['items']['result'][$i]), true));
			} 

			fwrite($logp, 'revised remote count: ' . count($object['items']['result']) . "\n");			
		}

		// parse it all
		$provider_posts = 0;
		$posted_posts = 0;
		for($i = count($object['items']['result']) - 1; $i >= 0; $i--)	{
			$obj = $object['items']['result'][$i];

			// print_r($obj);

			$post_id = $obj['id'];
			$post_url = $obj['postUrl'];
			$author = $obj['author']['name'];
			$lead_attribution = $obj['leadAttribution'];
			$link = $obj['content']['url'];
			$img = $obj['content']['images'][0]['originalUrl'];
			$title = $obj['content']['title'];
			$content_id = $obj['content']['uuid'];
			$summary = $obj['content']['summary'];
			$published_at = $obj['publishedAt'];
			$topic = $obj['topics'][0]['name'];
			$provider = $obj['content']['provider']['name'];
			$comments_count = $obj['comments']['count'];
			$content_url = $obj['content']['url'];
			$content_published_at = $obj['content']['publishedAt'];

			if($lead_attribution == 'provider')	{
				$provider_posts++;
			}
			
			// go through all the saved posts and see if this post already exists
			$already_exists = false;
			for($j = 0; $j < count($posts); $j++)	{
				if(
					$posts[$j]['post_id'] == $post_id ||
					$posts[$j]['title'] == $title || 
					$posts[$j]['content_id'] == $content_id
				)	{
					// already seen this one
					$already_exists = true;
				}
			}

			if($lead_attribution != 'provider')	{
				// print_r($obj);
			}

			if(
				!$already_exists
				&& $lead_attribution == 'provider'
			)	{
				// fwrite($logp, "\n");
				// fwrite($logp, 'found a new provider post: ' . "\n");
				// fwrite($logp, '--- title: ' . $title . "\n");
				// fwrite($logp, '---- uuid: ' . $content_id . "\n");
				// fwrite($logp, '-comments: ' . $comments_count . "\n");

				// add it to the front of the list!
				array_unshift($posts, array(
					'post_id' => $post_id,
					'post_url' => $post_url,
					'author' => $author,
					'provider' => $provider,
					'lead_attribution' => $lead_attribution,
					'link' => $link,
					'img' => $img,
					'title' => $title,
					'summary' => $summary,
					'published_at' => $published_at,
					'content_id' => $content_id,
					'content_url' => $content_url,
					'content_published_at' => $content_published_at,
					'content_relative_time' => floor((time() - $content_published_at) / 3600),
					'vibe_name' => $vibe_name
				));
			}
			else {
				// fwrite($logp, 'found an old post (or a ugc post): ' . "\n");
				// fwrite($logp, $title . "\n");
			}
		}

		// done looking through the new posts
		fwrite($logp, 'total remote provider posts found: ' . $provider_posts . "\n");
	}

	// ok if we got here things went ok. trim the post list:
	$posts = array_slice($posts, 0, $NUM_POSTS_TO_KEEP_PER_VIBE);

	// write stuff to file
	file_put_contents($CACHE_DIR . "$vibe_id.json", json_encode($posts));
}

// let's go get comment info
// http://canvass-yql.media.yahoo.com:4080/api/canvass/debug/v1/ns/yahoo_content/contexts/164ca269-c3c9-353e-b82a-f1b2199fae44/messages?count=100&sortBy=popular&region=US&lang=en-US&rankingProfile=canvassHalfLifeDecayProfile&userActivity=true					

// comment work
for($ind = 0; $ind < count($ALL_VIBES) && true; $ind++)	{
	fwrite($logp, 'working on comment requests for ' . $ALL_VIBES[$ind]['name'] . " - " . $ALL_VIBES[$ind]['id'] . "\n");

	// id for this vibe
	$vibe_id = $ALL_VIBES[$ind]['id'];

	// messages for this vibe
	// TODO GET THIS FROM FILE FIRST IF IT EXISTS
	$msgs = array();
	
	// all posts for this vibe we have (i think we just finished writing it here in the previous for loop)
	$posts = json_decode(file_get_contents($CACHE_DIR . "$vibe_id.json"), true);

	for($i = 0; $i < count($posts); $i++)	{		
		fwrite($logp, 'working on comment requests for post #' . $i . ' - ' . $posts[$i]['title'] . "\n");
		// extract($posts[$i]);
		// echo 'title: ' . $title . "\n";
		// echo 'provider: ' . $provider . "\n";
		// echo 'content id: ' . $content_id . "\n";
		// echo 'post age: ' . (time() - $published_at) / 3600 . "\n";
		// echo 'content url: ' . $content_url . "\n";
		// echo 'content published at: ' . (time() - $content_published_at) / 3600 . "\n";
		// echo "\n";

		// canvass request
		$canvass_request_url = 'http://canvass-yql.media.yahoo.com:4080/api/canvass/debug/v1/ns/yahoo_content/contexts/' . $posts[$i]['content_id'] . '/messages?count=30&sortBy=popular&region=US&lang=en-US&rankingProfile=canvassHalfLifeDecayProfile&userActivity=true';
		$canvass_response = file_get_contents($canvass_request_url);
		$canvass_response_object = json_decode($canvass_response, true);
		$messages = $canvass_response_object['canvassMessages'];

		// go through all them messages
		$found_message = false;
		for($j = 0; $j < count($messages); $j++)	{

			$message_text = $messages[$j]['details']['userText'];
			$message_id = $messages[$j]['messageId'];
			$message_upvotes = $messages[$j]['reactionStats']['upVoteCount'];
			$message_downvotes = $messages[$j]['reactionStats']['downVoteCount'];
			$message_reports = $messages[$j]['reactionStats']['abuseVoteCount'];
			$message_replies = $messages[$j]['reactionStats']['replyCount'];
			$message_author_name = $messages[$i]['meta']['author']['nickname'];
			$message_author_img = $messages[$i]['meta']['author']['image']['url'];
			$message_context_id = $messages[$i]['contextId'];
			$message_context_meta = json_decode(json_encode($posts[$i]), true);
			$message_created_at = $messages[$i]['meta']['createdAt'];

			// reddit score
	    	$r = $message_reports;
	    	$uv = $message_upvotes;
	    	$dv = $message_downvotes;
	    	// cheating downvotes by adding 10x the report count to penalize reported comments and 5 downvotes to underweight new comments
	    	$dv = $message_downvotes + 10 * $r + 5;
	    	$n = $uv + $dv;
	    	if($n == 0)	{ $score = 0; } else {
	    		$z = 1.281551565545;
	    		$p = $uv / $n;
				$left = $p + 1/(2*$n)*$z*$z;
				$right = $z*sqrt($p*(1-$p)/$n + $z*$z/(4*$n*$n));
				$under = 1+1/$n*$z*$z;
				$score = ($left + $right) / $under;
	    	}

			$msg = array(
				'text' => $message_text,
				'id' => $message_id,
				'context_id' => $message_context_id,
				'upvotes' => $message_upvotes,
				'downvotes' => $message_downvotes,
				'reports' => $message_reports,
				'replies' => $message_replies,
				'author_name' => $message_author_name,
				'author_img' => $message_author_img,
				'score' => $score,
				'context_meta' => $message_context_meta,
				'created_at' => $message_created_at,
				'comment_relative_time' => floor((time() - $message_created_at) / 3600),
				'bot' => false
			);

			if(
				// author is real? comment not deleted?
				isset($message_author_name)
				&& isset($message_author_img)
				// upvotes is not negligible?
				// && $uv > 1
			)	{
				array_unshift($msgs, $msg);
				$found_message = true;
			}
		}

		if($found_message)	{
			// good!
		}
		else {
			// oops, let's just hack this post together and pretend our bot posted it
			array_unshift($msgs, array(
				'text' => null,
				'id' => '?',
				'context_id' => '?',
				'upvotes' => '0',
				'downvotes' => '0',
				'reports' => '0',
				'replies' => '0',
				'author_name' => 'Newsroom Bot',
				'author_img' => 'https://s.yimg.com/ge/myc/newsroom_app_icon_ios.png',
				'score' => 0,
				'context_meta' => json_decode(json_encode($posts[$i]), true),
				'created_at' => 0,
				'comment_relative_time' => $posts[$i]['content_relative_time'],
				'bot' => true
			));
		}
	}

	// rank the messages by score
	for($i = 0; $i < count($msgs); $i++)	{
		for($j = $i + 1; $j < count($msgs); $j++)  {
			if($msgs[$i]['score'] < $msgs[$j]['score'])	{
				$tmp = json_encode($msgs[$i]);
				$msgs[$i] = json_decode(json_encode($msgs[$j]), true);
				$msgs[$j] = json_decode($tmp, true);
			}
		}
	}

	// write the comments themselves just in case
	file_put_contents($CACHE_DIR . "c_$vibe_id.json", json_encode($msgs));

	// spit out a jsonp for this vibe; only one comment per post
	$seen_posts = array();
	$deduped_msgs = array();
	for($i = 0; $i < count($msgs); $i++)	{
		if(!isset($seen_posts[$msgs[$i]['context_meta']['post_id']]))	{
			$seen_posts[$msgs[$i]['context_meta']['post_id']] = true;
			array_push($deduped_msgs, $msgs[$i]);
		}
		else {
			// let's move on, we're covered here already
		}
	}

	// resort this thing based on comment timestamp
	for($i = 0; $i < count($deduped_msgs); $i++)	{
			for($j = $i + 1; $j < count($deduped_msgs); $j++)  {
				$weighted_timestamp_i = pow(10, max(5 - floor(log($deduped_msgs[$i]['comment_relative_time'] + 1) / log(3)), 0)) + $deduped_msgs[$i]['score'];
				$weighted_timestamp_j = pow(10, max(5 - floor(log($deduped_msgs[$j]['comment_relative_time'] + 1) / log(3)), 0)) + $deduped_msgs[$j]['score'];

				if($weighted_timestamp_i < $weighted_timestamp_j)	{
					$tmp = json_encode($deduped_msgs[$i]);
					$deduped_msgs[$i] = json_decode(json_encode($deduped_msgs[$j]), true);
					$deduped_msgs[$j] = json_decode($tmp, true);
				}
			}
		}


	file_put_contents($CACHE_DIR . "c_$vibe_id.jsonp", 'jsonp_parse_posts(' . json_encode($deduped_msgs) . ');');
}

fwrite($logp, '=================== END' . "\n");
fclose($logp);
?>
