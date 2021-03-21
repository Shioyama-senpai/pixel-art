<?php
	require_once "controllers/UserManager.php";

	if (isset($_POST["submit"])) {
		$userData = UserManager::getInstance()->getLoggedInUser();
	}
?>

<?php require_once "blocks/header.php"; ?>

<?php if (!$userData || $userData["userId"] == -1): ?>
	<h1>Login</h1>

	<p>You don't need to sign up to use this website. Just enter a user token of your choice and remember it for the next time.</p>
	<p>The user token is a combination of your username and password. Thus, keep it secret and make sure you remember it. There is no way to recover a missing user token.</p>
	<p>If a new, unknown user token is entered, a new user profile will be created automatically.</p>

	<form method="POST">
		<input type="password" name="usertoken" placeholder="User token" required>
		<input type="submit" name="submit" value="Login">
	</form>
<?php else: ?>
	<?php UserManager::getInstance()->setSessionToken($userData["encryptedUserToken"]); ?>
	<script>window.open("latest.php", "_self");</script>
<?php endif; ?>

<?php require_once "blocks/footer.php"; ?>