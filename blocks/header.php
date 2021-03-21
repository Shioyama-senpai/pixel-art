<?php
	if (preg_match('~MSIE|Internet Explorer~i', $_SERVER['HTTP_USER_AGENT']) || preg_match('~Trident/7.0(; Touch)?; rv:11.0~', $_SERVER['HTTP_USER_AGENT'])) {
		echo "<h1>This website does not support Internet Explorer. It's time to move on and install a newer browser.</h1>";
		echo "<p><a href=\"https://www.google.com/chrome/\">Get Google Chrome</a></p>";
		echo "<p><a href=\"https://www.mozilla.org/firefox/new/\">Get Mozilla Firefox</a></p>";
		die;
	}
?>

<!DOCTYPE html>
<html>
	<head>
		<title>PixelArt</title>

		<link rel="preconnect" href="https://fonts.gstatic.com"> 
		<link href="https://fonts.googleapis.com/css2?family=VT323&display=swap" rel="stylesheet">

		<link rel="preconnect" href="https://fonts.gstatic.com"> 
		<link href="https://fonts.googleapis.com/css2?family=DotGothic16&display=swap" rel="stylesheet">

		<link rel="stylesheet" type="text/css" href="stylesheets/design.css">
		<link rel="stylesheet" type="text/css" href="stylesheets/layout.css">

		<meta name="viewport" content="width=device-width, initial-scale=1.0">

		<script src="scripts/jQuery.js"></script>
		<script src="scripts/Utilities.js"></script>
		<script src="scripts/Voter.js"></script>
		<script src="scripts/LazyLoader.js"></script>
		<script src="scripts/Validator.js"></script>

		<?php
			require_once "controllers/UserManager.php";

			$userId = UserManager::getInstance()->getLoggedInUserId(true);
		?>
	</head>
	<body>
		<div class="header">
			<a href="index.php"><img src="images/PixelArt.png" class="logo"></a>
			<a href="latest.php" class="navigation-link discover">discover</a>
			<a href="upload.php" class="navigation-link share">share</a>
			<div class="header-right">
				<div class="vertical-aligner"></div>
				<?php if ($userId == -1): ?>
					<a href="login.php" class="navigation-link profile">login</a>
				<?php else: ?>
					<a href="profile.php" class="navigation-link profile">profile</a>
				<?php endif; ?>
			</div>
		</div>
		<div class="content-container">
			<div class="content">