<?php
session_start();

$host = "localhost";
$dbname = "laxikon";
$username = "gustavgrannsjo";
$password = "";

$dsn = "mysql:host=$host;dbname=$dbname";
$attr = array(PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC);
$errorMsg = null;
$showControls = false;

$PDO = new PDO($dsn, $username, $password, $attr);
if ($PDO) {

	if (!empty($_GET)) {
		$_GET = null;
		$userId = filter_input(INPUT_GET, 'userId', FILTER_SANITIZE_SPECIAL_CHARS);
		$statement = $PDO->prepare("SELECT * FROM users WHERE id = :userId");
		$statement->bindparam(":userId", $userId);
		if ($statement->execute()) {
			while ($row = $statement->fetch()) {
				$profileName = $row['name'];
				$birthDate = $row['birthDate'];
				$registerDate = $row['regDate'];
				
				if ($row['userLevel'] == 2) {
					$userType = "Admin";
				}
				elseif ($row['userLevel'] == 1) {
					$userType = "Member";
				}
			}
		}
		if (!empty($_POST)) 
		{
			$_POST = null;
			$courseId = filter_input(INPUT_POST, 'course');


			$multiChecker = $PDO->prepare("SELECT * FROM userCourses WHERE userId = :userId AND courseId = :courseId");
			$multiChecker->bindparam(":courseId", $courseId);
			$multiChecker->bindparam(":userId", $_SESSION['userId']);
			if ($multiChecker->execute())
			{
				while ($multiChecker->fetch()) {
					$errorMsg .= "You've already entered that course.";
				}
			}
			else
			{
				$errorMsg .= $multiChecker->error_log();
			}


			$userCourseStatement = $PDO->prepare("INSERT INTO userCourses(userId, courseId) VALUES (:userId, :courseId)");
			$userCourseStatement->bindparam(":courseId", $courseId);
			$userCourseStatement->bindparam(":userId", $_SESSION['userId']);


			if (empty($errorMsg)) {
				$userCourseStatement->execute();
				array_push($_SESSION['coursePref'], $courseId);
			}

			if ($doomedCourses = filter_input(INPUT_POST, 'deletedCourse', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY))
			{
				foreach ($doomedCourses as $row) {
					$deleteStatement = $PDO->prepare("DELETE FROM usercourses WHERE usercourses.userId = :userId AND usercourses.courseId = :courseId");
					$deleteStatement->bindParam(":userId", $userId);
					$deleteStatement->bindParam(":courseId", $row);
					$deleteStatement->execute();
				}
			}
		}

		$postStatement = $PDO->prepare("SELECT * FROM posts WHERE authorId = :userId");
		$postStatement->bindparam(":userId", $userId);
		if ($postStatement->execute()) {

		}
		else
		{
			print_r($postStatement->errorInfo());
		}
		if ($_SESSION['userLoggedIn'] == true) 
		{
			if ($_SESSION['userId'] == $userId) 
			{
				$showControls = true;

				$addCoursesIds = array();
				$addCoursesNames = array();
				foreach($PDO->query("SELECT * FROM courses ORDER BY courseName") as $row)
				{
					array_push($addCoursesIds, $row['courseId']);
					array_push($addCoursesNames, $row['courseName']);
				}

				$coursePrefGetter = $PDO->prepare("SELECT usercourses.courseId, courses.courseName FROM usercourses JOIN courses ON usercourses.courseId = courses.courseId WHERE usercourses.userId = :userId");
				$coursePrefGetter->bindParam(":userId", $userId);
				if ($coursePrefGetter->execute()) 
				{
					$deleteCoursesIds = array();
					$deleteCoursesNames = array();
					while ($row = $coursePrefGetter->fetch()) 
					{
						array_push($deleteCoursesIds, $row['courseId']);
						array_push($deleteCoursesNames, $row['courseName']);
					}
				}
			}
		}
	}
}

include("profileTemplate.php");

?>