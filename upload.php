<?php
	ini_set('display_errors', '1');
	ini_set('display_startup_errors', '1');
	error_reporting(E_ALL);
?>

<?php require_once "blocks/header.php"; ?>
<?php require_once "controllers/PostManager.php"; ?>

<h1>Share your creation</h1>

<?php
	$creationResult = null;
	if (isset($_POST["submit"])) {
		$creationResult = PostManager::getInstance()->createPost();
	}
?>

<?php if (!is_numeric($creationResult)): ?>

<form enctype="multipart/form-data" class="upload-form" method="POST">
	<input type="file" name="image">
	<input type="text" name="title" placeholder="Title" value="<?php if (isset($_POST["title"])) { echo $_POST["title"]; } ?>">
	<textarea name="description" placeholder="Description ..."><?php if (isset($_POST["description"])) { echo $_POST["description"]; } ?></textarea>

	<?php
		$selectedLicense = "";
		if (isset($_POST["license"])) {
			$selectedLicense = $_POST["license"];
		}
	?>
	<select name="license">
		<option value="all-rights-reserved"<?php if ($selectedLicense == "all-rights-reserved") { echo " selected"; } ?>>All rights reserved</option>
		<option value="cc-by-nc-nd"<?php if ($selectedLicense == "cc-by-nc-nd") { echo " selected"; } ?>>CC BY-NC-ND</option>
		<option value="cc-by-nd"<?php if ($selectedLicense == "cc-by-nd") { echo " selected"; } ?>>CC BY-ND</option>
		<option value="cc-by-nc-sa"<?php if ($selectedLicense == "cc-by-nc-sa") { echo " selected"; } ?>>CC BY-NC-SA</option>
		<option value="cc-by-nc"<?php if ($selectedLicense == "cc-by-nc") { echo " selected"; } ?>>CC BY-NC</option>
		<option value="cc-by-sa"<?php if ($selectedLicense == "cc-by-sa") { echo " selected"; } ?>>CC BY-SA</option>
		<option value="cc-by"<?php if ($selectedLicense == "cc-by") { echo " selected"; } ?>>CC BY</option>
		<option value="cc0"<?php if ($selectedLicense == "cc0") { echo " selected"; } ?>>CC0 / Public Domain</option>
	</select>
	<div class="user-token-box">
		<input type="password" name="usertoken" placeholder="Your user token" value="<?php if (isset($_COOKIE["usertoken"])) { echo $_COOKIE["usertoken"]; } ?>">
	</div>
	<input type="submit" name="submit" value="Publish!">
</form>

<script>
	function hideUserTokenField() {
		document.body.querySelector(".user-token-box").style.display = "none";
	}

	function showUserTokenField() {
		document.body.querySelector(".user-token-box").style.display = "";
	}

	if (Utilities.getCookie("sessiontoken")) {
		hideUserTokenField();

		var expirationTime = Utilities.getCookie("sessionexpirationtime");
		if (expirationTime) {
			var remainingTime = expirationTime - new Date().getTime();
			setTimeout(showUserTokenField, remainingTime);
		}
	}
</script>

<?php
	if (is_string($creationResult)) {
		echo "<p class=\"error-message\">$creationResult</p>";
	}
?>

<?php else: ?>

<?php
	if (is_numeric($creationResult)) {
		echo "<p class=\"success-message\">Your post has been created. <a href=\"post.php?id=" . $creationResult . "\">View post</a></p>";
	}
?>

<?php endif; ?>

<?php require_once "blocks/footer.php"; ?>