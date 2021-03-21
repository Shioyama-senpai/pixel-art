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

		<script src="scripts/jQuery.js"></script>
		<script src="scripts/Utilities.js"></script>
		<script src="scripts/Voter.js"></script>
		<script src="scripts/LazyLoader.js"></script>

		<?php
			require_once "controllers/UserManager.php";

			$userId = UserManager::getInstance()->getLoggedInUserId(true);
		?>
	</head>
	<body>
		<div class="header">
			<a href="index.php"><img src="images/PixelArt.png" class="logo"></a>
			<a href="upload.php" class="navigation-link">share</a>
			<a href="latest.php" class="navigation-link">explore</a>
			<div class="header-right">
				<div class="vertical-aligner"></div>
				<?php if ($userId == -1): ?>
					<a href="login.php" class="navigation-link">login</a>
				<?php else: ?>
					<a href="profile.php" class="navigation-link">profile</a>
				<?php endif; ?>
			</div>
		</div>
		<div class="content-container">
			<div class="content">