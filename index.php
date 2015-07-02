<style>
body>header{
	font-size: 5em;
	text-align:center;
	margin-top: 100px;
}
body>header h1{
	background-color:#09f;
	color:white;
	font-family: Candara, Arial, sans-serif;
	padding:20px 40px;
	margin:0;
	display:inline-block;
	min-width: 3.5em;
	width: 40%;
}
#arrow{
	display:inline-block;
	height:0px;
	width:0px;
	border: solid 30px #000;
	border-color:#09f transparent transparent transparent;
}


/*https://css-tricks.com/snippets/css/simple-and-nice-blockquote-styling/*/
blockquote {
  background: #f9f9f9;
  border-left: 10px solid #ccc;
  margin: 1.5em 10px;
  padding: 0.5em 10px;
  quotes: "\201C""\201D""\2018""\2019";
}
blockquote:before {
  color: #ccc;
  content: "\00201C";
  font-size: 4em;
  line-height: 0.1em;
  margin-right: 0.25em;
  vertical-align: -0.4em;
}


section{
	border:solid 1px #000;
	margin: -25px 100px 50px 100px;
	padding: 30px;
}
section h2{
	margin: 0px;
}
</style>

<header>
	<h1>HoLHS</h1>
	<!--aside><img src="fb.png"></aside-->
	<br>
	<span id="arrow"></span>
</header>

<?php
require "secrets.php";
if(file_exists($jsonfile))
	$IMAGE_DATA=json_decode(file_get_contents($jsonfile),true);
else
	echo "<center>[no images! an error occurred]</center>";

$number_to_get = 5;

foreach($IMAGE_DATA as $post){
	if(!is_array($post))continue;
	
	$date = date("F j, Y",strtotime($post['time']));
	$picurl = "img/{$post['object_id']}.jpg";
	$msg = nl2br($post['message']);
	echo <<<HEREDOC
<section>
	<header>
		<h2>{$date}</h2>
	</header>
	<div>
		<img src='{$picurl}' alt='HoLHS Photo {$date}' />
	</div>
	<blockquote>
		{$msg}
	</blockquote>
</section>
HEREDOC;
	if(--$number_to_get <= 0) break;
}

?>
