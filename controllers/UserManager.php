<?php
	require_once "controllers/DatabaseManager.php";

	class UserManager {
		private static $instance;

		private $database;

		private $userId = null;
		private $encryptedUserToken;

		/**
		 * Singleton getter for UserManager.
		 */
		public static function getInstance() {
			if (!self::$instance) {
				self::$instance = new UserManager();
			}
			return self::$instance;
		}

		private function __construct() {
			$this->database = DatabaseManager::getInstance()->getDatabase();
		}

		/**
		 * Returns the ID of the logged in user.
		 * Returns -1 if the client is not logged in.
		 * It is possible to pass true as a parameter to enter passive mode which prevents the creation of a new user.
		 */
		public function getLoggedInUser($passiveMode = false) {
			if ($this->userId !== null) {
				return array(
					"userId" => $this->userId,
					"encryptedUserToken" => $this->encryptedUserToken
				);
			}

			$userId = -1;
			$encryptedUserToken = "";
			if (isset($_POST["usertoken"])) {
				//Encrypt the provided login token and check if there is a user which has it.
				$encryptedUserToken = sha1($_POST["usertoken"] . microtime());
				$encryptedUserTokenWithoutTime = sha1($_POST["usertoken"]);
				$getUserResult = $this->database->query("SELECT userid FROM user WHERE usertoken = ? LIMIT 1", array($encryptedUserTokenWithoutTime), array("s"));

				if ($getUserResult === true || $getUserResult === false || $getUserResult->num_rows == 0) {
					//No user has been found. Create a new one if we are not in passive mode.
					if (!$passiveMode) {
						$this->database->query("INSERT INTO user(usertoken) VALUES(?)", array($encryptedUserTokenWithoutTime), array("s"));
						$userId = $this->database->getInsertedId();
					}
				}
				else {
					$userId = $getUserResult->fetch_assoc()["userid"];
				}
			}
			else if (isset($_COOKIE["sessiontoken"])) {
				//Check if there is a user with the provided token from the cookies.
				$getUserResult = $this->database->query("SELECT userid FROM user WHERE sessiontoken = ? LIMIT 1", array($_COOKIE["sessiontoken"]), array("s"));

				if ($getUserResult !== true && $getUserResult !== false) {
					$userId = $getUserResult->fetch_assoc()["userid"];

					//Make sure the token is still valid.
					if ($this->database->query("SELECT * FROM user WHERE userid = ? AND sessionexpiration > NOW() LIMIT 1", array($userId), array("i"))->num_rows == 0) {
						return null;
					}
					
					//Encrypt the session token afterwards.
					$encryptedUserToken = sha1($_COOKIE["sessiontoken"] . microtime());
				}
			}

			if ($userId == -1) {
				return null;
			}

			//Save the new session token.
			if (!isset($GLOBALS["isAction"])) {
				$this->database->query("UPDATE user SET sessiontoken = ?, sessionexpiration = DATE_ADD(NOW(), INTERVAL 5 MINUTE) WHERE userid = ?", array($encryptedUserToken, $userId), array("s", "i"));
				$this->setSessionToken($encryptedUserToken);
			}

			$this->userId = $userId;
			$this->encryptedUserToken = $encryptedUserToken;

			return array(
				"userId" => $userId,
				"encryptedUserToken" => $encryptedUserToken
			);
		}

		public function getLoggedInUserId($passiveMode = false) {
			$userData = $this->getLoggedInUser($passiveMode);
			if ($userData) {
				return $userData["userId"];
			}
			
			return -1;
		}

		public function getLoggedInUserDetails() {
			$userId = $this->getLoggedInUserId(true);

			$getUser = $this->database->query("SELECT userid, username, created_at, updated_at FROM user WHERE userid = ? LIMIT 1", array($userId), array("i"));

			if ($getUser === false || $getUser === true || $getUser->num_rows == 0) {
				return null;
			}

			return $getUser->fetch_assoc();
		}

		public function setSessionToken($encryptedUserToken) {
			//Save the session token to the cookies with a lifetime of five minutes.
			echo "<script>Utilities.setCookie(\"sessiontoken\", \"" . $encryptedUserToken . "\", 300000); Utilities.setCookie(\"sessionexpiration\", \"" . ((time() + 300) * 1000) . "\", 300000);</script>";
			$_COOKIE["sessiontoken"] = $encryptedUserToken;
		}

		public function updateUser() {
			$userId = $this->getLoggedInUserId(true);
			if ($userId == -1) {
				return "You must be logged in.";
			}

			if (!isset($_POST["username"]) || empty($_POST["username"])) {
				return "You must specify an username.";
			}
			$username = $_POST["username"];

			if (strlen($username) > 200) {
				return "Your username must not be longer than 200 characters.";
			}

			$setUsername = $this->database->query("UPDATE user SET username = ? WHERE userid = ?", array($username, $userId), array("s", "i"));

			if ($setUsername === false) {
				return "Erör.";
			}

			return true;
		}
	}
?>