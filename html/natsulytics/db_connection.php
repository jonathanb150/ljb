<?php require "definitions.php"; ?>
<?php
Class DBLytics {
	private $host = NL_DB_HOST;
	private $user = NL_DB_USER;
	private $pw = NL_DB_PASS;
	private $db_name = NL_DB_NAME;
	private $charset = NL_DB_CHARSET;
	private $pdo;

	public function query($query, $values) {
		$stmt = $this->pdo->prepare($query);
		try {
			if ($stmt->execute($values)) {
				if (strpos(strtolower($query), 'select') !== NULL || strpos(strtolower($query), 'show') !== NULL) {
					return $stmt->fetchAll(PDO::FETCH_ASSOC);
				} else {
					return true;
				}
			}
		}
		catch (Exception $e) {
			if (strpos($e, "SQLSTATE[HY000]: General error") !== NULL) {
				return true;
			}
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
			die("We're experiencing connection issues.");
		}
	}

	public function __destruct() {
		$pdo = null;
	}
}

$db_nl = new DBLytics;
?>