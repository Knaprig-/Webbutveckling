<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<link rel="stylesheet" type="text/css" href="styles/submitStyle.css">
	<title>Läxikon</title>
</head>
<body>
<div id="topBarContainer">
	<a href="index.php"><img id="logotype" src="grafik/logo.png" alt="Logotype" title="Main Menu"></a>

	<nav id="mainNav">
		<ul>
			<a href="index.php"><li id="summaryButton">Summaries</li></a>
			<a href="submit.php"><li class="currentPage" id="contributeButton">Contribute</li></a>
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
	if ($_SESSION['userLoggedIn'] == true) {
		
		if (!empty($_POST)) {
		
			$_POST = null;
			$title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_SPECIAL_CHARS);
			$userId = $_SESSION['userId'];
			$course = filter_input(INPUT_POST, 'course', FILTER_SANITIZE_SPECIAL_CHARS);
			$request = filter_input(INPUT_POST, 'request', FILTER_SANITIZE_SPECIAL_CHARS);
			$post = filter_input(INPUT_POST, 'post', FILTER_SANITIZE_SPECIAL_CHARS);

			if (empty($request)) {
				$request = 0;
			}
			


			if ((!empty($post)) && (!empty($userId)) && (!empty($course)) && (!empty($title))) {
				$statement = $pdo->prepare("INSERT INTO posts (id, content, authorId, dateTime, courseId, request, title) VALUES (0, :post, :userId, NOW(), :course, :request, :title)");
				$statement->bindparam(":userId", $userId);
				$statement->bindparam(":title", $title);
				$statement->bindparam(":post", $post);
				$statement->bindparam(":request", $request);
				$statement->bindparam(":course", $course);
				if ($statement->execute())
				{
					
				}
				else
				{
					print_r($statement->error_log());
				}
			}
		}


	?>

	<p class="desc">Here you can submit summaries or requests for summaries to the website.</p>

	<form id="postForm" action="submit.php" method="POST">
		<p>
			<label for="title">Title: </label>
			<input type="text" name="title"/>
		</p>
		<p>
			<label for="course">Course: </label>
			<select name="course">
			<?php
				foreach($pdo->query("SELECT * FROM courses ORDER BY courseName") as $row){
					echo "<option value=\"{$row['courseId']}\">{$row['courseName']}</option>";
				}
			?>
		</select>
		</p>
		<p>
			<input type="checkbox" name="request" value="1">
			<label for="Request">Is this a request? </label>
		</p>

		<p id="mainField">
			<label for="post">Post: </label>
			<textarea name="post"></textarea>
		</p>

		<input id="submitButton" type="submit" value="Post" />
	</form>
	<br />
	<?php
	}
	else
	{
		echo "<p class=\"errorMsg\">You need to <a href=\"login.php\">log in</a> to submit posts.</p>";
	}
}


?>
</div>
<div id="contentFooter"></div>
</body>
</html>