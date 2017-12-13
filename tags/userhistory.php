<html>
<head>
<style type="text/css">
	body {
		width: 500px;
		background: #eee;
	}

	div.message_block	{
		border: 1px solid gray;
		margin: 10px 0;
		padding: 10px;
		box-sizing: border-box;
		background: white;
	}

	div.context {
		margin-bottom: 10px;
		font-weight: bold;
	}
</style>
</head>
<body>
<?php
error_reporting(0);
set_time_limit(3600);
ini_set('memory_limit', '512M');

// get our shared service for vibes
require('../shared/allvibes.php');
require('../shared/utilities.php');

$guid = $_GET['guid'];
$action = $_GET['action'];

if($action == 'whitelist')	{
	$whitelist = json_decode(file_get_contents('caches/whitelist.json'), true);
	$whitelist[$guid] = 1;
	file_put_contents('caches/whitelist.json', json_encode($whitelist));
}
if($action == 'blacklist')	{
	$blacklist = json_decode(file_get_contents('caches/blacklist.json'), true);
	$blacklist[$guid] = 1;
	file_put_contents('caches/blacklist.json', json_encode($whitelist));
}

?>

<a href="?guid=<?php echo $guid; ?>&action=whitelist">whitelist this user</a>
<a href="?guid=<?php echo $guid; ?>&action=blacklist">blacklist this user</a>

<?php

$msgs = get_user_message_history($guid);

// echo json_encode($msgs);

for($i = 0; $i < count($msgs); $i++)	{
	echo '<div class="message_block">';
	echo '<div class="context" style="font-style: italic">' . $msgs[$i]['context_info']['displayText'] . '</div>';
	echo '<div style="white-space: pre-wrap">' . $msgs[$i]['text'] . '</div>';
	echo '</div>';
}
?>
</body>
</html>