<?php
	ini_set('display_errors', '1');
	ini_set('display_startup_errors', '1');
	error_reporting(E_ALL);
?>

<?php require_once "blocks/header.php"; ?>
<?php require_once "controllers/PostManager.php"; ?>

<h1>Share your creation</h1>

<?php
	$userId = UserManager::getInstance()->getLoggedInUserId();

	$creationResult = null;
	if (isset($_POST["submit"])) {
		$creationResult = PostManager::getInstance()->createPost();
	}
?>

<?php if ($userId != -1): ?>
	<?php if (!is_numeric($creationResult)): ?>

	<form enctype="multipart/form-data" class="upload-form" method="POST">
		<input type="hidden" name="MAX_FILE_SIZE" value="50000000">
		<p>The maximum file size is 50 MB. Only PNG and WebP are allowed.</p>
		<input type="file" name="image" required onchange="Validator.validate(event);">
		<br>
		<input type="text" name="title" placeholder="Title" required maxlength="500" value="<?php if (isset($_POST["title"])) { echo $_POST["title"]; } ?>">
		<br>
		<textarea name="description" placeholder="Description ..." maxlength="500"><?php if (isset($_POST["description"])) { echo $_POST["description"]; } ?></textarea>
		<br>

		<?php
			$selectedLicense = "";
			if (isset($_POST["license"])) {
				$selectedLicense = $_POST["license"];
			}
		?>
		<select name="license" required>
			<option value="all-rights-reserved"<?php if ($selectedLicense == "all-rights-reserved") { echo " selected"; } ?>>All rights reserved</option>
			<option value="cc-by-nc-nd"<?php if ($selectedLicense == "cc-by-nc-nd") { echo " selected"; } ?>>CC BY-NC-ND</option>
			<option value="cc-by-nd"<?php if ($selectedLicense == "cc-by-nd") { echo " selected"; } ?>>CC BY-ND</option>
			<option value="cc-by-nc-sa"<?php if ($selectedLicense == "cc-by-nc-sa") { echo " selected"; } ?>>CC BY-NC-SA</option>
			<option value="cc-by-nc"<?php if ($selectedLicense == "cc-by-nc") { echo " selected"; } ?>>CC BY-NC</option>
			<option value="cc-by-sa"<?php if ($selectedLicense == "cc-by-sa") { echo " selected"; } ?>>CC BY-SA</option>
			<option value="cc-by"<?php if ($selectedLicense == "cc-by") { echo " selected"; } ?>>CC BY</option>
			<option value="cc0"<?php if ($selectedLicense == "cc0") { echo " selected"; } ?>>CC0 / Public Domain</option>
		</select>
		<br>
		<input type="submit" name="submit" value="Publish!">
	</form>

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
<?php else: ?>
	<p align="center">You must be logged in to create a post.</p>
	<p align="center">
		<a href="login.php" class="button">Log in</a>
	</p>
<?php endif; ?>

<?php require_once "blocks/footer.php"; ?>