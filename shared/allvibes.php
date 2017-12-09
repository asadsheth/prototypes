<?php
// search api
// http://mobile-homerun-yql.vibe.production.omega.gq1.yahoo.com:4080/api/vibe/v1/search/topics?query=health&enforcePostAcl=false

// vibe meta api with id
// http://mobile-homerun-yql.media.yahoo.com:4080/api/vibe/v1/editorial/topics/944d17a2-7cd5-337f-af95-2c7dd5e431f4

$ALL_VIBES = array(
	// here: random vibes i like ---------------------
	array( 'name' => 'NBA', 'id' => 'e238b3d0-c6d5-11e5-af54-fa163e2c24a6' ),
	array( 'name' => 'Finance', 'id' => '338950e1-cae3-359e-bfa3-af403b69d694' ),
	array( 'name' => 'Politics', 'id' => 'dbb2094c-7d9a-37c0-96b9-7f844af62e78', 'slackhook' => 'https://hooks.slack.com/services/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' ),
	array( 'name' => 'United States', 'id' => 'f5504734-2071-32a6-b729-74a9b3141a44' ),
	array( 'name' => 'Transportation', 'id' => '7fa3e636-f5f1-3e5e-bc73-e15fa8fa8d10' ),
	array( 'name' => 'Society & Culture', 'id' => '5f31157d-3f7d-30fd-af79-9f13bf1f304c' ),
	array( 'name' => 'Mar-a-Lago', 'id' => '93c20339-94e8-3bc1-92a6-69efcfe7df32' ),
	array( 'name' => 'InfoSec', 'id' => 'b7ceb013-9918-317e-ba58-bdb2987cf440', 'slackhook' => 'https://hooks.slack.com/services/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' ),
	array( 'name' => 'Feminism', 'id' => '86c832e0-078c-312f-95b9-1e77d1a0b8c6' ),
	array( 'name' => 'Bitcoin', 'id' => '98714316-d8b5-30ad-be71-77f8e9a5eb36', 'slackhook' => 'https://hooks.slack.com/services/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' ),
	array( 'name' => 'Personal Finance', 'id' => 'da8561ef-8822-31d3-9509-283a3d2b5223', 'slackhook' => 'https://hooks.slack.com/services/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' ),
	array( 'name' => 'Listen to America', 'id' => 'bfebc5e7-586f-3476-b78a-558b0bfc2f94' ),
	array( 'name' => 'MLB', 'id' => 'aae76c94-9dc9-11e5-a70a-fa163ecf49c3', 'slackhook' => 'https://hooks.slack.com/services/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' ),
	array( 'name' => 'Celebrity', 'id' => 'b7ddaf4b-9395-34b6-9ddc-e32547089110', 'slackhook' => 'https://hooks.slack.com/services/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' ),
	array( 'name' => 'Deep Learning', 'id' => '26680209-25eb-3186-ad86-033a8af16364' ),
	array( 'name' => 'Internet of Things', 'id' => 'c8a104ba-3365-3ebc-b8f2-cc2a332ce724' ),
	array( 'name' => 'Cricket', 'id' => '9831eeef-324e-3d73-8572-0cba84be3693', 'slackhook' => 'https://hooks.slack.com/services/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' ),
	array( 'name' => 'Korean Tensions', 'id' => '0f201ff2-4afb-11e5-a268-fa163e6f4a7e', 'slackhook' => 'https://hooks.slack.com/services/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' ),
	array( 'name' => 'Virtual Reality', 'id' => '2ab16973-6dc1-33f7-9434-6c81f38d1eea' ),
	array( 'name' => 'Odd News', 'id' => '4cc44322-c2d9-3f74-a5db-9b00e071574f' ),
	array( 'name' => 'Artificial Intelligence', 'id' => '106dab7c-e883-3cad-a20c-0ba7af1ec7fe', 'slackhook' => 'https://hooks.slack.com/services/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' ),
	array( 'name' => 'Astronomy', 'id' => '28d52c31-89c5-330f-9d52-61eec9fa77cc' ),
	array( 'name' => 'Health Care Reform', 'id' => 'a0d7935a-b327-11e5-bc1e-fa163e6f4a7e'),
	array( 'name' => 'Sports', 'id' => '5c839b50-00d3-37e8-a68f-7c03c48d7104', 'slackhook' => 'https://hooks.slack.com/services/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' ),
	array( 'name' => 'Trump Family', 'id' => '88cfc604-82db-3595-984d-76ff5706bcff'),

	// here: video franchises ---------------------
	array( 'name' => 'Dash', 'id' => '2bcb0f23-d4c8-3994-9e0e-c0fdcbd27ddd'),
	array( 'name' => 'Sound Off', 'id' => '4cd9e46f-e419-3804-b2aa-be5bef3eca88'),
	array( 'name' => 'Now I Get It', 'id' => '6737015f-a6ba-3a61-88c1-6e993dca285e'),
	array( 'name' => 'Build Last Minute', 'id' => '62d54d6e-661d-3cf5-930f-937b6657da5f'),
	array( 'name' => 'You Win, Internet', 'id' => '04e86925-cdd3-3829-8198-88004b017870'),
	array( 'name' => '#MyStyle', 'id' => '3096da92-77de-3d0c-b8fa-73ea816476ba'),
	array( 'name' => 'Full Coverage', 'id' => '523381cf-0972-3ed7-8bcc-4e5851fbf7e9'),
	array( 'name' => 'The Sweet Spot', 'id' => '6d3ce0d4-6e8b-3593-88f4-31bae42df84f'),
	array( 'name' => 'Sports Minute', 'id' => '0fc0b09d-bb80-3f71-aaee-f22b3fe4859e'),
	array( 'name' => 'Last Night Now', 'id' => '84a3202d-cc3e-3841-b38b-926df2ede393'),
	array( 'name' => '#CopyThat', 'id' => 'e0efc05c-28aa-39ff-9aab-f58247fac4b2'),
	array( 'name' => 'Morning Rundown', 'id' => '2f289dc2-b346-3007-a13c-0ef4d1f322a3'),
	array( 'name' => 'In The Know', 'id' => '90d9dd64-4101-3713-b303-41c1dc7f9140'),
	array( 'name' => 'Who Will Win', 'id' => '9f6bbdce-890e-35bc-9a60-ab1711552f3c'),
	array( 'name' => 'iWitness', 'id' => '22c9d124-9882-322a-806d-f39487400856'),
	array( 'name' => 'Unfiltered', 'id' => '4cbcad96-b3aa-3fe0-8f3a-3498fb29721e'),

	// starting here these are data-based - most popular vibes ---------------------
	array( 'name' => 'Wildfires', 'id' => '944d17a2-7cd5-337f-af95-2c7dd5e431f4' ),
	array( 'name' => 'Entertainment', 'id' => '7563eca7-14c1-3a20-ab16-782788cde33a' ),
	array( 'name' => 'U.S. News', 'id' => 'ecd5e8af-dc90-3332-9efb-d522bf6b8dfa' ),
	array( 'name' => 'NFL', 'id' => '110a9e34-b2c8-11e5-9dd2-fa163e2c24a6' ),
	array( 'name' => 'Science', 'id' => 'fc98c570-0d12-33f5-aa5a-f89224e57bdc' ),
	array( 'name' => 'President Trump', 'id' => '433beca8-469f-3942-9fad-a13615dd8aa8' ),
	array( 'name' => 'World News', 'id' => '69f70237-124f-3ea9-acd0-fc922af945e2' ),
	array( 'name' => 'Pets', 'id' => '3b2a0de9-9898-3b2b-9c3d-25fa6d4916d9'),
	array( 'name' => 'Relationships', 'id' => 'ce737d28-de97-3252-975e-30a192aaa3bb'),
	array( 'name' => 'TV buzz', 'id' => '785942fb-66c8-3270-9bb0-2b282695de68' ),	
	array( 'name' => 'Movies', 'id' => '46906397-b90f-3125-a26e-b000769d9eb5' ),	
	array( 'name' => 'Video games', 'id' => 'fd9a1486-f1c9-3189-9a24-eccd23fe6478' ),	
	array( 'name' => 'Recommended For You', 'id' => '@MEGASTREAMVIDEO' ),
	// array( 'name' => '@Megastream', 'id' => '@MEGASTREAM' ),
	array( 'name' => 'Top Stories', 'id' => '@NTKVIDEO' )
);

// go get the meta for all these vibes
for($i = 0; $i < count($ALL_VIBES); $i++) {
	$response = file_get_contents('http://mobile-homerun-yql.media.yahoo.com:4080/api/vibe/v1/editorial/topics/' . $ALL_VIBES[$i]['id']);
	$ALL_VIBES[$i]['meta'] = (json_decode($response, true)['topics']['result'][0]);
}

// alphabetize (ish) the list
for($i = 0; $i < count($ALL_VIBES); $i++) {
	// reorder as needed (bubble sortish)
	for($j = $i + 1; $j < count($ALL_VIBES); $j++) {
		if(
			strtolower($ALL_VIBES[$i]['name']) > strtolower($ALL_VIBES[$j]['name']) // alphabetize
			|| strstr($ALL_VIBES[$j]['id'], '@') // special vibes
		) {
			$tmp = json_encode($ALL_VIBES[$i]);
			$ALL_VIBES[$i] = json_decode(json_encode($ALL_VIBES[$j]), true);
			$ALL_VIBES[$j] = json_decode($tmp, true);
		}
	}	
}
?>
