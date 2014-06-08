<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<link rel="stylesheet" type="text/css" href="styles/registerStyle.css">
	<title>Läxikon</title>
</head>
<body>
<div id="topBarContainer">
	<a href="index.php"><img id="logotype" src="grafik/logo.png" alt="Logotype" title="Main Menu"></a>

	<nav id="mainNav">
		<ul>
			<a href="index.php"><li id="summaryButton">Summaries</li></a>
			<a href="submit.php"><li  id="contributeButton">Contribute</li></a>
			<a href="login.php"><li  id="loginButton">Login</li></a>
			<a href="register.php"><li class="currentPage" id="registerButton">Register</li></a>
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
$errorMsg="";


$userName = "";
$birthDate = "";

$dsn = "mysql:host=$host;dbname=$dbname";
$attr = array(PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC);
$showForm = true;

$pdo = new PDO($dsn, $username, $password, $attr);

if($pdo){

	if (!empty($_POST)) {
		$prePass = $_POST['passwordOne'];
		$preName = $_POST['userName'];
		$_POST = null;
		$birthDate = filter_input(INPUT_POST, 'birthDate', FILTER_SANITIZE_SPECIAL_CHARS);
		$userName = filter_input(INPUT_POST, 'userName', FILTER_SANITIZE_SPECIAL_CHARS);
		$passwordOne = filter_input(INPUT_POST, 'passwordOne', FILTER_SANITIZE_EMAIL);
		$passwordTwo = filter_input(INPUT_POST, 'passwordTwo', FILTER_SANITIZE_EMAIL);
		
		$userList = $pdo->prepare("SELECT name FROM users");
		if ($userList->execute()) {
			while ($row = $userList->fetch()) {
				$stringOne = strtolower($row['name']);
				$stringTwo = strtolower($userName);
				if (strtolower($userName) == strtolower($row['name'])) {
					$errorMsg = $errorMsg . "<p>That username is already in use.</p>";
					break;
				}
			}
		}

		if (($prePass == $passwordOne) && ($prePass == $passwordTwo)){
			# code...
		}
		else{
			$errorMsg = $errorMsg. "<p>Your passwords did not match or you used illegal characters</p>";
		}	

		if (!($preName == $userName)) {
			$errorMsg = $errorMsg. "<p>Unacceptable characters used.</p>";
		}
		if (strlen($passwordOne) >= 20) {
			$errorMsg= $errorMsg. "<p>Password too long.</p>";
		}
		if (strlen($preName) >= 16){
			$errorMsg = $errorMsg. "<p>Username too long.</p>";
		}
		if (strlen($preName) <= 3){
			$errorMsg = $errorMsg. "<p>Username too short.</p>";
		}
		if (strlen($prePass) <= 4){
			$errorMsg .= "<p>Password too short.</p>";
		}
		if(!($birthDate)){
			$errorMsg = $errorMsg. "<p>Enter your date of birth.</p>";
		}
		if ($birthDate > date('Y/m/d')) {
			$errorMsg = $errorMsg. "<p>Invalid date of birth.</p>";
		}
		if (!$errorMsg) {
			$showForm = false;

			$saltSize = mcrypt_get_iv_size(MCRYPT_CAST_256, MCRYPT_MODE_CFB);
			$salt = mcrypt_create_iv($saltSize, MCRYPT_DEV_RANDOM);
			$fullPass = $passwordOne. $salt;
			$hashedPass = hash('sha512', $fullPass);

			$statement = $pdo->prepare("INSERT INTO users(id, name, password, regDate, userLevel, salt, birthDate) VALUES(0, :userName, :hashedPass, CURDATE(), 1, :salt, :birthDate)");
			$statement->bindparam(":userName", $userName);
			$statement->bindparam(":hashedPass", $hashedPass);
			$statement->bindparam(":salt", $salt);
			$statement->bindparam(":birthDate", $birthDate);

			if($statement->execute())
			{
				
			}
			else
			{
				echo "Nu blev det fel.";
				print_r($statement->errorInfo());
			}
		}
		

	}

	if ($showForm == true) {
			if ($errorMsg) {
				echo "<div class=\"messages\">" . $errorMsg . "</div>";
			}
			?>

			<div id="formContainer">
				<p>Here you can register an account for this website!</p>
				<form id="registrationForm" action="register.php" method="POST">
					<p>
						<label for="userName">Your Username: </label>
						<input type="text" name="userName" value="<?php echo $userName?>">
					</p>

					<p class="passField">
						<label for="password">Your Password: </label>
						<input type="password" name="passwordOne"><p></p>
					</p>
					<p id="passHelpText">(All letters, digits and !#$%&'*+-/=?^_`{|}~@.[]. are accepted, and it must be longer than 4 characters)</p>
					<p class="passField">
						<label for="password">Repeat your password: </label>
						<input type="password" name="passwordTwo">
					</p>
					
					<p>
						<label for="birthDate">Birthdate: </label>
						<input type="date" name="birthDate" value="<?php echo $birthDate?>">
						(YYYY-MM-DD)
					</p>
					<input type="submit" value="Submit" />
				</form>
			</div>
			<?php
			
		}
	


}
?>
</div>
<div id="contentFooter"></div>
</body>
</html>