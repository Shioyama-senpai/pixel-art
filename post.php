<?php
	ini_set('display_errors', '1');
	ini_set('display_startup_errors', '1');
	error_reporting(E_ALL);
?>

<?php require_once "blocks/header.php"; ?>
<?php require_once "controllers/PostManager.php"; ?>

<?php
	if (!isset($_GET["id"]) || $_GET["id"] == "") {
		header("Location: latest.php");
	}

	$postId = $_GET["id"];
	
	$post = PostManager::getInstance()->getPost($postId);

	//Compile the license text.
	$licenseText = "";

	if ($post["license"] == "all-rights-reserved") {
		$licenseText = "All rights reserved";
	}
	else if ($post["license"] == "cc0") {
		$licenseText = "CC0 / Public domain";
	}
	else {
		$ccCode = "";
		if ($post["license"] == "cc-by") {
			$ccCode = "CC BY";
		}
		else if ($post["license"] == "cc-by-sa") {
			$ccCode = "CC BY-SA";
		}
		else if ($post["license"] == "cc-by-nc") {
			$ccCode = "CC BY-NC";
		}
		else if ($post["license"] == "cc-by-nc-sa") {
			$ccCode = "CC BY-NC-SA";
		}
		else if ($post["license"] == "cc-by-nd") {
			$ccCode = "CC BY-ND";
		}
		else if ($post["license"] == "cc-by-nc-nd") {
			$ccCode = "CC BY-NC-ND";
		}
		$licenseText = "Published under Creative Commons <a href=\"http://creativecommons.org/licenses/" . substr($post["license"], 3) . "/4.0/\" target=\"_blank\">" . $ccCode . "</a>";
	}
?>

<button onclick="window.history.back();">Back</button>

<h1><?php echo $post["title"]; ?></h1>

<div class="post-image-container">
	<picture>
		<source srcset="<?php echo $post["thumbnailpath"]; ?>">
		<img src="<?php echo $post["thumbnailpath"]; ?>" full-image-source="<?php echo $post["imagepath"]; ?>" class="post-image" loading="lazy" onload="LazyLoader.loadProperImage(event);" style="filter: blur(15px);">
	</picture>
	<?php require "blocks/votes.php"; ?>
</div>

<p class="author-label">Published by <?php echo $post["username"]; ?></p>

<p><?php echo $post["description"]; ?></p>

<p class="license-label"><?php echo $licenseText; ?></p>

<?php require_once "blocks/footer.php"; ?>