
<html lang="en" prefix="og: http://ogp.me/ns#"><!-- warning: adding DOCTYPE breaks 'tap left to go back' on mobile -->
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta property="og:type" content="website" />
    <style type="text/css">
/*
  .parallax {
    height: 100vh;
    overflow-x: hidden;
    overflow-y: auto;
    -webkit-perspective: 300px;
    perspective: 300px;
  }

  .parallax__group {
    position: relative;
    height: 100vh;
    -webkit-transform-style: preserve-3d;
    transform-style: preserve-3d;
  }

  .parallax__layer {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
  }

  .parallax__layer--fore {
    -webkit-transform: translateZ(90px) scale(.7);
    transform: translateZ(90px) scale(.7);
    z-index: 1;
  }

  .parallax__layer--base {
    -webkit-transform: translateZ(0);
    transform: translateZ(0);
    z-index: 4;
  }

  .parallax__layer--back {
    -webkit-transform: translateZ(-300px) scale(2);
    transform: translateZ(-300px) scale(2);
    z-index: 3;
  }

  .parallax__layer--deep {
    -webkit-transform: translateZ(-600px) scale(3);
    transform: translateZ(-600px) scale(3);
    z-index: 2;
  }
*/
        img { width: 100%; }
        body {
            margin: 0; padding: 0;
            overflow-x: hidden;
            background: #ddd;
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
            width: 130%;
            margin-left: -15%;
            top: 0px;
            left: 0px;
            background-size: cover;
            background-position: center;
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
            position: relative;
            padding: 15px 20px 15px 20px;
            background: white;
            color: black;
            font-family: sans-serif;
        }
        .i_contentbody h1, h2 {
        	margin: 0;
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
    <div id="stage" class="parallax"></div>
</body>

<script type="text/javascript">

var current_index = 0;
var stories = [{"contentUrl": "https://finance.yahoo.com/news/kelly-defends-trump-apos-call-192041857.html", "img": "https://s.yimg.com/os/en-US/video/video.cnbc2.com/315809177ce16739ea3c9360112256d2", "provider": "CNBC", "summary": "Donald Trump had done the best he could in calling the widow of a slain U.S. Army sergeant killed in Niger earlier this month. quot;If you&#39;ve never been in combat, you can&#39;t even imagine how to make that call,&quot; Kelly told reporters in a surprise appearance at the daily White House press briefing.Kelly, a Gold Star father, delivered an emotional defense of the president, who was criticized on Wednesday for his comments on a call with the widow of Sgt. La David Johnson, one of four Americans killed in Niger on Oct. 4.", "title": "Kelly defends Trump's call to widow: 'If you\u2019ve never been in combat, you can\u2019t even imagine how to make that call'", "utterance": "I thought at least that was sacred.", "utterer": "Robert Kelly"}, {"contentUrl": "https://www.yahoo.com/entertainment/john-kelly-says-president-obama-202207208.html", "img": "https://s.yimg.com/os/en-US/homerun/people_218/5d3af6b8a735ab9cd215cb6e13b93a09", "provider": "People", "summary": "White House Chief of Staff John Kelly said Thursday that former President Barack Obama did not call Kelly\u2019s family when his son was killed at the age of 29 while serving in Afghanistan in 2010.  \u201cObama did not call my family,\u201d Kelly said during a somber appearance in the White House briefing room, according to tweets by the Wall Street Journal\u2018s Rebecca Ballhaus and other reporters?.  The Washington Post and other outlets have reported that, according to White House records, Kelly and his wife were invited to a \u201cGold Star\u201d breakfast in May 2011, and sat at then-First Lady Michelle Obama\u2018s table.", "title": "John Kelly Says President Obama Did Not Call His Family When His Son Died -- But \u2018That Was Not a Criticism\u2019", "utterance": "That was not a criticism.", "utterer": "John Kelly"}, {"contentUrl": "https://uk.news.yahoo.com/george-w-bush-says-america-162409057.html", "img": "https://s.yimg.com/os/en-GB/homerun/newsweek_europe_news_328/9f1727114077214bd6b5b8450c3fe315", "provider": "Newsweek", "summary": "Former President George W. Bush warned about the rise of bigotry in the U.S. and the consequences of rejecting the outside world as he delivered a speech at the \"Spirit of Liberty\" forum in New York City on Thursday. Though he never explicitly mentioned President Donald Trump, Bush's remarks represented a staunch rejection of the commander-in-chief's embrace of isolationism and hyper-nationalism. \"Our identity as a nation\u2014unlike many other nations\u2014is not determined by geography or ethnicity, by soil or blood.", "title": "George W. Bush Says America Has Lost Its Identity in Trump Era", "utterance": "We need to recall and recover our own identity.", "utterer": "George W. Bush"}, {"contentUrl": "https://uk.news.yahoo.com/obama-delivers-veiled-withering-rebuke-022325670.html", "img": "https://s.yimg.com/os/en-GB/homerun/theguardian_763/f7f6978ed32ff9a19d11262b35559090", "provider": "The Guardian", "summary": "Barack Obama returned to the fray on Thursday with a fervent denunciation of Donald Trump in all but name, condemning the politics of division and rekindling the politics of hope.  The former US president earned deafening cheers at a rally ostensibly for the Democratic candidate in a gubernatorial election in Virginia.  In championing Ralph Northam\u2019s cause, Obama expressed his views on the state of the nation in the strongest terms since the inauguration of his successor and antithesis.", "title": "Obama delivers veiled but withering rebuke of Trump, urging a return to hope", "utterance": "You\u2019ll notice I haven\u2019t been commenting a lot on politics lately.", "utterer": "Barack Obama"}, {"contentUrl": "https://uk.sports.yahoo.com/news/jaguars-owner-shad-khan-jealousy-140159103.html", "img": "https://s.yimg.com/os/en-GB/homerun/article.sportingnews.com/eb6f40d29c0b8f67a7ea2fcbb5e04503", "provider": "Sporting News", "summary": "Jaguars owner Shad Khan has a theory as to why President Donald Trump has criticized NFL players and owners in the context of player protests during the national anthem: Trump is \"jealous\" of NFL ownership because he hasn't been able to join the group. \"He\u2019s been elected (p)resident, where maybe a great goal he had in life to own an NFL team is not very likely,\" Khan told USA Today at the NFL owners meetings in New York. So to make it tougher, or to hurt the league, it\u2019s very calculated.", "title": "Jaguars owner Shad Khan: Jealousy fueling President Donald Trump's anti-NFL rhetoric", "utterance": "So to make it tougher, or to hurt the league, it\u2019s very calculated.", "utterer": "Khan"}, {"contentUrl": "https://finance.yahoo.com/news/trumps-health-subsidy-shutdown-could-084924475.html", "img": "https://s.yimg.com/os/en_us/News/ap_webfeeds/cb14960b537c48168f242e0405a6cf0d.jpg", "provider": "Associated Press", "summary": "If President Donald Trump prevails in shutting down a major \"Obamacare\" health insurance subsidy, it would have the unintended consequence of making free basic coverage available to more people, and making upper-tier plans more affordable.  The unexpected assessment comes from consultants, policy experts, and state officials, who are trying to discern the potential fallout from a Washington health care debate that's becoming even more complicated and volatile.  It's because another subsidy that's part of the health law would go up for people with low-to-moderate incomes, offsetting Trump's move.", "title": "Trump's health subsidy shutdown could lead to free insurance", "utterance": "It's a kind of counter-intuitive result.", "utterer": "Kurt Giesa"}, {"contentUrl": "https://finance.yahoo.com/news/murder-insurance-protection-self-defense-cases-102308783.html", "img": "https://s.yimg.com/os/en_us/News/ap_webfeeds/5363651134c05e10520f6a706700eecb.jpg", "provider": "Associated Press", "summary": "The National Rifle Association is offering insurance for people who shoot someone, stirring criticism from gun-control advocates who say it could foster more violence and give gun owners a false sense of security to shoot first and ask questions later.  Some are calling it \"murder insurance,\" and say that rather than promoting personal responsibility and protection, it encourages gun owners to take action and not worry about the consequences.  Guns Down, a gun-control group formed last year, is running an ad campaign to criticize the NRA's new insurance.", "title": "'Murder insurance' or protection in self-defense cases?", "utterance": "Is the potential public relations mess worth the small amount.", "utterer": "Peter Kochenburger"}, {"contentUrl": "https://www.yahoo.com/lifestyle/playboy-makes-history-first-transgender-154601490.html", "img": "http://b.static.trunity.net/files/299501_299600/299598/vertical-farming-chris-jacobs.jpg", "provider": "HuffPost", "summary": "Later in the interview, the model reflects on society\u2019s limited notions of womanhood, saying that \u201cBeing a woman doesn\u2019t mean being extremely feminine all the time. Being a woman is just being a woman. Playmate is a title given to select women who appear in the centerfold pictorial in each issue of Playboy magazine.", "title": "Playboy Makes History With Its First Transgender Playmate", "utterance": "I lived a long time without saying I was transgender.", "utterer": "Rau"}, {"contentUrl": "https://sports.yahoo.com/richard-sherman-kaepernicks-unsigned-status-hear-every-excuse-world-142234737.html", "img": "https://s.yimg.com/os/en/homerun/feed_manager_auto_publish_494/150173f229be95091b095f215aa63f87", "provider": "Shutdown Corner", "summary": "Colin Kaepernick has likely ended any chance of playing in the NFL ever again by filing a grievance against the league. And while the merits of his collusion case remain up for debate, at least one player has no doubt about where he stands. Seattle Seahawks cornerback Richard Sherman has looked around at the league and seen the quarterbacks signed, both as backups and as starters for injured regulars, and has come to a conclusion about Kaepernick: that team owners \u201chad a point to make and they made it.", "title": "Richard Sherman on Colin Kaepernick's unsigned status: 'You hear every excuse in the world'", "utterance": "You hear every excuse in the world.", "utterer": "Richard Sherman"}, {"contentUrl": "https://uk.news.yahoo.com/white-men-told-hide-nazi-161138050.html", "img": "https://s.yimg.com/os/en-GB/homerun/newsweek_europe_news_328/93d9bfe1a7bc9075c48d5934431e566a", "provider": "Newsweek", "summary": "The Daily Stormer, a neo-Nazi website that frequently singles out Jewish people for harassment, has posted a set of \u201ctips\u201d for alt-right leader Richard Spencer\u2019s very expensive talk at the University of Florida in Gainesville Thursday afternoon, and one of them sticks out above others. Go to the event, preferably as a group, dressed normally, no uniform or racist anything,\u201d neo-Nazi blogger Andrew Anglin writes, \u201cNo white polo or khakis, no flags or signs, if you\u2019ve got Nazi tattoos cover them up. The \u201ctips\u201d could undercut Spencer\u2019s attempt at branding his white nationalist political movement as something other than a hate group.", "title": "White Men Told to Hide Nazi Tattoos Ahead of Richard Spencer Florida Rally", "utterance": "I\u2019d rather he not be here.", "utterer": "Spencer\u2019s"}];
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

var paginate = function(move_forward) {
    if(move_forward)    {
        if (current_index == stories.length - 1) return;
        current_index++;
    }
    else {
        if (current_index == 0) return;
        current_index--;
    }
    /*
    // if we want to transform individual items
    var thingers = document.getElementById('stage').childNodes;
    for(var i = 0; i < thingers.length && false; i++)    {
        var corrective = ((-1 * i - current_index));
        // thingers[i].style.transform = 'translate3d(' + (i * 100) + '%, 0px, ' + (corrective * -50) + 'px) rotateY(' + (corrective * -10) + 'deg)';;
        // thingers[i].style.opacity = (current_index == (-1 * i)) ? 1 : 0;
    }
    */
    document.getElementById('stage').style.transform = 'translate(-' + (current_index * 100) + '%)';
};

document.addEventListener("mousemove", function(event) {
    // console.log(((document.body.offsetWidth / 2) - event.pageX) / (document.body.offsetWidth / 2));
    var x_scaler = 0.25 * ((document.body.offsetWidth / 2) - event.pageX) / (document.body.offsetWidth / 2);
    var y_scaler = ((document.documentElement.clientHeight / 2) - event.pageY) / (document.documentElement.clientHeight / 2);
    y_scaler = 0;
    // console.log(y_scaler)

    var imgs = document.getElementsByClassName('i_photograph');
    for(var i = 0; i < imgs.length; i++)    {
        // imgs[i].style.transform = 'translateX(' + (x_scaler * 100) + 'px) translateY(' + (y_scaler * 100) + 'px) scale(' + (1 + Math.abs(x_scaler)) + ')';
        imgs[i].style.transform = 'translateX(' + (x_scaler * 100) + 'px) ' + 'translateY(' + (y_scaler * 100) + 'px) ' + 'rotateY(' + (x_scaler * -3) + 'deg)';
    }
});

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
        i_contentbody_string += '<h1>' + stories[i].title + '</h1>';
        // provider
        i_contentbody_string += '<div class="provider_attribution">' + stories[i].provider + '</div>';
        // separator
        i_contentbody_string += '<div class="separator" style="background: ' + theme_color + '; color: ' + theme_color +'">--</div>';
        // summary
        i_contentbody_string += '<div>' + stories[i].summary +'</div>';
        // wrap it up b
        i_contentbody.innerHTML = i_contentbody_string;
        // what do you do after this
        var i_upnext = document.createElement('div');
        i_upnext.className = 'up_next';
        i_upnext.innerHTML = '<a class="more" style="background: ' + theme_color + '" href="' + stories[i].contentUrl + '">read more &raquo;</a>';
        // add the what you do after this
        i_contentbody.appendChild(i_upnext);

        i_thinger.appendChild(i_contentbody)

        document.getElementById('stage').appendChild(i_thinger);
    }

    dom_content_loaded = true;
});

</script>
</html>
