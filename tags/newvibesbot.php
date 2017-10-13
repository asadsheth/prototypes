<?php
// TODO BACKFILL WITH ROLE OF ALGO
// TODO ADD TOP REPLY TO EACH COMMENT
// TODO ADD THE OTHER TOP COMMENT TO COMPARE

error_reporting(0);
set_time_limit(3600);

// get our shared service for vibes
require('../shared/allvibes.php');

// logger
$logp = fopen('log.log', 'a');
fwrite($logp, '----------------------------' . "\n");

// configs
$CACHE_DIR = './caches/';
$NUM_POSTS_TO_KEEP_PER_VIBE = 1000;
$DEBUG = false;
$ADDITIONAL_VIBE_PAGES = 0;
// echo json_encode($ALL_VIBES); exit;

if($DEBUG)	{
	$ALL_VIBES = array(
		array( 'name' => 'Finance', 'id' => '338950e1-cae3-359e-bfa3-af403b69d694' ),
		array( 'name' => 'Deep Learning', 'id' => '26680209-25eb-3186-ad86-033a8af16364' ),
		array( 'name' => 'Health Care Reform', 'id' => 'a0d7935a-b327-11e5-bc1e-fa163e6f4a7e'),
		array( 'name' => 'Sports', 'id' => '5c839b50-00d3-37e8-a68f-7c03c48d7104', 'slackhook' => 'https://hooks.slack.com/services/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' ),
		array( 'name' => 'Astronomy', 'id' => '28d52c31-89c5-330f-9d52-61eec9fa77cc' )	
	);
}

// write all vibes to disk
file_put_contents($CACHE_DIR . "all_vibes.jsonp", 'jsonp_parse_vibes(' . json_encode($ALL_VIBES) . ');');

// go get the stream
function curl_stream($vibe_id, $next)	{
	// legacy ranking
	// $url = 'http://mobile-homerun-yql.media.yahoo.com:4080/api/vibe/v1/topics/' . $vibe_id . '/rankedStream?lang=en-US&region=US';
	// smart chrono stream_encoding(stream)
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

// go get the stream
function curl_canvass_replies($context_id, $message_id)	{
	$reply_url = 'http://canvass-yql.media.yahoo.com:4080/api/canvass/debug/v1/ns/yahoo_content/contexts/' . $context_id . '/messages/' . $message_id . '/replies?region=US&lang=en-US&count=50';

	$reply_response = file_get_contents($reply_url);
	$reply_response_object = json_decode($reply_response, true);

	return $reply_response_object; 
}


// content work
for($ind = 0; $ind < count($ALL_VIBES); $ind++)	{
	fwrite($logp, 'working on ' . $ALL_VIBES[$ind]['name'] . " - " . $ALL_VIBES[$ind]['id'] . "\n");

	// shortcuts
	$vibe_id = $ALL_VIBES[$ind]['id'];
	$vibe_name = $ALL_VIBES[$ind]['name'];

	// get local posts if they exist
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
		$object = curl_stream($vibe_id);
		fwrite($logp, 'initial remote count: ' . count($object['items']['result']) . "\n");

		// get the next 15
		if(
			// only if this is full
			count($object['items']['result']) == 15
			// or just do it always?
			|| true
		)	{

			$next_token = json_encode($object['meta']['result'][0]);
			for($recurs = 0; $recurs < $ADDITIONAL_VIBE_PAGES; $recurs++)	{
				// get the next items
				$next_obj = (curl_stream($vibe_id, $next_token)); 

				// add them to the original object
				for($i = 0; $i < count($next_obj['items']['result']); $i++) {
					array_push($object['items']['result'], json_decode(json_encode($next_obj['items']['result'][$i]), true));
				} 

				fwrite($logp, 'revised remote count: ' . count($object['items']['result']) . "\n");

				$next_token = json_encode($next_obj['meta']['result'][0]);
			}
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
			else {
				// print_r($obj);
				// exit;
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
					'vibe_name' => $vibe_name,
					'vibe_id' => $vibe_id
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
$every_single_comment = array();
for($ind = 0; $ind < count($ALL_VIBES) && true; $ind++)	{
	fwrite($logp, 'working on comment requests for ' . $ALL_VIBES[$ind]['name'] . " - " . $ALL_VIBES[$ind]['id'] . "\n");

	// id for this vibe
	$vibe_id = $ALL_VIBES[$ind]['id'];
	$vibe_name = $ALL_VIBES[$ind]['name'];

	// messages for this vibe
	// TODO GET THIS FROM FILE FIRST IF IT EXISTS
	$msgs = array();
	
	// all posts for this vibe we have (i think we just finished writing it here in the previous for loop)
	$posts = json_decode(file_get_contents($CACHE_DIR . "$vibe_id.json"), true);

	for($i = 0; $i < count($posts); $i++)	{		
		fwrite($logp, $vibe_name . ': working on comment requests for post #' . $i . ' - ' . $posts[$i]['title'] . "\n");
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

			// echo json_encode($messages[$j]); exit;

			$message_text = $messages[$j]['details']['userText'];
			$message_id = $messages[$j]['messageId'];
			$message_upvotes = $messages[$j]['reactionStats']['upVoteCount'];
			$message_downvotes = $messages[$j]['reactionStats']['downVoteCount'];
			$message_reports = $messages[$j]['reactionStats']['abuseVoteCount'];
			$message_replies = $messages[$j]['reactionStats']['replyCount'];
			$message_author_name = $messages[$j]['meta']['author']['nickname'];
			$message_author_img = $messages[$j]['meta']['author']['image']['url'];
			$message_author_guid = $messages[$j]['meta']['author']['guid'];
			$message_context_id = $messages[$j]['contextId'];
			$message_context_meta = json_decode(json_encode($posts[$i]), true);
			$message_created_at = $messages[$j]['meta']['createdAt'];

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
				'comment_relative_time' => floor((time() - $message_created_at) / 3600),
				'bot' => false
			);

			if(
				// author is real? comment not deleted?
				isset($message_author_name)
				&& isset($message_author_img)
				// upvotes is not negligible?
				// && $uv > 1
				// comment text doesn't have "yahoo" in it?
				&& !strstr(strtolower($message_text), 'yahoo')
			)	{
				array_unshift($msgs, $msg);
				$found_message = true;
			}
		}

		if($found_message)	{
			// good!
			// could get replies?
			// echo $canvass_request_url; exit;
			// $replies_url = 'http://canvass-yql.media.yahoo.com:4080/api/canvass/debug/v1/ns/yahoo_content/contexts/062d0147-9039-3271-929a-e1dc1c216716/messages/dc9571ba-2ff2-431b-8226-e56ba6d42766/replies?region=US&lang=en-US&count=50';
			// $reply_url = 'http://canvass-yql.media.yahoo.com:4080/api/canvass/debug/v1/ns/yahoo_content/contexts/' . $context_id . '/messages/' . $message_id . '/replies?region=US&lang=en-US&count=50';
		}
		else {
			// oops, let's just hack this post together and pretend our bot posted it
			array_unshift($msgs, array(
				'text' => null,
				'message_id' => '?',
				'context_id' => '?',
				'upvotes' => '0',
				'downvotes' => '0',
				'reports' => '0',
				'replies' => '0',
				'author_name' => 'Newsroom Bot',
				'author_img' => 'https://s.yimg.com/ge/myc/newsroom_app_icon_ios.png',
				'author_guid' => '1',
				'score' => 0,
				'context_meta' => json_decode(json_encode($posts[$i]), true),
				'created_at' => 0,
				'comment_relative_time' => $posts[$i]['content_relative_time'],
				'bot' => true
			));
		}
	}

	// rank the messages by time-wieghted-score
	for($i = 0; $i < count($msgs); $i++)	{
		for($j = $i + 1; $j < count($msgs); $j++)  {
			$weighted_timestamp_i = pow(10, max(6 - floor(log($msgs[$i]['comment_relative_time'] + 1) / log(3)), 0)) + $msgs[$i]['score'];
			$weighted_timestamp_j = pow(10, max(6 - floor(log($msgs[$j]['comment_relative_time'] + 1) / log(3)), 0)) + $msgs[$j]['score'];

			
			if($weighted_timestamp_i < $weighted_timestamp_j)	{
				$tmp = json_encode($msgs[$i]);
				$msgs[$i] = json_decode(json_encode($msgs[$j]), true);
				$msgs[$j] = json_decode($tmp, true);
			}
		}
	}

	// write the comments themselves just in case
	file_put_contents($CACHE_DIR . "c_$vibe_id.json", json_encode($msgs));
	file_put_contents($CACHE_DIR . "c_full_$vibe_id.jsonp", 'jsonp_parse_comment_list(' .json_encode($msgs) . ');');
	// add em all to our full tracker
	$every_single_comment = array_merge($every_single_comment, $msgs);

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
			$weighted_timestamp_i = pow(10, max(6 - floor(log($deduped_msgs[$i]['comment_relative_time'] + 1) / log(3)), 0)) + $deduped_msgs[$i]['score'];
			$weighted_timestamp_j = pow(10, max(6 - floor(log($deduped_msgs[$j]['comment_relative_time'] + 1) / log(3)), 0)) + $deduped_msgs[$j]['score'];

			if($weighted_timestamp_i < $weighted_timestamp_j)	{
				$tmp = json_encode($deduped_msgs[$i]);
				$deduped_msgs[$i] = json_decode(json_encode($deduped_msgs[$j]), true);
				$deduped_msgs[$j] = json_decode($tmp, true);
			}
		}
	}

	// go through what we've got left and figure out the reply situation
	for($i = 0; $i < count($deduped_msgs); $i++)	{
		if(
			$deduped_msgs[$i]['message_id'] == '?'
			|| $deduped_msgs[$i]['replies'] == '0'
		) {
			// oops, this one is not a user comment
			continue;
		}
		else {
			$replies_obj = curl_canvass_replies($deduped_msgs[$i]['context_id'], $deduped_msgs[$i]['message_id']);

			$reply_list = $replies_obj['canvassReplies'];

			// parse replies out
			$reps = array();
			for($j = 0; $j < count($reply_list); $j++) {
				$reply_text = $reply_list[$j]['details']['userText'];
				$reply_id = $reply_list[$j]['messageId'];
				$reply_upvotes = $reply_list[$j]['reactionStats']['upVoteCount'];
				$reply_downvotes = $reply_list[$j]['reactionStats']['downVoteCount'];
				$reply_reports = $reply_list[$j]['reactionStats']['abuseVoteCount'];
				$reply_replies = $reply_list[$j]['reactionStats']['replyCount'];
				$reply_author_name = $reply_list[$j]['meta']['author']['nickname'];
				$reply_author_img = $reply_list[$j]['meta']['author']['image']['url'];
				$reply_context_id = $reply_list[$j]['contextId'];
				// $reply_context_meta = json_decode(json_encode($posts[$i]), true);
				$reply_created_at = $reply_list[$j]['meta']['createdAt'];

				// reddit score
		    	$r = $reply_reports;
		    	$uv = $reply_upvotes;
		    	$dv = $reply_downvotes;
		    	// cheating downvotes by adding 10x the report count to penalize reported comments and 5 downvotes to underweight new comments
		    	$dv = $reply_downvotes + 10 * $r + 5;
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
					'text' => $reply_text,
					'message_id' => $reply_id,
					'context_id' => $reply_context_id,
					'upvotes' => $reply_upvotes,
					'downvotes' => $reply_downvotes,
					'reports' => $reply_reports,
					'replies' => $reply_replies,
					'author_name' => $reply_author_name,
					'author_img' => $reply_author_img,
					'score' => $score,
					// 'context_meta' => $reply_context_meta,
					'created_at' => $reply_created_at,
					'comment_relative_time' => floor((time() - $reply_created_at) / 3600),
					'bot' => false
				);

				array_push($reps, $msg);

			}

			// sort the reply list
			for($j = 0; $j < count($reps); $j++) {
				for($k = $j + 1; $k < count($reps); $k++) {
					if($reps[$j]['score'] < $reps[$k]['score'])	{
						$tmp = json_encode($reps[$j]);
						$reps[$j] = json_decode(json_encode($reps[$k]), true);
						$reps[$k] = json_decode($tmp, true);
					}
				}
			}

			$deduped_msgs[$i]['ripostes'] = $reps[0];
		}
	}

	file_put_contents($CACHE_DIR . "c_$vibe_id.jsonp", 'jsonp_parse_posts(' . json_encode($deduped_msgs) . ');');
}

fwrite($logp, '= starting to re-sort all comments' . "\n");
// resort all comments by timestamp-weighted-score
for($i = 0; $i < count($every_single_comment); $i++)	{
	for($j = $i + 1; $j < count($every_single_comment); $j++)	{
		$weighted_timestamp_i = pow(10, max(6 - floor(log($every_single_comment[$i]['comment_relative_time'] + 1) / log(3)), 0)) + $every_single_comment[$i]['score'];
		$weighted_timestamp_j = pow(10, max(6 - floor(log($every_single_comment[$j]['comment_relative_time'] + 1) / log(3)), 0)) + $every_single_comment[$j]['score'];

		// echo "age:\t" . $every_single_comment[$j]['comment_relative_time'] . "\ntimestamp:\t" . $weighted_timestamp_j . "\n";;

		if($weighted_timestamp_i < $weighted_timestamp_j)	{
			$tmp = $every_single_comment[$i];
			$every_single_comment[$i] = $every_single_comment[$j];
			$every_single_comment[$j] = $tmp;
		}
	}	

	// echo 'sorted ' . $i . " of " . count($every_single_comment) . "\n";
}

file_put_contents($CACHE_DIR . "c_allvibes.jsonp", 'jsonp_parse_all_comments(' . json_encode($every_single_comment) . ')'); 
file_put_contents($CACHE_DIR . "c_allvibes.json", json_encode($every_single_comment)); 

fwrite($logp, '=================== END' . "\n");
fclose($logp);
?>
