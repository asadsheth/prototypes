<?php
date_default_timezone_set('America/Los_Angeles');

$QUOTE_EXTRACTOR_SERVICE = 'http://research-hm11.corp.gq1.yahoo.com:4080/slicksumm/captions?url=';
// $TRENDING_POSTS_SERVICE = 'http://vibe-social-notification-yql.v1.production.omega.gq1.yahoo.com/vibe/trending/posts/stream?count=15';
// $TRENDING_POSTS_SERVICE = 'http://vibe-social-notification-yql.media.yahoo.com/vibe/trending/posts?topicId=dbb2094c-7d9a-37c0-96b9-7f844af62e78&topicId=338950e1-cae3-359e-bfa3-af403b69d694&topicId=69f70237-124f-3ea9-acd0-fc922af945e2&topicId=b7ddaf4b-9395-34b6-9ddc-e32547089110&topicId=5c839b50-00d3-37e8-a68f-7c03c48d7104&topicId=c841dd90-2f8b-350f-8e65-f02648ba998b&topicId=7563eca7-14c1-3a20-ab16-782788cde33a&topicId=fc98c570-0d12-33f5-aa5a-f89224e57bdc&count=15';
$TRENDING_POSTS_SERVICE = 'http://mobile-homerun-yql.media.yahoo.com:4080/api/vibe/v1/streams/trending';

// docs for ^^ https://git.corp.yahoo.com/ymobile/docs-social-notification/tree/master/api
$NUM_TRENDING_POSTS = 15;
$SHOW_COMMENTS = false;
$NUM_COMMENTS = 100;
$RANKING = 'reddit';
if($_GET['ranking'] == 'legacy')	{
	$RANKING = 'legacy';
}
$OGIMAGES = array(
    'https://s.yimg.com/cv/ae/qotd/1.png',
    'https://s.yimg.com/cv/ae/qotd/2.png',
    'https://s.yimg.com/cv/ae/qotd/3.png',
    'https://s.yimg.com/cv/ae/qotd/4.png',
    'https://s.yimg.com/cv/ae/qotd/5.png'
);

$OG_IMAGE_BORROWED = '';

// UX configs
$IMAGE_HEIGHT = 350;
$SLIDE_DURATION = 3;
$FAST_TRANSITION_DURATION = 0.25;

// get the posts that will serve as the basis for our digest
$response = file_get_contents($TRENDING_POSTS_SERVICE);
$posts = array();
$object = json_decode($response, true);

// go through each item and parse it to see if it can make for a quote
for($i = 0; $i < count($object['items']['result']) && $i < $NUM_TRENDING_POSTS; $i++)   {
    $obj = $object['items']['result'][$i];

    $post_id = $obj['id'];
    $post_url = $obj['postUrl'];
    $author = $obj['author']['name'];
    $lead_attribution = $obj['leadAttribution'];
    $link = $obj['content']['url'];
    $img = $obj['content']['images'][0]['originalUrl'];
    // $img = $obj['content']['images'][0]['resolutions'][0]['url'];
    $title = $obj['content']['title'];
    $summary = $obj['content']['summary'];
    $published_at = $obj['publishedAt'];
    $topic = $obj['topics'][0]['name'];
    $provider = $obj['content']['provider']['name'];
    $uid = $obj['id'];
    $article_uuid = $obj['content']['uuid'];
    $post_content_url = $obj['content']['url'];
    $content_body = $obj['content']['body'];
    $comments = $obj['comments']['items'];

    // is the summary too short to show?
    if(strlen($summary) < 20) continue;

    // call the quote extractor service with this article as the input
    $quote_response = file_get_contents($QUOTE_EXTRACTOR_SERVICE . $post_content_url);
    $quote_object = json_decode($quote_response, true);

    // check to see if the quote extractor service found any utterances we like
    $found_utterance = false;
    for($j = 0; $j < count($quote_object['quotes']) && !$found_utterance; $j++)	{
    	$q_o = $quote_object['quotes'][$j];

    	// dumb heuristic filters on shitty quotes:
        // ----------------------------------
    	// 1. not too long?
    	// 2. speaker attribution not too short?
    	// 3. speaker attribution has a space in it, meaning it's likely a full name?
    	// 4. first char is uppercase, meaning it's not a fragment?
    	// 5. attribution doesn't have a quotation mark in it (this is a surprisingly common error)
    	// 6. attribution doesn't have a ” in it (this is a surprisingly common error)
    	if(
    		true
    		&& strlen($q_o['sentences'][0]) < 150
    		&& strlen($q_o['speaker']) > 3
    		&& strstr($q_o['speaker'], ' ')
    		&& strtoupper($q_o['sentences'][0][0]) == $q_o['sentences'][0][0]
    		&& !strstr($q_o['speaker'], '"')
    		&& !strstr($q_o['speaker'], '”')
    	)	{
		    $utterance = $quote_object['quotes'][$j]['sentences'][0];
		    $utterer = $quote_object['quotes'][$j]['speaker'];

		    // if it ends with a comma, make it an ellipsis
		    if(substr($utterance, -1) == ',') $utterance = substr($utterance, 0, -1) . '…';

		    // we found one! stop looking
		    $found_utterance = true;
    	}
    }

    if(
    	$found_utterance
    )   {
        $msg = array();
        // $msg['uid'] = $uid;
        // $msg['updateDate'] = date('c', $published_at);
        // $msg['titleText'] = $title;
        // $msg['mainText'] = $summary;
        // $msg['redirectionUrl'] = $post_url;
        // $msg['body'] = $content_body;
        // $msg['postcomments'] = $comments;
        
        $msg['unixtimestamp'] = $published_at;
        $msg['title'] = $title;
        $msg['img'] = $img;
        $msg['topic'] = $topic;
        $msg['provider'] = $provider;
        $msg['summary'] = $summary;
        $msg['contentUrl'] = $post_content_url;
        $msg['contentUuid'] = $article_uuid;

        $msg['utterance'] = $utterance;
        $msg['utterer'] = $utterer;

        // set the digest image from the first one we got
        if(
            $OG_IMAGE_BORROWED == ''
            && strlen($img) > 20
        )   {
            $OG_IMAGE_BORROWED = $img;
        }


        if($SHOW_COMMENTS)	{
	        // since we found an utterance that fits our criteria, let's go look at comments harder:
		    // comments batch api
		    // http://canvass-yql.media.yahoo.com:4080/api/canvass/debug/v1/ns/yahoo_content/contexts/batch?region=US&lang=en-US&contextIds=bec01585-577c-348e-9bab-e746d75d42e1
		    // comments single uuid api
		    // http://canvass-yql.media.yahoo.com:4080/api/canvass/debug/v1/ns/yahoo_content/contexts/bec01585-577c-348e-9bab-e746d75d42e1/messages?count=10&sortBy=popular&region=US&lang=en-US&rankingProfile=canvassHalfLifeDecayProfile&userActivity=true
		    $comment_response = file_get_contents('http://canvass-yql.media.yahoo.com:4080/api/canvass/debug/v1/ns/yahoo_content/contexts/' . $article_uuid .'/messages?count=100&sortBy=mostdiscussed&region=US&lang=en-US&userActivity=false');
		    $comment_object = json_decode($comment_response, true);
		    $comment_list = $comment_object['canvassMessages'];

		    // start a new list of all the comments we want
		    $parsed_comment_list = array();
		    // go through the list and score each comment with the reddit "best" algo
		    for($j = 0; $j < count($comment_list); $j++)	{
		    	$r = $comment_list[$j]['reactionStats']['abuseVoteCount'];
		    	$uv = $comment_list[$j]['reactionStats']['upVoteCount'];
		    	// cheating downvotes by adding 10x the report count to penalize reported comments
		    	$dv = $comment_list[$j]['reactionStats']['downVoteCount'] + 10 * $r;
		    	$n = $uv + $dv;

		    	if($n == 0)	{
		    		$bestscore = 0;
		    	}
		    	else {
		    		$z = 1.281551565545;
		    		$p = $uv / $n;
					$left = $p + 1/(2*$n)*$z*$z;
					$right = $z*sqrt($p*(1-$p)/$n + $z*$z/(4*$n*$n));
					$under = 1+1/$n*$z*$z;

					$bestscore = ($left + $right) / $under;
		    	}

		    	// save the whole comment object
		    	$comment_object = array(
		    		'upvotes' => $comment_list[$j]['reactionStats']['upVoteCount'],
		    		'downvotes' => $comment_list[$j]['reactionStats']['downVoteCount'],
			    	'reports' => $comment_list[$j]['reactionStats']['abuseVoteCount'],
			    	'replies' => $comment_list[$j]['reactionStats']['replyCount'],
			    	'text' => $comment_list[$j]['details']['userText'],
			    	'author' => $comment_list[$j]['meta']['author']['nickname'],
			    	'avatar' => $comment_list[$j]['meta']['author']['image']['url'],
			    	'bestscore' => $bestscore
		    	);

		    	// print_r($comment_object); 
		    	array_unshift($parsed_comment_list, $comment_object);
		    }
		    
		    // if we're using the reddit "best" ranking then sort the list of comments based on that score
		    if($RANKING == 'reddit')	{
			    for($j = 0; $j < count($parsed_comment_list); $j++)	{
				    for($k = $j + 1; $k < count($parsed_comment_list); $k++)  {
				        if($parsed_comment_list[$j]['bestscore'] < $parsed_comment_list[$k]['bestscore'])   {
				            $tmp = json_encode($parsed_comment_list[$j]);
				            $parsed_comment_list[$j] = json_decode(json_encode($parsed_comment_list[$k]), true);
				            $parsed_comment_list[$k] = json_decode($tmp, true);
				        }
				    }		    	
			    }
			}

			// strip to 3 comments, don't need the full list bruh
			$parsed_comment_list = array_slice($parsed_comment_list, 0, $NUM_COMMENTS);
		    $msg['commentlist'] = $parsed_comment_list;
		}

        // add this quote/article pair to the front of the list!
        // array_unshift($posts, $msg);
        // add this quote/article pair to the BACK of the list!
        array_push($posts, $msg);
    }
}

// reverse chron sort. bad idea? maybe.
// it's disabled for now
for($i = 0; false && $i < count($posts); $i++)   {
    for($j = $i + 1; $j < count($posts); $j++)  {
        if($posts[$i]['unixtimestamp'] > $posts[$j]['unixtimestamp'])   {
            $tmp = json_encode($posts[$i]);
            $posts[$i] = json_decode(json_encode($posts[$j]), true);
            $posts[$j] = json_decode($tmp, true);
        }
    }
}

// get the unique people talking
$speakers = array();
$speaker_string = '';
for($i = 0; $i < count($posts); $i++)   {
    if($speakers[$posts[$i]['utterer']])   {
        continue;
    }
    else {
        $speakers[$posts[$i]['utterer']] = true;
        $speaker_string .= $posts[$i]['utterer'] . ', ';
    }
}
$speaker_string = substr($speaker_string, 0, strlen($speaker_string) - 2);

?>
<html lang="en" prefix="og: http://ogp.me/ns#">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta property="og:title" content="Quotes of the Day - <?php echo date('l\, F jS'); ?>" />
    <meta property="og:type" content="article" />
    <meta property="og:image" content="<?php echo $OG_IMAGE_BORROWED; ?>" />
    <meta property="og:type" content="website" />
    <meta property="og:description" content="A quick recap of today's news through the lens of what newsmakers are saying. In this edition: <?php echo $speaker_string; ?>" />
    <meta charset="utf-8">
    <title><?php echo date('l\, F jS'); ?></title>
    <style type="text/css">
/*https://s.yimg.com/cv/ae/default/171027/Yahoo_Sans_Web_Fonts_170914/Yahoo_Sans-Regular.eot
https://s.yimg.com/cv/ae/default/171027/Yahoo_Sans_Web_Fonts_170914/Yahoo_Sans-ExtraLight.woff2
https://s.yimg.com/cv/ae/default/171027/Yahoo_Sans_Web_Fonts_170914/All_Fonts.html
https://s.yimg.com/cv/ae/default/171027/Yahoo_Sans_Web_Fonts_170914/Yahoo_Sans-Semibold.woff
https://s.yimg.com/cv/ae/default/171027/Yahoo_Sans_Web_Fonts_170914/Yahoo_Sans-Black.woff2
https://s.yimg.com/cv/ae/default/171027/Yahoo_Sans_Web_Fonts_170914/Yahoo_Sans-Semibold.eot
https://s.yimg.com/cv/ae/default/171027/Yahoo_Sans_Web_Fonts_170914/Yahoo_Sans-ExtraBold.eot
https://s.yimg.com/cv/ae/default/171027/Yahoo_Sans_Web_Fonts_170914/Yahoo_Sans-Italic.eot
https://s.yimg.com/cv/ae/default/171027/Yahoo_Sans_Web_Fonts_170914/fonts.css
https://s.yimg.com/cv/ae/default/171027/Yahoo_Sans_Web_Fonts_170914/Yahoo_Sans-Semibold.woff2
https://s.yimg.com/cv/ae/default/171027/Yahoo_Sans_Web_Fonts_170914/Yahoo_Sans-Black.eot
https://s.yimg.com/cv/ae/default/171027/Yahoo_Sans_Web_Fonts_170914/Yahoo_Sans-ExtraLight.eot
https://s.yimg.com/cv/ae/default/171027/Yahoo_Sans_Web_Fonts_170914/Yahoo_Sans-Black.woff
https://s.yimg.com/cv/ae/default/171027/Yahoo_Sans_Web_Fonts_170914/Yahoo_Sans-Bold.woff
https://s.yimg.com/cv/ae/default/171027/Yahoo_Sans_Web_Fonts_170914/Yahoo_Sans-ExtraLight.woff
https://s.yimg.com/cv/ae/default/171027/Yahoo_Sans_Web_Fonts_170914/Yahoo_Sans-ExtraBold.woff2
https://s.yimg.com/cv/ae/default/171027/Yahoo_Sans_Web_Fonts_170914/_How_To_Install_Web_Fonts.html
https://s.yimg.com/cv/ae/default/171027/Yahoo_Sans_Web_Fonts_170914/Yahoo_Sans-Bold.eot
https://s.yimg.com/cv/ae/default/171027/Yahoo_Sans_Web_Fonts_170914/Yahoo_Sans-ExtraBold.woff
https://s.yimg.com/cv/ae/default/171027/Yahoo_Sans_Web_Fonts_170914/Yahoo_Sans-Light.eot
https://s.yimg.com/cv/ae/default/171027/Yahoo_Sans_Web_Fonts_170914/Yahoo_Sans-Light.woff
https://s.yimg.com/cv/ae/default/171027/Yahoo_Sans_Web_Fonts_170914/Yahoo_Sans-Regular.woff
https://s.yimg.com/cv/ae/default/171027/Yahoo_Sans_Web_Fonts_170914/Yahoo_Sans-Medium.woff
https://s.yimg.com/cv/ae/default/171027/Yahoo_Sans_Web_Fonts_170914/Yahoo_Sans-Italic.woff
https://s.yimg.com/cv/ae/default/171027/Yahoo_Sans_Web_Fonts_170914/Yahoo_Sans-Italic.woff2*/
        @font-face{
            font-family: "Yahoo-Sans";
            src: url('https://s.yimg.com/cv/ae/default/171027/Yahoo_Sans_Web_Fonts_170914/Yahoo_Sans-Regular.woff2');
            font-weight: normal;
            font-style: normal;
        }
        @font-face{
            font-family: "Yahoo-Sans";
            src: url('https://s.yimg.com/cv/ae/default/171027/Yahoo_Sans_Web_Fonts_170914/Yahoo_Sans-Light.woff2');
            font-weight: lighter;
            font-style: normal;
        }
        @font-face{
            font-family: "Yahoo-Sans";
            src: url('https://s.yimg.com/cv/ae/default/171027/Yahoo_Sans_Web_Fonts_170914/Yahoo_Sans-Medium.woff2');
            font-weight: medium;
            font-style: normal;
        }
        @font-face{
            font-family: "Yahoo-Sans";
            src: url('https://s.yimg.com/cv/ae/default/171027/Yahoo_Sans_Web_Fonts_170914/Yahoo_Sans-Bold.woff2');
            font-weight: bold;
            font-style: normal;
        }

        img { width: 100%; }
        body {
            margin: 0; padding: 0;
            overflow-x: hidden;
            background: #ddd;
            font-family: "Yahoo-Sans";
        }
        #stage {
            margin: 0; padding: 0;
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            /*transform: translate(-2000%);*/
            transition-duration: 0.25s;
        }
        .i_thinger  {
            width: 100%;
            box-sizing: border-box;
            transition-duration: 0.25s;
            position: relative;
            overflow: hidden;
            perspective: 100px;
            margin: 0px 0px 20px 0px;
            box-shadow: 0 0 10px 0 rgba(0,0,0,0.75);
        }
        .i_photograph {
            position: relative;
            transition-duration: 0.5s;
            transition-timing-function: linear;
            width: 130%;
            margin-left: -15%;
            top: 0px;
            left: 0px;
            background-size: cover;
            background-position: center;
            /*height: 130%;*/
            /*margin-top: -5%;*/
        }
        .i_shade {
            width: 100%;
            height: 350px;
            background: linear-gradient(rgba(0,0,0,0.25), rgba(0,0,0,0), rgba(0,0,0,1));
            position: absolute;
            top: 0;
            left: 0;
            perspective: 800px;
        }

        .i_quoter {
            position: absolute;
            top: -40px;
            left: -30px;
            width: 172px;
            opacity: 0.5;
        }

        .i_message  {
            position: absolute;
            top: 4%;
            left: 5%;
            color: white;
            width: 90%;
        }
        .i_message .quote   {
            display: block;
            text-align: left;
            font-size: 18pt;
            font-weight: normal;
        }

        .i_message .source  {
            display: block;
            text-align: right;
            font-size: 12pt;
            padding: 6pt;
            color: #ccc;
        }

        .i_contentbody  {
            position: relative;
            padding: 15px 20px 15px 20px;
            background: white;
            color: black;
        }
        .i_contentbody h1, h2 {
            margin: 0;
        }
        h1 a {
            text-decoration: none;
            color: inherit;
        }
        .i_contentbody .provider_attribution    {
            font-size: 12px;
            opacity: 0.5;
            padding-top: 5px;
        }
        .i_contentbody .separator   {
            width: 20%;
            font-size: 5px;
            margin-bottom: 20px;
            margin-top: 20px;
        }
        .i_contentbody .summary {
            margin-bottom: 10px;
        }
        .i_contentbody a.more {
            font-weight: normal;
            text-decoration: none;
            padding: 10px;
            margin: 0px 0px 10px 0;
            color: black;
            display: inline-block;
            display: none;
            color: white;
        }

        .i_contentbody .tapper {
            height: 50px;
            width: 50px;
            border-radius: 25px;
            line-height: 50px;
            text-align: right;
            float: left;
            position: relative;
        }
        .i_contentbody .prev {
            background: green;
        }
        .i_contentbody .next {
            background: yellow;
        }

        .comment_block {
            padding: 20px;
            margin-bottom: 10px;
            box-shadow: 0 0 5px 0 rgba(0,0,0,0.5);
            border-radius: 3px;
        }
        .comment_block .comment_text {
            font-size: 20px;
        }
        .comment_block .author {
            font-size: 16px;
            text-align: right;
            clear: both;
            opacity: 0.5;
            padding-top: 10px;
        }

        .comment_block .avatar  {
            width: 80px;
            height: 80px;
            float: left;
            margin-right: 10px;
            border-radius: 40px;
        }
    </style>
</head>
<body>
    <div id="stage"></div>
</body>

<script type="text/javascript">

var current_index = 0;
var stories = <?php echo json_encode($posts); ?>;
var dom_content_loaded = false;

// modify the following line to exlude the stories at index 0 to N from this doc. this is for the manual review breakpoint
var excluded_indexes = [];
var filtered_stories = [];
for(var i = 0; i < stories.length; i++)	{
	is_excluded = false;
	for(var j = 0; j < excluded_indexes.length; j++)	{
		if(i == excluded_indexes[j])	{
			is_excluded = true;
		}
	}

	if(!is_excluded)	{
		filtered_stories.push(stories[i]);
	}
}
stories = filtered_stories;

var colors = [
    'darkred',
    'darkgreen',
    'darkcyan',
    'darkblue',
    'darkgoldenrod',
    'darkslateblue',
    'darkviolet',
    'indigo',
    'darkmagenta',
    'firebrick',
    'green',
    'maroon',
    'midnightblue',
    'rebeccapurple'
];

document.addEventListener("DOMContentLoaded", function(event) {
    // paint each story
    var theme_seed = Math.floor(Math.random() * colors.length);
    for(var i = 0; i < stories.length; i++)  {
        var theme_color = colors[(i + theme_seed) % colors.length];

        var i_thinger = document.createElement('div');
        i_thinger.className = 'i_thinger parallax__group';
        // i_thinger.style.transform = 'translate3d(' + (100 * i) + '%, 0, 0)';

        var i_photograph = document.createElement('div');
        i_photograph.className = 'i_photograph parallax__layer parallax__layer--base';
        i_photograph.style.backgroundImage = 'url("' + stories[i].img + '")';
        i_photograph.innerHTML = '<img src="' + stories[i].img + '" />'
        i_thinger.appendChild(i_photograph);

        var i_shade = document.createElement('div');
        i_shade.className = 'i_shade';
        i_shade.style.background = 'linear-gradient(' + theme_color + ' 10%, rgba(0,0,0,0) 80%, rgba(0,0,0,0))';
        i_thinger.appendChild(i_shade);
        var i_quoter = document.createElement('img');
        i_quoter.src = 'https://d30y9cdsu7xlg0.cloudfront.net/png/19279-200.png';
        i_quoter.className = 'i_quoter';
        i_thinger.appendChild(i_quoter);
        var i_message = document.createElement('div');
        i_message.className = 'i_message';
        i_message.innerHTML = '<span class="quote">' + stories[i].utterance + '</span>' + '<span class="source">' + stories[i].utterer + '</span>';
        i_thinger.appendChild(i_message);

        var i_contentbody = document.createElement('div');
        i_contentbody.className = 'i_contentbody parallax__layer parallax__layer--back';
        i_contentbody_string = '';
        // title
        i_contentbody_string += '<h1>' + '<a href="' + stories[i].contentUrl + '">' + stories[i].title + '</a>' + '</h1>';
        // provider
        i_contentbody_string += '<div class="provider_attribution">' + stories[i].provider + '</div>';
        // separator
        i_contentbody_string += '<div class="separator" style="background: ' + theme_color + '; color: ' + theme_color +'">--</div>';
        // summary
        i_contentbody_string += '<div class="summary">' + stories[i].summary +'</div>';
        // wrap it up b
        i_contentbody.innerHTML = i_contentbody_string;
        // what do you do after this
        var i_upnext = document.createElement('div');
        i_upnext.className = 'up_next';
        i_upnext.innerHTML = '<a class="more" style="background: ' + theme_color + '" href="' + stories[i].contentUrl + '">read more &raquo;</a>';
        i_upnext.firstChild.onclick = function() {
            this.innerHTML = 'loading&hellip;'
        }
        // add the what you do after this
        i_contentbody.appendChild(i_upnext);

        i_thinger.appendChild(i_contentbody)

        document.getElementById('stage').appendChild(i_thinger);
    }

    dom_content_loaded = true;
});

PREV_X_SCALER = 0; PREV_Y_SCALER = 0; SCALER_IMAGES = null;
function handleOrientation(event) {
    if(SCALER_IMAGES)   {
        // ?
    }
    else {
        SCALER_IMAGES = document.getElementsByClassName('i_photograph');
    }
    var x_scaler = -1 * Math.min(Math.abs(event.accelerationIncludingGravity.x) / 10, 0.2) * Math.abs(event.accelerationIncludingGravity.x) / event.accelerationIncludingGravity.x;
    var y_scaler = -1 * Math.min(Math.abs(event.accelerationIncludingGravity.y) / 10, 0.2) * Math.abs(event.accelerationIncludingGravity.y) / event.accelerationIncludingGravity.y;
    
    if(
        Math.abs(PREV_X_SCALER - x_scaler) < 0.01
        && Math.abs(PREV_Y_SCALER - y_scaler) < 0.01
    ) {
        return;
    }
    else {
        PREV_X_SCALER = x_scaler;
        PREV_Y_SCALER = y_scaler;        
    }

    for(var i = 0; i < SCALER_IMAGES.length; i++)    {
        SCALER_IMAGES[i].style.transitionDuration = '0.5s';        
        SCALER_IMAGES[i].style.transitionTimingFunction = 'linear'; 
        SCALER_IMAGES[i].style.transform = 'translateX(' + (x_scaler * 100) + 'px) translateY(' + (y_scaler * 100) + 'px)';
    }

}
// window.addEventListener("devicemotion", handleOrientation, true);


</script>
</html>