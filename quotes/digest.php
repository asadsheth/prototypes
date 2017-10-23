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
    // $img = $obj['content']['images'][0]['originalUrl'];
    $img = $obj['content']['images'][0]['resolutions'][0]['url'];
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
for($i = 0; false && $i < count($posts); $i++)   {
    for($j = $i + 1; $j < count($posts); $j++)  {
        if($posts[$i]['unixtimestamp'] > $posts[$j]['unixtimestamp'])   {
            $tmp = json_encode($posts[$i]);
            $posts[$i] = json_decode(json_encode($posts[$j]), true);
            $posts[$j] = json_decode($tmp, true);
        }
    }
}

?>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta property="og:title" content="Quotes of the Day - <?php echo date('l\, F jS'); ?>" />
    <meta property="og:type" content="article" />
    <meta property="og:image" content="<?php echo $OG_IMAGE_BORROWED; ?>" />
    <title>Quotes of the Day - <?php echo date('l\, F jS'); ?></title>
    <style type="text/css">
        img { width: 100%; }
        body {
            margin: 0; padding: 0;
            perspective: 800px;
            overflow-x: hidden;
        }
        #stage {
            margin: 0; padding: 0;
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;    
            /*transform: translate(-2000%);*/
            transition-duration: <?php echo $FAST_TRANSITION_DURATION; ?>s;        
            perspective: 800px;
        }
        .i_thinger  {
            width: 100%;
            height: <?php echo $IMAGE_HEIGHT; ?>px;
            position: absolute;
            box-sizing: border-box;
            background-size: cover;
            background-position: center;            
            transition-duration: <?php echo $FAST_TRANSITION_DURATION; ?>s;
        }
        .i_shade {
            width: 100%;
            height: 100%;
            background: linear-gradient(rgba(0,0,0,0.25), rgba(0,0,0,0), rgba(0,0,0,1));
            position: absolute;
            top: 0;
            left: 0;
            perspective: 800px;
        }

        .i_quoter {
			position: absolute;
			top: -8%;
			left: -7%;
			width: 172px;
			opacity: 0.5;
        }

        .i_message  {
            position: absolute;
            top: 4%;
            left: 5%;
            color: white;
            width: 90%;
            font-family: sans-serif;
        }
        .i_message .quote   {
            display: block;
            text-align: left;
            font-size: 18pt;
            font-weight: bold;
        }

        .i_message .source  {
            display: block;
            text-align: right;
            font-size: 12pt;
            padding: 6pt;
            color: #ccc;
        }

        .i_contentbody  {
            position: absolute;
            padding: 15px 20px;
            top: <?php echo $IMAGE_HEIGHT; ?>px;
            background: white;
            color: black;
            font-family: sans-serif;
        }
        .i_contentbody h1, h2 {
        	margin-bottom: 0;
        }
        .i_contentbody .provider_attribution	{
        	font-size: 12px;
        	opacity: 0.5;
        	padding-top: 5px;
        }
        .i_contentbody .separator	{
        	width: 20%;
        	font-size: 5px;
        	margin-bottom: 20px;
        	margin-top: 20px;
        }
        .i_contentbody a.more {
        	font-weight: bold;
        	text-decoration: none;
        	padding: 10px;
        	margin: 10px 0;
        	color: black;
        	display: inline-block;
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

        .comment_block .avatar	{
        	width: 80px;
        	height: 80px;
        	float: left;
        	margin-right: 10px;
        	border-radius: 40px;
        }
    </style>
</head>
<body>
	<div id="ftux" style="
	    position: absolute;
	    top: 0;
	    right: 0;
	    z-index: 100;
	    height:  <?php echo $IMAGE_HEIGHT; ?>px;
	    background: black;
	    line-height: <?php echo $IMAGE_HEIGHT; ?>px;
	    color: white;
	    font-weight: bold;
	    padding: 0;
	    width: 25%;
	    text-align: center;
	    opacity: 0;
	    transition-duration: 0.5s;
	    background: -webkit-linear-gradient(left, rgba(0,0,0,0), black);
	">&nbsp; &rarr;</div>
    <div id="prog" 
        style="
            position: absolute;
            top: 0px;
            left: 0px;
            z-index: 100;
            height: 5px;
            line-height: 5px;
            color: transparent;
            font-weight: bold;
            padding: 0px;
            width: 0%;
            text-align: center;
            opacity: 1;
            transition-duration: <?php echo $SLIDE_DURATION; ?>s;
            transition-timing-function: linear;
            background: rgba(255,255,255,0.5);
        ">&nbsp; →</div>    
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

// timer for autocarousel
var is_unmolested = true; // indicates that the user hasn't touched anything yet
var auto_carousel = function() {
    // boundaries
    // console.log('end: ' + (current_index == (-1 * stories.length + 1)));
    // console.log('start: ' + (current_index == 0));

    var at_end = (current_index == (-1 * stories.length + 1));

    // TODO add check for whether we're on a boundary slide here
    if(
        !at_end && is_unmolested && dom_content_loaded
    ) {
        paginate(true);
    }

    // run it again
    window.setTimeout(auto_carousel, <?php echo $SLIDE_DURATION; ?>000);
}
auto_carousel();

var paginate = function(move_forward)    {
    if(move_forward)    {
        current_index--;
    }
    else {
        current_index++;
    }

    current_index = Math.max(current_index, -1 * stories.length + 1);
    current_index = Math.min(current_index, 0);

    // if we want to transform individual items
    var thingers = document.getElementById('stage').childNodes;
    for(var i = 0; i < thingers.length && false; i++)    {
        var corrective = ((-1 * i - current_index));
        // thingers[i].style.transform = 'translate3d(' + (i * 100) + '%, 0px, ' + (corrective * -50) + 'px) rotateY(' + (corrective * -10) + 'deg)';;
        // thingers[i].style.opacity = (current_index == (-1 * i)) ? 1 : 0;
    }

    document.getElementById('stage').style.transform = 'translate(' + ((current_index ) * 100) + '%)';
}

document.addEventListener("DOMContentLoaded", function(event) {
    // paint each story
    for(var i = 0; i < stories.length; i++)  {
        var theme_color = colors[Math.floor(Math.random() * colors.length)];

        var i_thinger = document.createElement('div');
        i_thinger.className = 'i_thinger';
        i_thinger.style.backgroundImage = 'url("' + stories[i].img + '")';;
        i_thinger.style.transform = 'translate3d(' + (100 * i) + '%, 0, 0)';

        var i_shade = document.createElement('div');
        i_shade.className = 'i_shade';
        i_shade.style.background = 'linear-gradient(' + theme_color + ', rgba(0,0,0,0) 80%, rgba(0,0,0,0))';
        i_thinger.appendChild(i_shade);

        var i_quoter = document.createElement('img');
        i_quoter.src = 'https://d30y9cdsu7xlg0.cloudfront.net/png/19279-200.png';
        i_quoter.className = 'i_quoter';
        i_thinger.appendChild(i_quoter);

        var i_down = document.createElement('img');
        i_down.src = 'https://cdn3.iconfinder.com/data/icons/google-material-design-icons/48/ic_keyboard_arrow_down_48px-128.png';
        i_down.style.width = '10%';
        i_down.style.position = 'absolute';
        i_down.style.top = '<?php echo $IMAGE_HEIGHT; ?>px';
        i_down.style.left = '45%';
        i_down.style.zIndex = '10';
        i_thinger.appendChild(i_down);

        // diagonal
        var i_diag = document.createElement('div');
        i_diag.innerHTML = '<div style="z-index: 10; top: 275px; position: absolute; border-top: transparent; border-right: transparent; width: 0; height: 0; border-left: 0px solid transparent; border-right: 1000px solid transparent; border-bottom: 25px solid white; color: transparent;">&nbsp;</div>';
        // i_thinger.appendChild(i_diag);

        var i_message = document.createElement('div');
        i_message.className = 'i_message';
        i_message.innerHTML = '<span class="quote">' + stories[i].utterance + '</span>' + '<span class="source">' + stories[i].utterer + '</span>'
        i_thinger.appendChild(i_message);

        var i_contentbody = document.createElement('div');
        i_contentbody.className = 'i_contentbody';
        i_contentbody_string = '';
        // title
        i_contentbody_string += '<h1>' + stories[i].title + '</h1>';
        // provider
        i_contentbody_string += '<div class="provider_attribution">' + stories[i].provider + '</div>';
        // separator
        i_contentbody_string += '<div class="separator" style="background: ' + theme_color + '; color: ' + theme_color +'">--</div>';
        // summary
        i_contentbody_string += '<div>' + stories[i].summary +'</div>';
        // wrap it up b
        i_contentbody.innerHTML = i_contentbody_string;
        i_thinger.appendChild(i_contentbody)

        <?php if($SHOW_COMMENTS) { ?>
        // comments
        var i_wys = document.createElement('div');
        i_wys.className = 'wys';
        i_wys_string = '';
        i_wys_string += '<h2>What you said</h2>';
        // separator
        i_wys_string += '<div class="separator" style="background: ' + theme_color + '; color: ' + theme_color +'">--</div>';
        for(var j = 0; j < stories[i].commentlist.length; j++)	{
        	i_wys_string += '<div class="comment_block">';
        	i_wys_string += '<img class="avatar" src="' + stories[i].commentlist[j].avatar + '" />'
        	i_wys_string += '<div class="comment_text">' + stories[i].commentlist[j].text + '</div>';
        	i_wys_string += '<div class="author">&mdash; ' + stories[i].commentlist[j].author + '</div>';
        	i_wys_string += '<div class="data">';
        	i_wys_string += stories[i].commentlist[j].upvotes + ':' + stories[i].commentlist[j].downvotes + ':' + stories[i].commentlist[j].reports + ':' + stories[i].commentlist[j].replies + ':' + stories[i].commentlist[j].bestscore;
        	i_wys_string += '</div>';
        	i_wys_string += '</div>';
        }

        i_wys.innerHTML = i_wys_string;
        i_contentbody.appendChild(i_wys);
        <?php } ?>

        // what do you do after this
        var i_upnext = document.createElement('div');
        i_upnext.className = 'up_next';
        i_upnext.innerHTML = '<a class="more" style="background: ' + theme_color + '" href="' + stories[i].contentUrl + '">read more &raquo;</a>';

        // prev / next story
        if(false)    {
            var i_tapper = document.createElement('div');
            i_tapper.innerHTML = '<div class="prev tapper">previous</div><div class="next tapper">next</div>';
            i_upnext.appendChild(i_tapper);
            i_tapper.lastChild.addEventListener('touchstart', function() {
                paginate(true);
            });
            i_tapper.firstChild.addEventListener('touchstart', function() {
                paginate(false);
            });
        }

        // add the what you do after this
        i_contentbody.appendChild(i_upnext);
        document.getElementById('stage').appendChild(i_thinger);

        // show/hide ftux
    	// document.getElementById('stage').style.transform = 'translateX(100%)';
        setTimeout(function() {
        	document.getElementById('ftux').style.opacity = '1';
    		// document.getElementById('stage').style.transform = 'translateX(-10%)';        	
	        setTimeout(function() {
	        	document.getElementById('ftux').style.opacity = '0';
	        	// document.getElementById('stage').style.transform = 'translateX(0%)';
	        }, 500)
        }, 500);
    }

    document.body.addEventListener('touchstart', function(e) {
        // note that it's been touched
        is_unmolested = false;
        // stop the progress animation
        document.getElementById('prog').style.display = 'none';

        if(e.changedTouches[0].pageY > <?php echo $IMAGE_HEIGHT; ?>)	{
        	// this is happening below the top image
            // so don't do anything for now
        }
        else {
        	// this is happening within the top image
	        var next = (e.changedTouches[0].pageX > (document.body.offsetWidth * 3/4));
	        var prev = (e.changedTouches[0].pageX < (document.body.offsetWidth * 1/4));

	        if(next)    {
	            paginate(true);
	        }
	        else if(prev) {
	            paginate(false);
	        }

        }
    }, false);

    // handle the progress bar
    document.getElementById('prog').style.width = '0%';
    document.getElementById('prog').style.transitionDuration = (<?php echo $SLIDE_DURATION; ?> * stories.length) + 's';    
    setTimeout(function() {
        document.getElementById('prog').style.width = '100%';
    }, 1);

    setTimeout(function() {
        document.getElementById('stage').style.transform = 'translate(0%)';
    }, 1);

    dom_content_loaded = true;
});

</script>
</html>
<!-- og image stuff
<html><head></head><body>
    <div id="qotd" style="
    width: 600px;
    height: 600px;
    background: linear-gradient(45deg, aqua, orange);
    font-size: 50px;
    font-family: sans-serif;
    text-align: center;
    vertical-align: middle;
    display: block;
    line-height: 576px;
    font-weight: bold;
    color: white;
    text-shadow: 5px 5px 15px black;
">
        quotes of the day   
    </div>  


</body></html>
-->