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
				$encryptedUserToken = sha1($_POST["usertoken"]);
				$getUserResult = $this->database->query("SELECT userid FROM user WHERE usertoken = ? LIMIT 1", array($encryptedUserToken), array("s"));

				if ($getUserResult === true || $getUserResult === false || $getUserResult->num_rows == 0) {
					//No user has been found. Create a new one if we are not in passive mode.
					if (!$passiveMode) {
						$this->database->query("INSERT INTO user(usertoken) VALUES(?)", array($encryptedUserToken), array("s"));
						$userId = $this->database->getInsertedId();
					}
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
					
					//Encrypt the session token afterwards.
					$encryptedUserToken = sha1($_SESSION["usertoken"]);
				}
			}

			if ($userId == -1) {
				return null;
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
	}
?>