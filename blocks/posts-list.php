<div class="posts-list">
	<?php

		$allPosts = PostManager::getInstance()->getAllPosts();

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

			echo "<div class=\"post\" id=\"" . $post["postid"] . "\">";
			echo "	<picture>";
			echo "		<source srcset=\"" . $post["imagepath"] . "\">";
			echo "		<img src=\"" . $post["imagepath"] . "\">";
			echo "	</picture>";
			echo "	<div class=\"votes\">";
			echo "		<img src=\"images/Upvote.png\" class=\"upvote-button\">";
			echo "		<span>" . $post["total_votes"] . "</span>";
			echo "		<img src=\"images/Downvote.png\" class=\"downvote-button\">";
			echo "	</div>";
			echo "	<h3>" . $post["title"] . "</h3>";
			echo "	<p>Published by " . $post["username"] . " on/at " . $post["created_at"] . "</p>";
			echo "	<p>Licensing: " . $licenseText . "</p>";
			echo "</div>";
		}

	?>
</div>