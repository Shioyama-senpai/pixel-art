<?php
	namespace Database;

	class DatabaseConnection {
		private $connection;

		private $hostname;
		private $username;
		private $password;
		private $database;

		/**
		 * This is the constructor for this class.
		 * You must specify the connection details to be able to connect using the connect() function.
		 */
		public function __construct($hostname, $username, $password, $database) {
			$this->hostname = $hostname;
			$this->username = $username;
			$this->password = $password;
			$this->database = $database;
		}

		/**
		 * Attempts to establish a connection to the database using the parameters that were handed over with the constructor.
		 * @return $this on success or false on error.
		 */
		public function connect() {
			$this->connection = mysqli_connect($this->hostname, $this->username, $this->password, $this->database);

			if ($this->connection->connect_error) {
				return false;
			}

			mysqli_set_charset($this->connection, "utf8mb4");

			return true;
		}

		public function getConnectionError() {
			return $this->connection->connect_error;
		}

		public function getError() {
			return $this->connection->error;
		}

		/**
		 * This function builds and runs an SQL query.
		 * @param $template is the SQL query with question marks as placeholders.
		 * @param $parameters is an array containing the values that should replace the placeholders in the query.
		 * @param $types is an array with the same length as the $parameters array containing characters representing the data types of the parameters. See https://www.php.net/manual/de/mysqli-stmt.bind-param.php for a list of type specifiers.
		 * @return true if the query was successful but did not return any row, false if the query caused an error or a mysqli result set on success with returned data.
		 */
		public function query($template, $parameters = null, $types = null) {
			if (!$this->connection) {
				$this->connect();
			}

			if (isset($_GET["debug"])) {
				echo "Running database query: " . $template;
			}

			if ($statement = mysqli_prepare($this->connection, $template)) {
				if ($parameters != null && $types != null) {
					$a_param_type = $types;

					$a_bind_params = array();
					foreach ($parameters as $key => $parameter) {
						$a_bind_params[] = $parameter;
					}

					$a_params = array();
					 
					$param_type = '';
					$n = count($a_param_type);
					for($i = 0; $i < $n; $i++) {
						$param_type .= $a_param_type[$i];
					}
					
					$a_params[] = & $param_type;
					 
					for($i = 0; $i < $n; $i++) {
						$a_params[] = & $a_bind_params[$i];
					}

					call_user_func_array(array($statement, 'bind_param'), $a_params);
				}

				if (mysqli_stmt_execute($statement)) {
					$result = mysqli_stmt_get_result($statement);
					if (!$result && !mysqli_errno($this->connection)) {
						return true;
					}
					else {
						return $result;
					}
				}
				else {
					mysqli_stmt_close($statement);
					return false;
				}
			}
			else {
				return false;
			}
		}

		/**
		 * @return an array of associative arrays with the rows in @param $result. An empty array is returned if the result does not contain any data.
		 */
		public function toArray($result) {
			if ($result === false || $result === true) {
				return array();
			}

			$rows = array();

			while ($row = mysqli_fetch_assoc($result)) {
				$rows[] = $row;
			}

			return $rows;
		}

		/**
		 * Returns the first result of the given mysqli result.
		 * Returns null if there is no result.
		 */
		public function getFirstResult($result) {
			if ($result === false || $result === true) {
				return null;
			}

			$rows = array();

			while ($row = mysqli_fetch_assoc($result)) {
				return $row;
			}

			return null;
		}

		/**
		 * Returns the ID of the last inserted row.
		 */
		public function getInsertedId() {
			return $this->connection->insert_id;
		}
	}
?>