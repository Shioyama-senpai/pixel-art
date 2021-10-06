<?php
	require_once "controllers/DatabaseConnection.php";

	use Database\DatabaseConnection;

	class DatabaseManager {
		private static $instance;

		private $database;

		/**
		 * Singleton getter for DatabaseManager.
		 */
		public static function getInstance() {
			if (!self::$instance) {
				self::$instance = new DatabaseManager();
			}
			return self::$instance;
		}

		private function __construct() {
			$this->database = new DatabaseConnection("localhost", "root", "", "pixelart");
			if (!$this->database->connect()) {
				die("Could not connect to the database.");
			}
		}

		public function getDatabase() {
			return $this->database;
		}
	}
?>