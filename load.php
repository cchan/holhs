<h1>HoLHS Image Importer</h1>
<?php
require "secrets.php";

if(!hash_equals($_GET['pass'],$password)){ //Secret password to let this process happen
	header("Location: index.php");
	die();
}

set_time_limit(30);
$max_to_retrieve = 150;
$time_to_wait = 3600;

if(file_exists("images.json")){ //If $time_to_wait seconds have not yet elapsed, don't make this query agin
	$OLD_IMAGE_DATA=json_decode(file_get_contents("images.json"),true);
	if(time() - $OLD_IMAGE_DATA["lastUpdated"] < $time_to_wait){
		$remaining = $time_to_wait - (time() - $OLD_IMAGE_DATA->lastUpdated);
		die("Error: Wait at least $time_to_wait seconds between consecutive queries. (remaining: $remaining seconds)");
	}
}

$page_posts = json_decode(file_get_contents("https://graph.facebook.com/humansoflhs/posts?limit={$max_to_retrieve}&access_token=".$access_token), true);
if(@$page_posts["error"]){var_dump($page_posts["error"]);die();}

echo "Retrieved: ".count($page_posts['data'])." posts [max {$max_to_retrieve}]<br>";
echo "(If it times out, just reload and it'll keep working from where it started).<br>";


//Insert all images into JSON, and retrieve the image files if necessary
$IMAGE_DATA = [];
$i = 0;
foreach($page_posts['data'] as $post){
	if(!ctype_digit($post['object_id']))die("Fatal error: invalid object_id");
	
	//Get Likes/Comments/Shares
	/*
	$likes_request = json_decode(file_get_contents("https://graph.facebook.com/{$post['object_id']}/likes?summary=1"));
	$likes = 0;//$likes_request->summary->total_count;
	$comments = @$post['comments'] ? count($post['comments']['data']) : "0"; //no count field?? will overflow tho
	$shares = @$post['shares'] ? $post['shares']['count'] : "0";
	*/
	
	//Insert the data into JSON
	$IMAGE_DATA[] = [
		"object_id"=>$post['object_id'],
		"time"=>$post['updated_time'],
		"message"=>$post['message']
	];
	
	//Download the photo
	if(file_exists("img/{$post['object_id']}.jpg")){
		echo "[".(++$i)."] Image {$post['object_id']} already exists, skipping.<br>";
		ob_flush();flush();
	}
	else{
		echo "[".(++$i)."] copying image {$post['object_id']}...";
		ob_flush();flush();
		
		if(@!copy("https://graph.facebook.com/{$post['object_id']}/picture","img/{$post['object_id']}.jpg"))
			echo " Unexpected error. Just download it manually from <a href='https://graph.facebook.com/{$post['object_id']}/picture'>https://graph.facebook.com/{$post['object_id']}/picture</a> to img/{$post['object_id']}.jpg.";
		else
			echo " Done!";
		
		echo "<br>";
		ob_flush();flush();
	}
}

usort($IMAGE_DATA,function($a,$b){return ($a["time"]>$b["time"])?-1:1;}); //Sort them in reverse chronological order
$IMAGE_DATA["lastUpdated"] = time();//Add the lastUpdated field
file_put_contents("images.json", json_encode($IMAGE_DATA), LOCK_EX); //and write the JSON data
?>
<p><b>Done importing everything!</b></p>
