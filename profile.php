<?php require_once "blocks/header.php"; ?>

<?php
	require_once "controllers/UserManager.php";

	$userId = UserManager::getInstance()->getLoggedInUserId();

	if ($userId == -1) {
		header("Location: login.php");
		die();
	}

	$userDetails = UserManager::getInstance()->getLoggedInUserDetails();

	$result = null;
	if (isset($_POST["submit"])) {
		$result = UserManager::getInstance()->updateUser();
	}
?>

<form method="POST">
	<input type="text" name="username" required value="<?php echo $userDetails["username"]; ?>">
	<input type="submit" name="submit" value="Save">
</form>

<?php
	if (is_string($result)) {
		echo "<p class=\"error-message\">$result</p>";
	}
?>

<?php
	if ($result === true) {
		echo "<p class=\"success-message\">The changes have been saved.</p>";
	}
?>

<?php require_once "blocks/footer.php"; ?>