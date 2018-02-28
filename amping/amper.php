<?php

$stories = array(
	array('discovered' => '39m', 'url' => 'http://abcnews.go.com/amp/US/suspect-facebook-live-killing-turned-sheriff/story?id=53386141'),
	array('discovered' => '2h', 'url' => 'https://mobile.reuters.com/article/amp/idUSKCN1GB26Z'),
	array('discovered' => '2h', 'url' => 'https://amp.cnn.com/cnn/2018/02/27/us/north-carolina-man-shot-on-facebook-live/index.html'),
	array('discovered' => '2h', 'url' => 'http://www.dailymail.co.uk/news/article-5440649/amp/Man-shot-dead-Facebook-Live-outing-drug-dealers.html'),
	array('discovered' => '2h', 'url' => 'http://www.independent.co.uk/news/world/americas/facebook-live-shooting-north-carolina-wingate-university-prentis-robinson-douglas-colson-latest-a8230846.html?amp'),
	array('discovered' => '3h', 'url' => 'https://www.myajc.com/news/crime--law/atlanta-musician-killed-during-facebook-live-stream/QRnNI0ZvRv27yzvDMoMklI/amp.html'),
	array('discovered' => '3h', 'url' => 'https://patch.com/north-carolina/charlotte/amp/27487704/facebook-live-shooting-suspect-turns-himself-report'),
	array('discovered' => '4h', 'url' => 'http://www.wfmynews2.com/amp//news/local/wingate-police-interviewing-person-of-interest-in-deadly-shooting-caught-on-facebook-live/523688618'),
	array('discovered' => '6h', 'url' => 'http://www.newsweek.com/facebook-live-north-carolina-murder-prentis-robinson-821489?amp=1'),
	array('discovered' => '11h', 'url' => 'http://www.dailymail.co.uk/news/article-5437573/amp/Man-films-moment-murdered-Facebook-Live-video.html'),
	array('discovered' => '12h', 'url' => 'http://www.ibtimes.com/prentis-robinsons-facebook-live-murder-suspect-identified-north-carolina-police-2657838?amp=1'),
	array('discovered' => '18h', 'url' => 'https://www.buzzfeed.com/amphtml/blakemontgomery/a-man-was-killed-on-facebook-live'),
	array('discovered' => '19h', 'url' => 'http://amp.fox32chicago.com/news/national/man-murdered-on-facebook-live-was-outing-suspected-drug-dealers'),
	array('discovered' => '19h', 'url' => 'https://heavy.com/news/2018/02/prentis-robinson-facebook-live-shooting-photos-videos/amp/'),
	array('discovered' => '19h', 'url' => 'http://amp.fox32chicago.com/news/national/man-murdered-on-facebook-live-was-outing-suspected-drug-dealers'),
	array('discovered' => '20h', 'url' => 'https://nypost.com/2018/02/26/man-livestreams-his-own-fatal-shooting-near-college-cops/amp/'),
	array('discovered' => '21h', 'url' => 'http://metro.co.uk/2018/02/26/musician-celebrating-birthday-films-murder-facebook-live-7344314/amp/'),
	array('discovered' => '22h', 'url' => 'https://www.mercurynews.com/2018/02/26/man-films-own-killing-on-facebook-live/amp/')
);

$metas = array();
for($i = 0; $i < count($stories); $i++)	{
	echo $stories[$i]['url'] . "\n";

	libxml_use_internal_errors(true);
	$get_c = file_get_contents($stories[$i]['url']);
	$get_d = new DomDocument();
	$get_d->loadHTML($get_c);
	$get_xp = new domxpath($get_d);

	$site_name = '';
	$title = '';
	$desc = '';
	$image = '';
	// $image_w = '';
	// $image_h = '';

	foreach ($get_xp->query("//meta[@property='og:title']") as $get_el) {
	    $title = $get_el->getAttribute("content");
	}
	foreach ($get_xp->query("//meta[@property='og:description']") as $get_el) {
	    $desc = $get_el->getAttribute("content");
	}
	foreach ($get_xp->query("//meta[@property='og:image']") as $get_el) {
	    $image = $get_el->getAttribute("content");
	}
	foreach ($get_xp->query("//meta[@property='og:site_name']") as $get_el) {
	    $site_name = $get_el->getAttribute("content");
	}

	array_push($metas, array(
		'site_name' => $site_name,
		'title' => $title,
		'desc' => $desc,
		'image' => $image,
		'url' => $stories[$i]['url'],
		'discovered' => $stories[$i]['discovered']
	));
}

echo json_encode($metas);

?>
