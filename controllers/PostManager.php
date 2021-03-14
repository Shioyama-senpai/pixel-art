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

		public function getAllPosts() {
			$result = $this->database->query("SELECT postid, iduser, username, title, description, imagepath, thumbnailpath, license, post.created_at, total_votes FROM (SELECT *, SUM(IFNULL(vote, 0)) AS total_votes FROM post INNER JOIN user ON user.userid = post.iduser LEFT JOIN postvote ON postvote.idpost = post.postid GROUP BY postid) AS posts_with_votes ORDER BY created_at DESC");
			$resultArray = $this->database->toArray($result);
			return $resultArray;
		}

		/**
		 * Returns the data of the post with the given ID or null if that post does not exist.
		 */
		public function getPost($postId) {
			$userId = UserManager::getInstance()->getLoggedInUserId(true);

			$getPostResult = $this->database->query("SELECT postid, iduser, username, title, description, imagepath, thumbnailpath, license, created_at, total_votes, IFNULL(user_vote, 0) AS user_vote FROM (SELECT *, SUM(IFNULL(vote, 0)) AS total_votes FROM post INNER JOIN (SELECT userid, username FROM user) AS user ON user.userid = post.iduser LEFT JOIN (SELECT idpost, vote FROM postvote) AS postvote ON postvote.idpost = post.postid GROUP BY postid) AS posts_with_votes LEFT JOIN (SELECT idpost, SUM(vote) AS user_vote FROM postvote WHERE iduser = ? GROUP BY idpost) AS uservote ON uservote.idpost = posts_with_votes.postid WHERE postid = ? ORDER BY created_at DESC", array($userId, $postId), array("i", "i"));
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
			//Check if a user token is provided.
			if ((!isset($_POST["usertoken"]) || empty($_POST["usertoken"])) && (!isset($_SESSION["usertoken"]) || empty($_SESSION["usertoken"]))) {
				return "You must be authenticated to create a post.";
			}

			//Check the user token.
			/*$userId = -1;
			$encryptedUserToken = "";
			if (isset($_POST["usertoken"])) {
				//Encrypt the provided login token and check if there is a user which has it.
				$encryptedUserToken = sha1($_POST["usertoken"]);
				$getUserResult = $this->database->query("SELECT userid FROM user WHERE usertoken = ? LIMIT 1", array($encryptedUserToken), array("s"));

				if ($getUserResult === true || $getUserResult === false || $getUserResult->num_rows == 0) {
					//No user has been found. Create a new one.
					$this->database->query("INSERT INTO user(usertoken) VALUES(?)", array($encryptedUserToken), array("s"));
					$userId = $this->database->getInsertedId();
				}
				else {
					$userId = $getUserResult->fetch_assoc()["userid"];
				}
			}
			else if (isset($_SESSION["usertoken"])) {
				//Check if there is a user with the provided token from the cookies.
				$getUserResult = $this->database->query("SELECT userid FROM user WHERE sessiontoken = ? AND sessionexpiration > NOW() LIMIT 1", array($_SESSION["usertoken"]), array("s"));

				if ($getUserResult !== true && $getUserResult !== false) {
					$userId = $getUserResult->fetch_assoc()["userid"];
				}
				else {
					return "Please provide your user token.";
				}
				
				//Encrypt the session token afterwards.
				$encryptedUserToken = sha1($_SESSION["usertoken"]);
			}*/
			$userData = UserManager::getInstance()->getLoggedInUser();
			if (!$userData) {
				return "Please provide your user token.";
			}

			$userId = $userData["userId"];
			$encryptedUserToken = $userData["encryptedUserToken"];

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
			if (!is_uploaded_file($_FILES["image"]["tmp_name"]) || $_FILES["image"]["size"] > 2000000) { //2 MB
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

			//Update the user's session token.
			$this->database->query("UPDATE user SET sessiontoken = ?, sessionexpiration = DATE_ADD(NOW(), INTERVAL 5 MINUTE) WHERE userid = ?", array($encryptedUserToken, $userId), array("s", "i"));

			//Save the session token to the cookies with a lifetime of five minutes.
			echo "<script>Utilities.setCookie(\"sessiontoken\", \"" . $encryptedUserToken . "\", 300000); Utilities.setCookie(\"sessionexpiration\", \"" . ((time() + 300) * 1000) . "\", 300000);</script>";

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

			//Scale the image down.
			$scaledImage = imagescale($image, 128);

			//Build the thumbnail file name.
			$pathInfo = pathinfo($imagePath);
			$thumbnailPath = $pathInfo["dirname"] . DIRECTORY_SEPARATOR . $pathInfo["filename"] . " (Thumbnail)." . $pathInfo["extension"];

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

			$response = $this->database->query("INSERT INTO postvote(idpost, iduser, vote) VALUES(?, ?, ?)", array($postId, $userId, $vote), array("i", "i", "i"));

			if ($response === false) {
				return "An error occurred.";
			}

			return true;
		}
	}
?>