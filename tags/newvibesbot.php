<?php
// TODO BACKFILL WITH ROLE OF ALGO
// TODO ADD TOP REPLY TO EACH COMMENT
// TODO ADD THE OTHER TOP COMMENT TO COMPARE
// DESIGN newsroom stream designs: https://www.dropbox.com/s/fm8k7g5my8dqd5i/explore1.png?dl=0
// DESIGN newsroom stream designs (lighter): https://www.dropbox.com/s/sxf4hvnykvy8tcu/explore2.png?dl=0
// DESIGN web mobile cards: https://www.dropbox.com/s/fwyladpe3m8vr0p/stream_master_nov13.png?dl=0
error_reporting(0);
set_time_limit(3600);
ini_set('memory_limit', '512M');

// get our shared service for vibes
require('../shared/allvibes.php');
require('../shared/utilities.php');

// logger
$logp = fopen('log.log', 'a');
fwrite($logp, '----------------------------' . "\n");

// configs
$CACHE_DIR = './caches/';
$NUM_POSTS_TO_KEEP_PER_VIBE = 1000;
$DEBUG = false;
$ADDITIONAL_VIBE_PAGES = 0;
$REPLY_FETCH_QUALITY_THRESHOLD = 0.99;
$REPLY_FETCH_COUNT_THRESHOLD = 100;
$COMMENTS_PER_POST = 0;
$WHITELIST_COMMENTER_GUIDS = array(
	'FI3SFWX5YUMNC57AOOIW2UTAC4' => 1, // asad
	'6NOU2PIONBDXJJKHMGGFXT4ZNE' => 1, // rafi
	'HRMMTS66W6MRK7JA2YM3LKR3DA' => 1, // tenni
	'ET7XMWF2G3A3FTE3YEYZ2GAM7I' => 1  // cris
);

if($DEBUG)	{
	$ALL_VIBES = array(
		// array( 'name' => '@Megastream', 'id' => '@MEGASTREAM' ),
		array( 'name' => 'Featured', 'id' => '@NTKVIDEO' ),
		array( 'name' => 'Recommended For You', 'id' => '@MEGASTREAMVIDEO' ),
		array( 'name' => '[smartChrono] NBA', 'id' => 'e238b3d0-c6d5-11e5-af54-fa163e2c24a6', 'ranking' => 'smartChrono' ),
		// array( 'name' => '[legacy] NBA', 'id' => 'e238b3d0-c6d5-11e5-af54-fa163e2c24a6', 'ranking' => 'ranked' )
	);
}

// TODO MOVE THIS OVER TO UTILS AND USE IT
// go get the list of suggested vibes from our editorial team
function get_suggested_vibes() {
	// featured by editorial?
	$url = 'http://mobile-homerun-yql.vibe.production.omega.gq1.yahoo.com:4080/api/vibe/v1/featured/topics';
	$response = file_get_contents($url);
	$obj = json_decode($response, true);
	$topics = $obj['topics']['result'];

	return $topics;
}

// write the vibe list to disk
file_put_contents($CACHE_DIR . "all_vibes.json", json_encode($ALL_VIBES));
file_put_contents($CACHE_DIR . "all_vibes.jsonp", 'jsonp_parse_vibes(' . json_encode($ALL_VIBES) . ');');

// content work
for($ind = 0; $ind < count($ALL_VIBES); $ind++)	{
	fwrite($logp, 'working on ' . $ALL_VIBES[$ind]['name'] . " - " . $ALL_VIBES[$ind]['id'] . "\n");

	// go get the posts
	$posts = get_vibe_posts($ALL_VIBES[$ind]);

	// shortcuts
	$vibe_id = $ALL_VIBES[$ind]['id'];
	$vibe_name = $ALL_VIBES[$ind]['name'];

	// write stuff to file
	file_put_contents($CACHE_DIR . "$vibe_id.json", json_encode($posts));
	// write stuff to file
	file_put_contents($CACHE_DIR . "$vibe_id.jsonp", 'jsonp_parse_post_list(' .json_encode($posts) . ');');
}

// let's go get comment info
$every_single_comment = array();
for($ind = 0; $ind < count($ALL_VIBES); $ind++)	{
	fwrite($logp, 'working on comment requests for ' . $ALL_VIBES[$ind]['name'] . " - " . $ALL_VIBES[$ind]['id'] . "\n");

	// shortcuts  for this vibe
	$vibe_id = $ALL_VIBES[$ind]['id'];
	$vibe_name = $ALL_VIBES[$ind]['name'];

	// all posts for this vibe we have (i think we just finished writing it here in the previous for loop)
	$posts = json_decode(file_get_contents($CACHE_DIR . "$vibe_id.json"), true);

	// messages for this whole vibe
	// TODO GET THIS FROM FILE FIRST IF IT EXISTS
	// $msgs = get_vibe_messages($posts);
	$msgs = array();

	for($i = 0; $i < count($posts); $i++)	{		
		// fwrite($logp, $vibe_name . ': working on comment requests for post #' . $i . ' - ' . $posts[$i]['title'] . "\n");

		// call the function to get comments for this context
		if($COMMENTS_PER_POST > 0) {
			$messages_for_this_post = get_context_comments($posts[$i]['content_id']);
		}
		else {
			$messages_for_this_post = array();
		}

		// go through all them message and see if we got any
		$found_message = false;
		// $post_comment_blob = '';
		for($j = 0; $j < count($messages_for_this_post); $j++)	{
			$msg = create_msg_from_canvass_response($messages_for_this_post[$j], $posts[$i]);

			if(
				// author is real? comment not deleted?
				isset($msg['author_name'])
				&& isset($msg['author_img'])
				// upvotes is not negligible?
				// && $uv > 1
				// comment text doesn't have "yahoo" in it?
				&& !strstr(strtolower($msg['text']), 'yahoo')
			)	{
				// add it to the end!
				array_push($msgs, $msg);
				$found_message = true;
				// track the blob for later
				// $post_comment_blob .= $message_text . ' ';
			}
		}

		/*
		// TODO split the blob and tag cloud logic out into a function
		// split the blob
		$post_comment_tokens = explode(' ', $post_comment_blob);
		$post_comment_token_histogram = array();
		$post_comment_bigram_histogram = array();
		// this is disabled for now
		for($j = 0; false && $j < count($post_comment_tokens); $j++)	{
			$post_comment_tokens[$j] = strtolower(trim($post_comment_tokens[$j], "\W\.\?\,\!"));
			$token = $post_comment_tokens[$j];

			if(strlen($token) == 0) continue;

			if($j > 0 && j < count($post_comment_tokens) - 1)	{
				$bigram = $post_comment_tokens[$j - 1] . ' ' . $post_comment_tokens[$j];
				$post_comment_bigram_histogram[$bigram] = isset($post_comment_bigram_histogram[$bigram]) ? ($post_comment_bigram_histogram[$bigram] + 1) : 1;
			}

			$post_comment_token_histogram[$token] = isset($post_comment_token_histogram[$token]) ? ($post_comment_token_histogram[$token] + 1) : 1;
		}
		// checking to see if it's an empty histogram
		if(strlen(json_encode($post_comment_token_histogram)) > 5000) {
			// echo json_encode($post_comment_token_histogram);
			// exit;
		}
		*/

		// now package this post to be shown - comment first, or bot first when there are no comments
		if($found_message)	{
			// good!
		}
		else {
			// oops, let's just add a "comment" to hack this post together and pretend our bot posted it
			array_push($msgs, array(
				'text' => null,
				'message_id' => '?',
				'context_id' => $posts[$i]['content_id'],
				'post_id' => $posts[$i]['post_id'],
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
				'comment_time' => $posts[$i]['content_relative_time'],				
				'comment_relative_time' => $posts[$i]['content_relative_time'],
				'bot' => true,
				'whitelisted_commenter' => false
			));
		}
	}

	// done with ALL the posts for this vibe

	// go through what we've got now for this whole vibe and figure out the reply situation
	for($i = 0; $i < count($msgs); $i++)	{
		if(
			// skip posts we already know had no messages
			$msgs[$i]['message_id'] == '?'
			// skip messages with few replies
			|| $msgs[$i]['replies'] < $REPLY_FETCH_COUNT_THRESHOLD
			// skip messages where the message itself is low quality
			|| $msgs[$i]['score'] < $REPLY_FETCH_QUALITY_THRESHOLD
		) {
			// oops, this one is not a user comment orrr it has no replies or it is low-scoring
			continue;
		}
		else {
			echo $i . "\n";
			fwrite($logp, $vibe_name . ': working on comment replies for message #' . $i . ' of ' . count($msgs) . "\n");
			$replies_obj = curl_canvass_replies($msgs[$i]['context_id'], $msgs[$i]['message_id']);
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
				$score = reddit_score($reply_upvotes, $reply_downvotes, $reply_reports);

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

				// add this message to the list of replies
				array_push($reps, $msg);
			}

			// sort the reply list by score - NOT time-weighted score
			for($j = 0; $j < count($reps); $j++) {
				for($k = $j + 1; $k < count($reps); $k++) {
					if($reps[$j]['score'] < $reps[$k]['score'])	{
						$tmp = $reps[$j];
						$reps[$j] = $reps[$k];
						$reps[$k] = $tmp;
					}
				}
			}

			// only store one reply to the message at this point
			$msgs[$i]['ripostes'] = $reps[0];
		}
	}

	// before we do any re-sorting, write a raw deduped post list. this should match the stream sorting. start by spitting out a jsonp for this vibe; only one comment per post - the algo "top" comment
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
	file_put_contents($CACHE_DIR . "c_rawrank_$vibe_id.json", json_encode($deduped_msgs));	
	file_put_contents($CACHE_DIR . "c_rawrank_$vibe_id.jsonp", 'jsonp_parse_posts(' .json_encode($deduped_msgs) . ');');

	// TODO now re-rank by just tiered content time
	// for($i = 0; $i < count($msgs); $i++)	{
		// for($j = $i + 1; $j < count($msgs); $j++)  {			
			// if($msgs[$i]['score'] < $msgs[$j]['score'])	{
				// $tmp = $msgs[$i];
				// $msgs[$i] = $msgs[$j];
				// $msgs[$j] = $tmp;
			// }
		// }
	// }

	// now just rank all of the messages by message score
	for($i = 0; $i < count($msgs); $i++)	{
		for($j = $i + 1; $j < count($msgs); $j++)  {			
			// don't demote whitelisted dudes
			if($msgs[$i]['whitelisted_commenter']) continue;

			if(
				$msgs[$i]['score'] < $msgs[$j]['score']
				// or check if the commenter is whitelisted!
				|| $msgs[$j]['whitelisted_commenter']
			)	{
				$tmp = $msgs[$i];
				$msgs[$i] = $msgs[$j];
				$msgs[$j] = $tmp;
			}
		}
	}

	// write the full set of ranked-by-score comments for this vibe
	file_put_contents($CACHE_DIR . "c_full_$vibe_id.json", json_encode($msgs));
	file_put_contents($CACHE_DIR . "c_full_$vibe_id.jsonp", 'jsonp_parse_comment_list(' .json_encode($msgs) . ');');

	// add all these comments to our full megalist tracker; skip our special vibes
	if(!strstr($vibe_id, '@')) {
		$every_single_comment = array_merge($every_single_comment, $msgs);
	}

	// rank the messages by a weighted score
	for($i = 0; $i < count($msgs); $i++)	{		
		for($j = $i + 1; $j < count($msgs); $j++)  {
			// don't demote whitelisted dudes
			if($msgs[$i]['whitelisted_commenter']) continue;

			// weight it by comment age?
			// $weighted_timestamp_i = time_weighted_power($msgs[$i]['comment_relative_time']) + $msgs[$i]['score'];
			// $weighted_timestamp_j = time_weighted_power($msgs[$j]['comment_relative_time']) + $msgs[$j]['score'];

			// weight it by content age?
			$weighted_timestamp_i = time_weighted_power($msgs[$i]['context_meta']['content_relative_time']) + $msgs[$i]['score'];
			$weighted_timestamp_j = time_weighted_power($msgs[$j]['context_meta']['content_relative_time']) + $msgs[$j]['score'];
			
			if(
				$weighted_timestamp_i < $weighted_timestamp_j
				// or check if the latter is one of our blessed commenters! if so move them forward
				|| $msgs[$j]['whitelisted_commenter']
			)	{
				$tmp = $msgs[$i];
				$msgs[$i] = $msgs[$j];
				$msgs[$j] = $tmp;
			}
		}
	}

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
	
	// resort this thing now on weighted timestamp
	for($i = 0; $i < count($deduped_msgs); $i++)	{
		for($j = $i + 1; $j < count($deduped_msgs); $j++)  {
			// don't demote whitelisted dudes
			if($deduped_msgs[$i]['whitelisted_commenter']) continue;

			// weight it by comment age and comment score?
			// $weighted_timestamp_i = time_weighted_power($deduped_msgs[$i]['comment_relative_time']) + $msgs[$i]['score'];
			// $weighted_timestamp_j = time_weighted_power($deduped_msgs[$j]['comment_relative_time']) + $msgs[$j]['score'];

			// weight it by content age and comment score?
			// $weighted_timestamp_i = time_weighted_power($deduped_msgs[$i]['context_meta']['content_relative_time']) + $deduped_msgs[$i]['score'];
			// $weighted_timestamp_j = time_weighted_power($deduped_msgs[$j]['context_meta']['content_relative_time']) + $deduped_msgs[$j]['score'];			

			// weight it by content age only?
			$weighted_timestamp_i = time_weighted_power($deduped_msgs[$i]['context_meta']['content_relative_time']);
			$weighted_timestamp_j = time_weighted_power($deduped_msgs[$j]['context_meta']['content_relative_time']);			

			if(
				$weighted_timestamp_i < $weighted_timestamp_j
				// or whitelisted commenter?
				|| $deduped_msgs[$j]['whitelisted_commenter']
			)	{
				$tmp = $deduped_msgs[$i];
				$deduped_msgs[$i] = $deduped_msgs[$j];
				$deduped_msgs[$j] = $tmp;
			}
		}
	}

	// write a file that represents the pseudo vibe stream, ranked and with a single comment
	file_put_contents($CACHE_DIR . "c_$vibe_id.json", json_encode($deduped_msgs));
	file_put_contents($CACHE_DIR . "c_$vibe_id.jsonp", 'jsonp_parse_posts(' . json_encode($deduped_msgs) . ');');
}

fwrite($logp, '======= starting to re-sort all comments' . "\n");
// resort all comments by score
for($i = 0; $i < count($every_single_comment); $i++)	{
	for($j = $i + 1; $j < count($every_single_comment); $j++)	{
		// don't demote whitelisted dudes
		if($every_single_comment[$i]['whitelisted_commenter']) continue;

		// time weighted score
		$weighted_timestamp_i = time_weighted_power($every_single_comment[$i]['comment_relative_time']) + $every_single_comment[$i]['score'];
		$weighted_timestamp_j = time_weighted_power($every_single_comment[$j]['comment_relative_time']) + $every_single_comment[$j]['score'];

		// non-weighted score
		$weighted_timestamp_i = $every_single_comment[$i]['score'];
		$weighted_timestamp_j = $every_single_comment[$j]['score'];
		
		if(
			$weighted_timestamp_i < $weighted_timestamp_j
			|| $every_single_comment[$j]['whitelisted_commenter']
		)	{
			$tmp = $every_single_comment[$i];
			$every_single_comment[$i] = $every_single_comment[$j];
			$every_single_comment[$j] = $tmp;
		}
	}	
}
fwrite($logp, '======= done re-sorting all comments' . "\n");

// write out the full list of comments
file_put_contents($CACHE_DIR . "c_allvibes.jsonp", 'jsonp_parse_all_comments(' . json_encode($every_single_comment) . ')'); 
file_put_contents($CACHE_DIR . "c_allvibes.json", json_encode($every_single_comment)); 

// deeper analysis
// fwrite($logp, '======= deeper analysis: multiposters' . "\n");
// $multi_posters = (derive_multi_posters($every_single_comment));
// write this set of users to file
// file_put_contents($CACHE_DIR . "multi_posters.json", json_encode($multi_posters));
// file_put_contents($CACHE_DIR . "multi_posters.jsonp", 'jsonp_parse_multi_posters(' .json_encode($multi_posters) . ');');
// write each user's history
// for($i = 0; $i < count($multi_posters); $i++)	{
	// $guid = $multi_posters[$i];
	// $msgs = get_user_message_history($guid);

	// write this individual user history to file
	// file_put_contents($CACHE_DIR . "user_history_$guid.json", json_encode($msgs));
	// write this individual history to a callable file
	// file_put_contents($CACHE_DIR . "user_history_$guid.jsonp", 'jsonp_parse_user_history(' .json_encode($msgs) . ');');
// }
// fwrite($logp, '======= deeper analysis: uuid_to_vibes' . "\n");
// $uuid_to_vibes = (get_uuid_to_vibes($every_single_comment));
// fwrite($logp, '======= done with deeper analysis' . "\n");


// write out a superset of all vibes for a full home stream
fwrite($logp, '======= starting amalgamation' . "\n");
$amalgam = array();
$whitelisted_messages = get_whitelisted_comments();
for($ind = 0; $ind < count($ALL_VIBES); $ind++)	{
	// shortcuts
	$vibe_id = $ALL_VIBES[$ind]['id'];
	$vibe_name = $ALL_VIBES[$ind]['name'];
	$vibe_meta = $ALL_VIBES[$ind]['meta'];
	fwrite($logp, 'amalgamation for ' . $vibe_name . "\n");

	$vibe_posts = json_decode(file_get_contents($CACHE_DIR . "$vibe_id.json"), true);
	$vibe_comments = json_decode(file_get_contents($CACHE_DIR . "c_full_$vibe_id.json"), true);
	$vibe_postscomments = json_decode(file_get_contents($CACHE_DIR . "c_$vibe_id.json"), true);

	// filter postscomments for this vibe for rules, e.g. whitelists
	for($i = 0; $i < count($vibe_postscomments); $i++) {
		// this first if statement checks if any of our whitelisted commenters have posted on this story
		if(isset($whitelisted_messages[$vibe_postscomments[$i]['context_id']])) {
			// replace this in the current list with the whitelisted one
			$keys_to_copy = array(
				'author_guid',
				'author_img',
				'author_name',
				'comment_relative_time',
				'comment_time',
				'created_at',
				'downvotes',
				'replies',
				'reports',
				'text',
				'upvotes'
			);

			// copy the keys over - we're doing this because we don't have the post metadata i guess?
			for($j = 0; $j < count($keys_to_copy); $j++)	{
				$vibe_postscomments[$i][$keys_to_copy[$j]] = $whitelisted_messages[$vibe_postscomments[$i]['context_id']][$keys_to_copy[$j]];
			}

			// hack to introduce diversity in our whitelisted commenters, even though they're all asad ;)
			$random_commenter_names = array(
				'aescalus',
				'brienne',
				'Snoop247',
				'OmarFromTheWire',
				'gettyImagines',
				'Thumbelina.3',
				'carbonarofx',
				'dope-o-mine'
			);
			// check if it's asad first
			if($vibe_postscomments[$i]['author_guid'] == 'FI3SFWX5YUMNC57AOOIW2UTAC4') {
				// it is! hack a random name together
				$vibe_postscomments[$i]['author_name'] = $random_commenter_names[array_rand($random_commenter_names)];
			}
			
			// housekeeping to ensure that our proprietary fields work
			$vibe_postscomments[$i]['bot'] = false;
			$vibe_postscomments[$i]['score'] = 1;
			$vibe_postscomments[$i]['whitelisted_commenter'] = true;
		}

		// if the organically ranked comment happened to come from a whitelisted commenter... (super unlikely, right?)
		if(
			$vibe_postscomments[$i]['whitelisted_commenter']
		)	{
			// good! feature this comment on the main stream
		}
		else {
			// no! bad commenter! pretend this is a bot post
			$vibe_postscomments[$i]['bot'] = true;
		}
	}

	// add this vibe to the full array
	array_push($amalgam, array(
		'id' => $vibe_id,
		'name' => $vibe_name,
		'meta' => $vibe_meta,
		'posts' => $vibe_posts,
		'comments' => $vibe_comments,
		'postscomments' => $vibe_postscomments
	));
}

// re-sort the vibe cards by most recently updated
for($i = 0; $i < count($amalgam); $i++)	{
	for($j = $i + 1; $j < count($amalgam); $j++)	{
		// shortcuts
		$i_vibe_id = $amalgam[$i]['id'];
		$i_vibe_name = $amalgam[$i]['name'];
		$i_vibe_posts = $amalgam[$i]['posts'];

		if(strstr($i_vibe_id, '@')) {
			$amalgam[$i]['type'] = $i_vibe_id;
		}
		
		// reorder everything except the ntk video and breaking news
		if(
			$i_vibe_id != '@NTKVIDEO'
			&& $i_vibe_id != '@BREAKING'
		) {
			$amalgam[$i]['type'] = 'VIBE';
			$j_vibe_posts = $amalgam[$j]['posts'];

			$max_i_pub_at = 0;
			for($k = 0; $k < count($i_vibe_posts); $k++)	{
				$max_i_pub_at = max($max_i_pub_at, $i_vibe_posts[$k]['content_published_at']);
			}

			$max_j_pub_at = 0;
			for($k = 0; $k < count($j_vibe_posts); $k++)	{
				$max_j_pub_at = max($max_j_pub_at, $j_vibe_posts[$k]['content_published_at']);
			}

			// echo $max_i_pub_at . ':' . $max_j_pub_at . "\n";

			if($max_i_pub_at < $max_j_pub_at)	{
				$tmp = $amalgam[$i];
				$amalgam[$i] = $amalgam[$j];
				$amalgam[$j] = $tmp;
			}
		}
	}	
}

// add breaking news;
// TODO make this conditional
array_unshift($amalgam, array(
	'type' => 'BREAKING',
	'id' => '@BREAKING',
	'heading' => 'Breaking News',
	'msg' => 'Guys, the sky is literally on fire right now!'
));
// add suggested vibes
// $sugg_vibes = get_suggested_vibes();
// write it out

// write the amalgamation to disk
file_put_contents($CACHE_DIR . "amalgam.json", json_encode($amalgam));
file_put_contents($CACHE_DIR . "amalgam.jsonp", 'jsonp_parse_amalgam(' . json_encode($amalgam) . ');');

fwrite($logp, '======= done amalgamation' . "\n");

fwrite($logp, '=================== END' . "\n");
fclose($logp);
?>
