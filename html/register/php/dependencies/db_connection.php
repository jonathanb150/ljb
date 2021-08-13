<?php require "functions.php"; ?>
<?php 
Class DBConnection {
	private $host = "localhost";
	private $user = "ljb";
	private $pw = "GsnSdnrt^3475Sdnkfg#465";
	private $db_name = "inveltio";
	private $charset = "utf8mb4";
	private $pdo;

	public function query($query, $values) {
		$stmt = $this->pdo->prepare($query);
		try {
			if ($stmt->execute($values)) {
				if (stringInVariable('select', strtolower($query))) {
					return $stmt->fetchAll(PDO::FETCH_ASSOC);
				} else {
					return true;
				}
			}
		}
		catch (Exception $e) {
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
			//die("We're experiencing connection issues. (".$e->getMessage().")"); //For debugging
			die("We're experiencing connection issues.");
		}
	}

	public function __destruct() {
		$pdo = null;
	}
}

$db = new DBConnection;
?>