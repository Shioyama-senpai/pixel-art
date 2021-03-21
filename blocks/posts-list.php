<?php require_once "controllers/PostManager.php"; ?>

<div class="posts-list">
	<?php
		$allPosts = PostManager::getInstance()->getAllPosts($sorting);

		foreach ($allPosts as $post) {
			$licenseText = "";
			if ($post["license"] == "cc0") {
				$licenseText = "CC0 / Public domain";
			}
			if ($post["license"] == "cc-by") {
				$licenseText = "<a href=\"http://creativecommons.org/licenses/by/4.0/\">CC BY</a>";
			}
			if ($post["license"] == "cc-by-sa") {
				$licenseText = "<a href=\"http://creativecommons.org/licenses/by-sa/4.0/\">CC BY-SA</a>";
			}
			if ($post["license"] == "cc-by-nc") {
				$licenseText = "<a href=\"http://creativecommons.org/licenses/by-nc/4.0/\">CC BY-NC</a>";
			}
			if ($post["license"] == "cc-by-nc-sa") {
				$licenseText = "<a href=\"http://creativecommons.org/licenses/by-nc-sa/4.0/\">CC BY-NC-SA</a>";
			}
			if ($post["license"] == "cc-by-nd") {
				$licenseText = "<a href=\"http://creativecommons.org/licenses/by-nd/4.0/\">CC BY-ND</a>";
			}
			if ($post["license"] == "cc-by-nc-nd") {
				$licenseText = "<a href=\"http://creativecommons.org/licenses/by-nc-nd/4.0/\">CC BY-NC-ND</a>";
			}
			else if ($post["license"] == "all-rights-reserved") {
				$licenseText = "All rights reserved";
			}

			echo "<div class=\"post " . $displayMode . "\" id=\"" . $post["postid"] . "\">";
			echo "	<div class=\"post-image-container\">";
			echo "		<a href=\"post.php?id=" . $post["postid"] . "\">";
			echo "			<picture>";
			echo "				<source srcset=\"" . $imagePath . "\">";
			echo "				<img class=\"post-image\" src=\"" . $post["thumbnailpath"] . "\" loading=\"lazy\"" . ($displayMode == "list" ? " onload=\"LazyLoader.loadProperImage(event);\" full-image-source=\"" . $post["imagepath"] . "\" style=\"filter: blur(15px);\"" : "") . ">";
			echo "			</picture>";
			require "blocks/votes.php";
			echo "			<h3>" . $post["title"] . "</h3>";
			echo "		</a>";
			echo "		<p>Published by " . $post["username"] . " on/at " . $post["created_at"] . "</p>";
			echo "		<p>License: " . $licenseText . "</p>";
			echo "	</div>";
			echo "</div>";
		}
	?>
</div>