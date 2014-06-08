<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<link rel="stylesheet" type="text/css" href="styles/loginStyle.css">
	<title>Läxikon</title>
</head>
<body>
<div id="topBarContainer">
	<a href="index.php"><img id="logotype" src="grafik/logo.png" alt="Logotype" title="Main Menu"></a>

	<nav id="mainNav">
		<ul>
			<a href="index.php"><li id="summaryButton">Summaries</li></a>
			<a href="submit.php"><li  id="contributeButton">Contribute</li></a>
			<a href="login.php"><li class="currentPage" id="loginButton">Login</li></a>
			<a href="register.php"><li id="registerButton">Register</li></a>
		</ul>
	</nav>
</div>
<div id="contentRoof"></div>
<div id="mainContainer">
<?php
session_start();
$_SESSION["userName"] =  "";
$_SESSION["userLoggedIn"] = false;
$_SESSION['userId'] = "";
$_SESSION['coursePref'] = array();

$host = "localhost";
$dbname = "laxikon";
$username = "gustavgrannsjo";
$password = "";

$loginErrorMsg="";


$dsn = "mysql:host=$host;dbname=$dbname";
$attr = array(PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC);


$PDO = new PDO($dsn, $username, $password, $attr);

if ($PDO){
	if (!empty($_POST)) {
		$_POST 	= null;
		$logName = filter_input(INPUT_POST, 'nameField', FILTER_SANITIZE_SPECIAL_CHARS);
		$logPass = filter_input(INPUT_POST, 'passField', FILTER_SANITIZE_EMAIL);

		$userStatement = $PDO->prepare('SELECT * from users WHERE name=:logName');
		$userStatement->bindparam(":logName", $logName);

		if ($userStatement->execute()) {

			$checker = false;

			while ($row = $userStatement->fetch()) {
				$checker = true;
				$hashedLogPass = hash('sha512', $logPass . $row['salt']);

				if ($hashedLogPass == $row['password']) {

					echo "<p class=\"message\">Login successful! Click <a href=\"profile.php?userId={$row['id']}\">here</a> to visit your profile page.</p>";
					$_SESSION["userName"] =  $row['name'];
					$_SESSION["userLoggedIn"] = true;
					$_SESSION['userId'] = $row['id'];
					$_SESSION['coursePref'] = array();

					$courseGetter = $PDO->prepare("SELECT * FROM usercourses WHERE userId = :userId");
					$courseGetter->bindparam("userId", $_SESSION['userId']);
					if ($courseGetter->execute()) 
					{
						while ($row = $courseGetter->fetch()) 
						{
							array_push($_SESSION['coursePref'], $row['courseId']);
						}
					}
					 
				}
				else{
					echo "<p class=\"message\">Incorrect password.</p>";
				}
			}

			if ($checker == false) {
				echo "<p class=\"message\">Invalid username.</p>";
			}
		}
		else{
			print_r($userStatement->error_log());
			echo "<p class=\"message\">Error has occured, error code 1, it might mean something to someone</p>";
		}
	}

	if ($_SESSION['userLoggedIn'] == false) {

	?>
	<div id="formContainer">
	<form id="loginForm" action="login.php" method="POST">
		<p>
			<label for="nameField">Username:</label>
			<input type="text" name="nameField">
		</p>
		<p>
			<label for="passwordField">Password:</label>
			<input type="password" name="passField">
		</p>
		<p><input type="submit" value="Login"></p>
	</form>
	</div>

	<div id="helpText">
		<p>Haven't got an account yet? Click <a href="register.php">here</a> to register.</p>
		<p>In the future, this page will serve as a logout page if you're logged in. Simply visit this page and you'll be logged out.</p>
	</div>
	<?php
	}


}
?>
</div>
<div id="contentFooter"></div>
</body>
</html>