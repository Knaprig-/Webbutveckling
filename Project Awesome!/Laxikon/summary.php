<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<link rel="stylesheet" type="text/css" href="styles/summaryStyle.css">
	<title>Läxikon</title>
</head>
<body>
<div id="topBarContainer">
	<a href="index.php"><img id="logotype" src="grafik/logo.png" alt="Logotype" title="Main Menu"></a>

	<nav id="mainNav">
		<ul>
			<a href="index.php"><li id="summaryButton">Summaries</li></a>
			<a href="submit.php"><li id="contributeButton">Contribute</li></a>
			<a href="login.php"><li id="loginButton">Login</li></a>
			<a href="register.php"><li id="registerButton">Register</li></a>
		</ul>
	</nav>
</div>
<div id="contentRoof"></div>
<div id="mainContainer">
<?php
session_start();
	
$host = "localhost";
$dbname = "laxikon";
$username = "gustavgrannsjo";
$password = "";

$dsn = "mysql:host=$host;dbname=$dbname";
$attr = array(PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC);

$pdo = new PDO($dsn, $username, $password, $attr);

if($pdo){

	if (!empty($_GET)) {
		$_GET = null;
		$postId = filter_input(INPUT_GET, 'postId', FILTER_SANITIZE_SPECIAL_CHARS);
		$statement = $pdo->prepare("SELECT posts.*, users.name as userName, courses.*  
			FROM posts 
			JOIN courses ON posts.courseId = courses.courseId 
			JOIN users ON posts.authorId = users.id 
			WHERE posts.id = :postId");
		$statement->bindparam(":postId", $postId);
		if($statement->execute())
		{
			while($row = $statement->fetch())
			{
				echo 
				"<div class=\"summary\">
					<h2>{$row['title']}</h2><p>Course: {$row['courseName']}</p><p>Written By: <a href=\"profile.php?userId={$row['authorId']}\">{$row['userName']}</a></p><p>{$row['dateTime']}</p>";
					if ($row['request'] == 1) {
						echo "<p>This is a request</p>";
					}
					else {
						echo "<p>This is a summary</p>";
					}
					echo "<section>{$row['content']}</section>
				</div>";
			}
		}
		else {
			print_r($statement->errorInfo());
		}

		if (!empty($_POST)) {
			$_POST = null;
			$comCont = filter_input(INPUT_POST, 'comCont', FILTER_SANITIZE_SPECIAL_CHARS);
			$comState = $pdo->prepare("INSERT INTO comments (id, content, dateTime, authorId, postId) VALUES (0, :comCont, NOW(), :authorId, :postId)");
			$comState->bindParam(":comCont", $comCont);
			$comState->bindParam(":authorId", $_SESSION['userId']);
			$comState->bindParam(":postId", $postId);
			$comState->execute();
		}

		if ($_SESSION['userLoggedIn'] == true) 
		{
			echo "
			<div class=\"submitBox\">
				<form action=\"\" method=\"POST\">
					<textarea name=\"comCont\"></textarea>
					<input type=\"submit\" value=\"Post comment\">
				</form>
			</div>
			";
		}

		$commentGetter = $pdo->prepare("SELECT comments.*, users.name AS authorName 
			FROM comments JOIN users ON comments.authorId = users.id 
			WHERE :postId = comments.postId");
		$commentGetter->bindParam(":postId", $postId);
		$commentGetter->execute();
		while ($row = $commentGetter->fetch()) 
		{
			echo "
			<div class=\"comment\">
				<p><a href=\"profile.php?userId={$row['authorId']}\">{$row['authorName']}</a></p>
				<p class=\"commentDate\">{$row['dateTime']}</p>
				<div class=\"clear\"></div>
				<p>{$row['content']}</p>
			</div>
			";
		}
	}

}
else
{
	echo "Failed to connect, YA DONE GOOFED SON (On a scale from ok to ultragoof)";
}


?>
</div>
<div id="contentFooter"></div>
</body>
</html>