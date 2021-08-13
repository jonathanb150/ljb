<?php require 'definitions.php'; ?>
<?php require 'functions.php'; ?>
<?php
Class DBConnection {
	private $host = DB_HOST;
	private $user = DB_USER;
	private $pw = DB_PASS;
	private $db_name = DB_NAME;
	private $charset = DB_CHARSET;
	private $pdo;
	public $debugging = DB_DEBUGGING;
	public $last_query = false;

	public function query($query, $values) {
		$stmt = $this->pdo->prepare($query);
		try {
			if ($stmt->execute($values)) {
				$this->last_query = true;
				if (stringInVariable('select', strtolower($query)) || stringInVariable('show', strtolower($query))) {
					return $stmt->fetchAll(PDO::FETCH_ASSOC);
				} else {
					return true;
				}
			}
		}
		catch (Exception $e) {
			if (strpos($e, "SQLSTATE[HY000]: General error") !== NULL) {
				$this->last_query = true;
				return true;
			} else if ($this->debugging) {
				var_dump($e);
				return $e;
			}
			$this->last_query = false;
			return false;			
		}
		
		return false;
	}

	public function __construct() {
		try {
			$this->pdo = "mysql:host={$this->host};dbname={$this->db_name};charset={$this->charset};";
			$this->pdo = new PDO($this->pdo, $this->user, $this->pw);
			$this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			return $this->pdo;		
		} catch (Exception $e) {
			if ($this->debugging) {
				die("We're experiencing connection issues. (".$e->getMessage().")");
			} else {
				die("We're experiencing connection issues.");
			}
		}
	}

	public function __destruct() {
		$pdo = null;
	}
}

$db = new DBConnection;
?>