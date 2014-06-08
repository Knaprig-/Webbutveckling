<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<link rel="stylesheet" type="text/css" href="styles/indexStyle.css">
	<title>Läxikon</title>
</head>
<body>
<div id="topBarContainer">
	<a href="index.php"><img id="logotype" src="grafik/logo.png" alt="Logotype" title="Main Menu"></a>

	<nav id="mainNav">
		<ul>
			<a href="index.php"><li class="currentPage" id="summaryButton">Summaries</li></a>
			<a href="submit.php"><li id="contributeButton">Contribute</li></a>
			<a href="login.php"><li id="loginButton">Login</li></a>
			<a href="register.php"><li id="registerButton">Register</li></a>
		</ul>
	</nav>
	<div id="searchBox">
		<form id="searchForm" action="index.php" method="GET">
		<p>
			<input id="searchField" type="text" name="searchField">
			<input id="searchButton" type="submit" value="Search for summaries!">
		</p>
		</form>
	</div>

</div>
<div id="contentRoof"></div>
<div id="mainContainer">
<?php
session_start();
if (empty($_SESSION)) {
	$_SESSION["userName"] =  "";
	$_SESSION["userLoggedIn"] = false;
	$_SESSION['userId'] = "";
	$_SESSION['coursePref'] = array();
}

$host = "localhost";
$dbname = "laxikon";
$username = "gustavgrannsjo";
$password = "";

$dsn = "mysql:host=$host;dbname=$dbname";
$attr = array(PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC);

$PDO = new PDO($dsn, $username, $password, $attr);

if($PDO){

	if ($_SESSION['userLoggedIn']) {
		
		$prefs = "";	
		foreach ($_SESSION['coursePref'] as $row) 
		{
			if(empty($prefs))
			{
				$prefs = "courseId=" . $row;
			}	
			else
			{
				$prefs = $prefs . " OR " . "courseId=" . $row; 
			}

		}
		$postGetter = $PDO->prepare("SELECT * FROM posts WHERE " . $prefs . " ORDER BY dateTime DESC");	
	}
	else
	{
		$postGetter = $PDO->prepare("SELECT * FROM posts ORDER BY dateTime DESC");
	}

	if (!empty($_GET)) {
		$_GET = null;

		$searchTerms = explode(" " , filter_input(INPUT_GET, "searchField", FILTER_SANITIZE_SPECIAL_CHARS));
		$searchSql = "SELECT * FROM posts WHERE ";
		$tempCounter = 0;
		foreach ($searchTerms as $row) 
		{
			if ($tempCounter == 0)
			{
				$searchSql .= "title LIKE ? OR content LIKE ?";
			}
			else
			{
				$searchSql .= " OR title LIKE ? OR content LIKE ?";
			}
			$tempCounter++;
		}
		$postGetter = $PDO->prepare($searchSql);
		$tempCounter = 0;
		$arr = array();
		foreach ($searchTerms as $row) 
		{
			$row = "%".$row."%";
			array_push($arr, $row);
			array_push($arr, $row);
			$tempCounter++;
			$postGetter->bindParam($tempCounter, $arr[$tempCounter - 1], PDO::PARAM_STR);

			$tempCounter++;
			$postGetter->bindParam($tempCounter, $arr[$tempCounter - 1], PDO::PARAM_STR);
		}
	}

	

	$postGetter->execute();
	while ($row = $postGetter->fetch()) 
	{
		$courseGetter = $PDO->prepare("SELECT courseName FROM courses WHERE courseId = " . $row['courseId']);
		$commentGetter = $PDO->prepare("SELECT COUNT(*) AS count FROM comments WHERE postId =  " . $row['id']);
		$ratingGetter = $PDO->prepare("");
		$authorGetter = $PDO->prepare("SELECT name as authorName FROM users WHERE id = :authorId");
		$authorGetter->bindParam(":authorId", $row['authorId']);

		$courseGetter->execute();
		$courseName = $courseGetter->fetch();
		$subSum = substr($row['content'], 0, 50) . "..."; 

		$authorGetter->execute();
		$authorName = $authorGetter->fetch();

		if (strlen($row['title']) > 20) {
			$subTitle = substr($row['title'], 0, 20) . "...";
		}
		else
		{
			$subTitle = substr($row['title'], 0, 20);
		}
		
		$commentGetter->execute();
		$commentCount = $commentGetter->fetch();

		if ($row['request'] == 1) {
			$postType = "request";
			$requestText = "[Request]";
		}
		else
		{
			$postType = "summary";
			$requestText = "[Summary]";
		}

		echo "
		<div class=\"post\">
			<h2><a class=\"underLined\" href=\"summary.php?postId={$row['id']}\">{$subTitle} <span class=\"{$postType}\">{$requestText}</span></a></h2><h3><a class=\"underLined\" href=\"profile.php?userId={$row['authorId']}\">{$authorName['authorName']}</a></h3><div class=\"clear\"></div><p><a href=\"summary.php?postId={$row['id']}\">{$courseName['courseName']}</a></p><p><a href=\"summary.php?postId={$row['id']}\">{$row['dateTime']}</a></p><p><a href=\"summary.php?postId={$row['id']}\">{$commentCount['count']} Comments</a></p><p><a href=\"summary.php?postId={$row['id']}\">{$subSum}</a></p>
		</div>";
	}
}

?>
</div>
<div id="contentFooter"></div>
</body>
</html>