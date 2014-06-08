<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<link rel="stylesheet" type="text/css" href="styles/profileStyle.css">
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
			<a href="register.php"><li id="registerButton">Register</li></a>
		</ul>
	</nav>
</div>

<div id="contentRoof"></div>
<div id="mainContainer">
	<h1 id="userName"><?php echo $profileName; ?></h1>
	<p class="userInfo">Born: <?php echo $birthDate; ?></p>
	<p class="userInfo">Registered: <?php echo $registerDate; ?></p>
	<p class="userInfo"><?php echo $userType; ?></p>

	<?php if ($showControls){ ?>
	<div id="ownerControlsContainer">
		<h3>Owner Controls</h3>
		<?php 
			if ($errorMsg) {
				echo "<p class=\"message\">" . $errorMsg . "</p>";
			}
		?>
		<form action="profile.php?userId=<?php echo $userId; ?>" method="POST">
			<div class="formContainer">
				<p>Here you can specify which courses you attend. On the front page you will only see summaries and requests which are made for these courses.</p>
				<label for="course">Course: </label>
				<select name="course">
					<?php 
						$counter = 0;
						foreach ($addCoursesIds as $row) {
							echo "<option value=\"{$addCoursesIds[$counter]}\">{$addCoursesNames[$counter]}</option>";
							$counter++;
						}
					?>
				</select>
				<input type="submit" value="Add to preferences"/>
			</div>
		</form>


		<form action="profile.php?userId=<?php echo $userId; ?>" method="POST">
			<div class="formContainer">
				<p>Here is a list of all courses you've said you attend. If you no longer wish to see summaries for any specific course, you can remove them here.</p>
				<?php 
				$counter = 0;
				foreach ($deleteCoursesIds as $row) {
					echo "<p>
					<input type=\"checkbox\" name=\"deletedCourse[]\" value=\"{$deleteCoursesIds[$counter]}\">
					<label for=\"deletedCourse[]\"> {$deleteCoursesNames[$counter]}</label>
					</p>";
					$counter++;
				}

				?>
				<input type="submit" value="Remove selected courses from preferences"/>
			</div>
		</form>
	</div>
	<?php } ?>
</div>
<div id="contentFooter"></div>
</body>
</html>