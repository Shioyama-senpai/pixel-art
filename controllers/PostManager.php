<?php
	require_once "controllers/DatabaseManager.php";
	require_once "controllers/UserManager.php";

	class PostManager {
		private static $instance;

		private $database;

		/**
		 * Singleton getter for PostManager.
		 */
		public static function getInstance() {
			if (!self::$instance) {
				self::$instance = new PostManager();
			}
			return self::$instance;
		}

		private function __construct() {
			$this->database = DatabaseManager::getInstance()->getDatabase();
		}

		public function getAllPosts($sorting = "most-popular") {
			$userId = UserManager::getInstance()->getLoggedInUserId(true);

			$result = $this->database->query("SELECT postid, iduser, username, title, description, imagepath, thumbnailpath, license, created_at, total_votes, total_votes_last_7_days, IFNULL(user_vote, 0) AS user_vote FROM (SELECT *, SUM(IFNULL(postvote.vote, 0)) AS total_votes, SUM(IFNULL(postvote_last_7_days.vote_7, 0)) AS total_votes_last_7_days FROM post INNER JOIN (SELECT userid, username FROM user) AS user ON user.userid = post.iduser LEFT JOIN (SELECT idpost, vote FROM postvote) AS postvote ON postvote.idpost = post.postid LEFT JOIN (SELECT idpost AS idpost_7, vote AS vote_7 FROM postvote WHERE created_at > DATE_SUB(NOW(), INTERVAL 7 DAY)) AS postvote_last_7_days ON postvote_last_7_days.idpost_7 = post.postid GROUP BY postid) AS posts_with_votes LEFT JOIN (SELECT idpost, SUM(vote) AS user_vote FROM postvote WHERE iduser = ? GROUP BY idpost) AS uservote ON uservote.idpost = posts_with_votes.postid ORDER BY " . ($sorting == "most-popular" ? "total_votes_last_7_days" : "created_at") . " DESC LIMIT 100", array($userId), array("i"));
			$resultArray = $this->database->toArray($result);
			return $resultArray;
		}

		/**
		 * Returns the data of the post with the given ID or null if that post does not exist.
		 */
		public function getPost($postId) {
			$userId = UserManager::getInstance()->getLoggedInUserId(true);

			$getPostResult = $this->database->query("SELECT postid, iduser, username, title, description, imagepath, thumbnailpath, license, created_at, total_votes, total_votes_last_7_days, IFNULL(user_vote, 0) AS user_vote FROM (SELECT *, SUM(IFNULL(postvote.vote, 0)) AS total_votes, SUM(IFNULL(postvote_last_7_days.vote_7, 0)) AS total_votes_last_7_days FROM post INNER JOIN (SELECT userid, username FROM user) AS user ON user.userid = post.iduser LEFT JOIN (SELECT idpost, vote FROM postvote) AS postvote ON postvote.idpost = post.postid LEFT JOIN (SELECT idpost AS idpost_7, vote AS vote_7 FROM postvote WHERE created_at > DATE_SUB(NOW(), INTERVAL 7 DAY)) AS postvote_last_7_days ON postvote_last_7_days.idpost_7 = post.postid GROUP BY postid) AS posts_with_votes LEFT JOIN (SELECT idpost, SUM(vote) AS user_vote FROM postvote WHERE iduser = ? GROUP BY idpost) AS uservote ON uservote.idpost = posts_with_votes.postid WHERE postid = ? LIMIT 100", array($userId, $postId), array("i", "i"));
			if ($getPostResult === false || $getPostResult->num_rows == 0) {
				return null;
			}

			return $getPostResult->fetch_assoc();
		}

		/**
		 * Creates a new post with an uploaded image.
		 * @return the post ID if the post was successfully created or an array of error strings if something went wrong.
		 */
		public function createPost() {
			$userId = UserManager::getInstance()->getLoggedInUserId(true);
			if (!$userId) {
				return "Please log in first.";
			}

			//Check if the title is set.
			if (!isset($_POST["title"]) || empty($_POST["title"])) {
				return "You must specify a title.";
			}
			$title = $_POST["title"];

			//Make sure the title is not too long.
			if (strlen($title) > 500) {
				return "The title must not be longer than 500 characters.";
			}

			//Get the description if it is set.
			$description = "";
			if (isset($_POST["description"])) {
				$description = $_POST["description"];

				if (strlen($description) > 1000) {
					return "The length of the description must not exceed 1000 characters.";
				}
			}

			//Check if the license is set and if it is one of the allowed values.
			$allowedLicenses = array("all-rights-reserved", "cc0", "cc-by", "cc-by-sa", "cc-by-nc", "cc-by-nc-sa", "cc-by-nd", "cc-by-nc-nd");
			if (!isset($_POST["license"]) || empty($_POST["license"]) || !in_array($_POST["license"], $allowedLicenses)) {
				return "You must specify a license.";
			}
			$license = $_POST["license"];

			//Check if the image file was properly uploaded.
			if (!isset($_FILES["image"])) {
				return "You must upload an image.";
			}

			//Make sure the file size does not exceed the limit.
			if (!is_uploaded_file($_FILES["image"]["tmp_name"]) || $_FILES["image"]["size"] > 50000000) { //50 MB
				return "The uploaded file is too big.";
			}

			//The file was uploaded properly. Store the temp file path in a variable.
			$tempFilePath = $_FILES["image"]["tmp_name"];

			//Make sure that the uploaded file has the required format.
			$allowedFormats = array("image/png", "image/webp");
			$fileInfo = new finfo();
			$mimeType = $fileInfo->file($tempFilePath, FILEINFO_MIME_TYPE);
			if (!in_array($mimeType, $allowedFormats)) {
				return "You must upload a PNG or a WebP file.";
			}

			//Make sure the upload directory exists.
			$uploadDirectory = "Posts" . DIRECTORY_SEPARATOR . date("Y-m-d");
			if (!file_exists($uploadDirectory)) {
				//Create the directory if it does not exist. Use 0777 as permissions and set recursive to true.
				mkdir($uploadDirectory, 0777, true);
			}

			//Move the uploaded image to the destination directory.
			$uploadPath = $uploadDirectory . DIRECTORY_SEPARATOR . microtime() . "." . strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
			if (!move_uploaded_file($tempFilePath, $uploadPath)) {
				return "The uploaded image could not be saved. Please try again.";
			}

			$thumbnailPath = $this->generateThumbnail($uploadPath);

			//Insert the post into the database.
			$result = $this->database->query("INSERT INTO post(iduser, title, description, license, imagepath, thumbnailpath) VALUES(?, ?, ?, ?, ?, ?)", array($userId, $title, $description, $license, $uploadPath, $thumbnailPath), array("i", "s", "s", "s", "s", "s"));

			if ($result !== true) {
				return "An error has occurred while writing the post to the database. " . $this->database->getError();
			}

			$postId = $this->database->getInsertedId();

			//Return the ID of the inserted row, since everything went as planned if we arrive down here.
			return $postId;
		}

		/**
		 * Generates a smaller version of an image if it is bigger than a certain size.
		 * @return the file name under which the scaled version is saved.
		 */
		private function generateThumbnail($imagePath) {
			//Determine the image type and original size.
			$imageData = getimagesize($imagePath);
			$width = $imageData[0];
			$height = $imageData[1];
			$imageType = $imageData[2];

			//If the image is already very small, the thumbnail is the image itself.
			if ($width <= 128) {
				return $imagePath;
			}

			//Open the image for editing using the determined image type.
			$image = null;
			if ($imageType == IMAGETYPE_PNG) {
				$image = imagecreatefrompng($imagePath);
			}
			else if ($imageType == IMAGETYPE_WEBP) {
				$image = imagecreatefromwebp($imagePath);
			}
			else {
				//Return the original image path because the image is not in a format we want to scale.
				return $imagePath;
			}

			imagealphablending($image, false);

			//Scale the image down.
			$scaledImage = imagescale($image, 128);

			//Build the thumbnail file name.
			$pathInfo = pathinfo($imagePath);
			$thumbnailPath = $pathInfo["dirname"] . DIRECTORY_SEPARATOR . $pathInfo["filename"] . " (Thumbnail)." . $pathInfo["extension"];

			imagesavealpha($scaledImage, true);

			//Save the image with the previous type.
			if ($imageType == IMAGETYPE_PNG) {
				imagepng($scaledImage, $thumbnailPath);
			}
			else if ($imageType == IMAGETYPE_WEBP) {
				imagewebp($scaledImage, $thumbnailPath, 100);
			}

			return $thumbnailPath;
		}

		public function vote($postId, $vote) {
			$userId = UserManager::getInstance()->getLoggedInUserId(true);

			if ($userId == -1) {
				return "You must be logged in to vote.";
			}

			//Check if there is already a vote by this user and remove the vote if so.
			$getVote = $this->database->query("SELECT * FROM postvote WHERE idpost = ? AND iduser = ?", array($postId, $userId), array("i", "i"));
			if ($getVote->num_rows > 0) {
				//Change the vote if the existing one is an opposite vote.
				if ($getVote->fetch_assoc()["vote"] != $vote) {
					$response = $this->database->query("UPDATE postvote SET vote = ? WHERE idpost = ? AND iduser = ?", array($vote, $postId, $userId), array("i", "i", "i"));
				}
				else {
					//Otherwise, remove the vote.
					$response = $this->database->query("DELETE FROM postvote WHERE idpost = ? AND iduser = ?", array($postId, $userId), array("i", "i"));
				}
			}
			else {
				//Insert the vote.
				$response = $this->database->query("INSERT INTO postvote(idpost, iduser, vote) VALUES(?, ?, ?)", array($postId, $userId, $vote), array("i", "i", "i"));
			}

			if ($response === false) {
				return "An error occurred.";
			}

			return true;
		}
	}
?>