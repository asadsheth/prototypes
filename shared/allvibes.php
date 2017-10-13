<?php
// search api
// http://mobile-homerun-yql.vibe.production.omega.gq1.yahoo.com:4080/api/vibe/v1/search/topics?query=health&enforcePostAcl=false

// vibe meta api with id
// http://mobile-homerun-yql.media.yahoo.com:4080/api/vibe/v1/editorial/topics/944d17a2-7cd5-337f-af95-2c7dd5e431f4

$ALL_VIBES = array(
	array( 'name' => 'NBA', 'id' => 'e238b3d0-c6d5-11e5-af54-fa163e2c24a6' ),
	array( 'name' => 'NFL', 'id' => '110a9e34-b2c8-11e5-9dd2-fa163e2c24a6' ),
	array( 'name' => 'Finance', 'id' => '338950e1-cae3-359e-bfa3-af403b69d694' ),
	array( 'name' => 'Politics', 'id' => 'dbb2094c-7d9a-37c0-96b9-7f844af62e78', 'slackhook' => 'https://hooks.slack.com/services/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' ),
	array( 'name' => 'United States', 'id' => 'f5504734-2071-32a6-b729-74a9b3141a44' ),
	array( 'name' => 'Transportation', 'id' => '7fa3e636-f5f1-3e5e-bc73-e15fa8fa8d10' ),
	array( 'name' => 'Society & Culture', 'id' => '5f31157d-3f7d-30fd-af79-9f13bf1f304c' ),
	array( 'name' => 'Entertainment', 'id' => '7563eca7-14c1-3a20-ab16-782788cde33a' ),
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
	array( 'name' => 'Astronomy', 'id' => '28d52c31-89c5-330f-9d52-61eec9fa77cc' ),
	array( 'name' => 'Health Care Reform', 'id' => 'a0d7935a-b327-11e5-bc1e-fa163e6f4a7e')
);

for($i = 0; $i < count($ALL_VIBES); $i++) {
	for($j = $i + 1; $j < count($ALL_VIBES); $j++) {
		if($ALL_VIBES[$i]['name'] > $ALL_VIBES[$j]['name']) {
			$tmp = json_encode($ALL_VIBES[$i]);
			$ALL_VIBES[$i] = json_decode(json_encode($ALL_VIBES[$j]), true);
			$ALL_VIBES[$j] = json_decode($tmp, true);
		}
	}	
}
?>
