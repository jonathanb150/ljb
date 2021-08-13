<?php require "db_operation.php"; ?>
<?php 
session_start();

Class NewUser extends DBOperation {
	//Registration rules
	public $error = "";
	private $form_length = 21;
	private $min_password_length = 8;   
	private $max_password_length = 50; 
	private $country_list = ["United States"];
	private $state_list = ['WY', 'WI', 'WV', 'WA', 'VA', 'VT', 'UT', 'TX', 'TN', 'SD', 'SC', 'RI', 'PA', 'OR', 'OK', 'OH', 'ND', 'NC', 'NY', 'NM', 'NJ', 'NH', 'NV', 'NE', 'MT', 'MO', 'MS', 'MN', 'MI', 'MA', 'MD', 'ME', 'LA', 'KY', 'KS', 'IA', 'IN', 'IL', 'ID', 'HI', 'GA', 'FL', 'DC', 'DE', 'CT', 'CO', 'CA', 'AR', 'AZ', 'AK', 'AL'];
	private $sex_list = ["Male", "Female", "Other"];
	private $find_list = ["Internet", "Referred", "Others"];
	private $security_q1_list = ['What time of the day was your first child born? (hh:mm)', 'What time of the day were you born? (hh:mm)', 'In what town or city did your mother and father meet?', 'What is your spouse or partner\'s mother\'s maiden name?', 'What is your grandmother\'s (on your mother\'s side) maiden name?', 'What are the last five digits of your driver\'s licence number?', 'What is the middle name of your oldest child?', 'In what town or city did you meet your spouse/partner?', 'In what town or city was your first full time job?', 'What primary school did you attend?', 'What were the last four digits of your childhood telephone number?', 'What was the house number and street name you lived in as a child?'];
	private $security_q2_list = ['What time of the day was your second child born? (hh:mm)', 'What is your grandmother\'s (on your father\'s side) maiden name?', 'What are the first five digits of your driver\'s licence number?', 'What is the middle name of your youngest child?', 'What high school did you attend?', 'What were the first four digits of your childhood telephone number?', 'What\'s your favorite ice cream flavor?', 'What was the model of your first vehicle?'];

	//Functions
	private function getLastUID() {
		$last_uid = $this->query("SELECT uid FROM users ORDER BY uid DESC LIMIT 1", array());

		if(is_array($last_uid) && count($last_uid) == 1) {
			return $last_uid[0]['uid'];
		}

		return 0;
	}

	private function duplicateEmail($email) {
		$found_mail = $this->query("SELECT email FROM users WHERE email = ?", array($email));

		if(is_array($found_mail) && count($found_mail) == 1) {
			return true;
		}

		return false;
	}

	private function verifyCaptcha($captcha) {
		return true; //DISABLE CAPTCHA

		if(!empty($captcha)) {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, "https://www.google.com/recaptcha/api/siteverify");
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, "secret=6LeysZkUAAAAAKevV6kXPdzbwajRULbKJAQuDBv4&response=".$captcha);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			
			$html = curl_exec($ch);
			$html = json_decode($html, true);

			if(is_array($html) && count($html) > 0 && isset($html['success']) && $html['success']) {
				return true;
			}
		}

		return "Invalid captcha.";
	}

	private function createUserTables() {
		$uid = $this->getLastUID();
		$this->query("CREATE TABLE `{$uid}_cash` (
		  `date` date NOT NULL,
		  `cash` double NOT NULL,
		  PRIMARY KEY (`date`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;", array());
		$this->query("CREATE TABLE `{$uid}_cash_history` (
		  `id` int(11) NOT NULL,
		  `date` date NOT NULL,
		  `cash` double NOT NULL,
		  `verified` int(1) NOT NULL DEFAULT '0',
		  `credited` int(1) NOT NULL DEFAULT '0',
		  PRIMARY KEY (`id`),
		  KEY `date` (`date`),
		  KEY `verified` (`verified`),
		  KEY `credited` (`credited`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;", array());
	}

	public static function activateUser($activation_hash) {
		global $db;
		$verify_hash = $db->query("SELECT uid, activated FROM users WHERE activation_hash = ?",array($activation_hash));
		if(is_array($verify_hash) && count($verify_hash) == 1) {
			if($verify_hash[0]['activated'] == 0) {
				if($db->query("UPDATE users SET activated = ?, activated_date = ? WHERE uid = ?", array(1, time(), $verify_hash[0]['uid']))) {
					return (int) $verify_hash[0]['uid'];
				}
			}
			else {
				return "This account has already been activated.";
			}
		}

		return "There was an error activating your account. Please check that you provided a valid URL.";
	}

	//Accepts array with registration values
	public function __construct($user_input) {
		DBConnection::__construct();

		$user_input = json_decode($user_input);

		if(checkArray($user_input) && count($user_input) == $this->form_length) {
			$mail = trim($user_input[0]);
			$cmail = trim($user_input[1]);
			$password = $user_input[2];
			$cpassword = $user_input[3];
			$name = trim($user_input[4]);
			$lname = trim($user_input[5]);
			$dob = trim($user_input[6]);
			$sex = trim($user_input[7]);
			$country = trim($user_input[8]);
			$state = trim($user_input[9]);
			$address = trim($user_input[10]);
			$phone = trim($user_input[11]);
			$zipcode = trim($user_input[12]);
			$security_q1 = trim($user_input[13]);
			$security_a1 = trim($user_input[14]);
			$security_q2 = trim($user_input[15]);
			$security_a2 = trim($user_input[16]);
			$find = trim($user_input[17]);
			$terms = $user_input[18];
			$news = $user_input[19];
			$captcha = $user_input[20];

			//Verify inputs
			$verification = [];
			for ($i=0; $i < count($user_input); $i++) { 
				$verification[] = true;
			}

			//Verify email
			if(verifyEmail($mail) !== true) {
				$verification[0] = verifyEmail($mail);
			}
			else if($this->duplicateEmail($mail)) {
				$verification[0] = "Email address already taken.";
			}
			if($mail != $cmail) {
				$verification[1] = "Email addresses don't match.";
			}

			//Verify password
			$verification[2] = verifyPassword($password, $this->min_password_length, $this->max_password_length);
			
			if($password !== $cpassword) {
				$verification[3] = "Passwords don't match.";
			}

			//Verify name
			if(!verifyName($name)) {
				$verification[4] = "Please provide a valid name.";
			}
			if(!verifyName($lname)) {
				$verification[5] = "Please provide a valid last name.";
			}

			//Verify dob
			$verification[6] = verifyDOB($dob);
			
			//Verify sex
			if(!in_array($sex, $this->sex_list)) {
				$verification[7] = "Please provide a valid sex.";
			}

			//Verify country and state
			if(!in_array($country, $this->country_list)) {
				$verification[8] = "Please select your country.";
			}
			if(!in_array($state, $this->state_list)) {
				$verification[9] = "Please provide a valid state.";
			}

			//Verify address, phone number and zip code
			$verification[10] = verifyAddress($address);
			if(empty($phone) || !ctype_digit($phone)) {
				$verification[11] = "Please provide a valid phone number.";
			}
			$verification[12] = verifyUSZipCode($zipcode);

			//Verify security questions and answers
			if(!in_array($security_q1, $this->security_q1_list)) {
				$verification[13] = "Please select one of the options.";
			}
			if(empty($security_a1) || strlen($security_a1) > 255) {
				$verification[14] = "Please provide a valid answer.";
			}
			if(!in_array($security_q2, $this->security_q2_list)) {
				$verification[15] = "Please select one of the options.";
			}
			if(empty($security_a2) || strlen($security_a2) > 255) {
				$verification[16] = "Please provide a valid answer.";
			}

			//Verify "How did you find us?"
			if(!in_array($find, $this->find_list)) {
				$verification[17] = "Please select one of the options.";
			}

			//Verify terms of service
			if(!$terms) {
				$verification[18] = "You must accept the terms.";
			}

			//Verify captcha
			$verification[20] = $this->verifyCaptcha($captcha);

			//Verify registration
			if(count(array_unique($verification)) == 1) {
				$news = ($news ? 1 : 0); 
				$security_1 = json_encode(array($security_q1, password_hash($security_a1, PASSWORD_DEFAULT)));
				$security_2 = json_encode(array($security_q2, password_hash($security_a2, PASSWORD_DEFAULT)));
				$uid = $this->getLastUID()+1;

				//INSERT NEW USER
				$insert_user = $this->query("INSERT INTO users (uid, password, email, activation_hash, creation_ip, last_ip, creation_date, last_login, name, last_name, dob, sex, country, state, address, phone, zip_code, security_1, security_2, referred_by, newsletter) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)", array($uid, password_hash($password, PASSWORD_DEFAULT), $mail, password_hash(($mail.$name.$lname.$password), PASSWORD_DEFAULT), $_SERVER['REMOTE_ADDR'], $_SERVER['REMOTE_ADDR'], time(), time(), $name, $lname, $dob, $sex, $country, $state, $address, $phone, $zipcode, $security_1, $security_2, $find, $news));

				if(!$insert_user) {
					$this->error = "0";
				}
				else { 
					$this->createUserTables();
				}
			}
			else{
				$this->error = json_encode($verification);
			}
		}
		else {
			$this->error = "0";
		}
	}

	public function __destruct() {
		DBConnection::__destruct();
	} 
}

Class User extends DBOperation {
	//Login variables
	public $error = "";
	private $form_length = 3;

	private function verifyLogin($mail, $password) {
		$verify_login = $this->query("SELECT uid, password FROM users WHERE email = ?", array($mail));

		if(is_array($verify_login) && count($verify_login) == 1 && password_verify($password, $verify_login[0]['password'])) {
			return $verify_login[0]['uid'];
		}

		return false;
	}

	private function lastDateIP($uid) {
		$this->query("UPDATE users SET last_ip = ?, last_login = ? WHERE uid = ?", array($_SERVER['REMOTE_ADDR'], time(), $uid));
	}

	private function isActivated($uid) {
		$query = $this->query("SELECT activated FROM users WHERE uid = ?", array($uid));

		if(isset($query[0]['activated']) && $query[0]['activated'] == 1) {
			return true;
		}

		return false;
	}

	public function __construct($user_input) {
		DBConnection::__construct();

		$user_input = json_decode($user_input);

		if(checkArray($user_input) && count($user_input) == $this->form_length) {
			$mail = trim($user_input[0]);
			$password = $user_input[1];
			$cookie = $user_input[2];

			$uid = $this->verifyLogin($mail, $password);

			if($uid && is_numeric($uid)) {
				$this->lastDateIP($uid);

				if(!$this->isActivated($uid)) {
					$this->error = "Activation required.";
				}
				else {
					$_SESSION['account_setup'] = $uid;
				}
			}
			else {
				$this->error = "error";
			}
		}
		else {
			$this->error = "error";
		}
	}

	public function __destruct() {
		DBConnection::__destruct();
	} 
}

Class AccountSetup extends DBOperation {
	//AccountSetup variables
	public $error = "";
	public $settings_status = "";
	private $user_questions = [];
	private $account_type = null;
	private $current_question = [];
	private $questions = [["Choose your account type", "account_type"], ["What is your net worth?", "net_worth"], ["What is the net worth of your business?", "net_worth"], ["What is your annual income?", "annual_income"], ["What is the annual income of your business?", "annual_income"], ["Social Security Number", "ssn"], ["Employer Identification Number", "ein"], ["Investing Experience (Equites)", "investing_exp_equites"], ["Investing Experience (Bonds)", "investing_exp_bonds"], ["Investing Experience (Forex)", "investing_exp_forex"], ["Investing Experience (Others)", "investing_exp_others"], ["Enter your bank account information", "bank_information"], ["What are your investment plans?", "investing_purpose"], ["Please select how much risk you're willing to take", "risk_profile"], ["Where would you like to allocate your assets?", "asset_allocation"]];
	private $personal_order = ["net_worth", "annual_income", "ssn", "investing_exp_equites", "investing_exp_bonds", "investing_exp_forex", "investing_exp_others", "bank_information", "investing_purpose", "risk_profile", "asset_allocation"];
	private $corporate_order = ["net_worth", "annual_income", "ein", "investing_exp_equites", "investing_exp_bonds", "investing_exp_forex", "investing_exp_others", "bank_information", "investing_purpose", "risk_profile", "asset_allocation"];

	private function verifyUid($uid) {
		$query = $this->query("SELECT uid FROM users WHERE uid = ?", array($uid));
		
		if(is_array($query) && count($query) == 1) {
			$check_exists = $this->query("SELECT uid FROM account_setup WHERE uid = ?", array($uid));

			if(is_array($check_exists) && count($check_exists) == 0) {
				$this->query("INSERT INTO account_setup (uid) VALUES (?)", array($uid));
			}

			$get_user_questions = $this->query("SELECT * FROM account_setup WHERE uid = ?", array($uid));

			if(is_array($get_user_questions) && count($get_user_questions) == 1) {
				$question_keys = array_keys($get_user_questions[0]);
				$question_values = array_values($get_user_questions[0]);

				for ($i=0; $i < count($get_user_questions[0]); $i++) { 
					if($get_user_questions[0][$question_keys[$i]] == null) {
						$this->user_questions[] = $question_keys[$i];
					}
					else if($question_keys[$i] == "account_type") {
						if($question_values[$i] == "Personal") {
							$this->account_type = "Personal";
						}
						else {
							$this->account_type = "Corporate";
						}
					}
				}
			}

			return true;
		}

		return false;
	}

	private function getCurrentQuestion() {
		if($this->account_type == null) {
			return "account_type";
		}
		else if($this->account_type == "Personal") {
			for ($i=0; $i < count($this->personal_order); $i++) { 
				if(in_array($this->personal_order[$i], $this->user_questions)) {
					return $this->personal_order[$i];
				}
			}
		}
		else if($this->account_type == "Corporate") {
			for ($i=0; $i < count($this->corporate_order); $i++) { 
				if(in_array($this->corporate_order[$i], $this->user_questions)) {
					return $this->corporate_order[$i];
				}
			}
		}

		return false;
	}

	private function validQuestion($question) {
		$current_question = $this->getCurrentQuestion();
		$possible_questions = [];

		for ($i=0; $i < count($this->questions); $i++) { 
			$possible_questions[] = $this->questions[$i][1];

			if($this->questions[$i][1] == $current_question) {
				break;
			}
		}

		for ($i=0; $i < count($this->questions); $i++) { 
			if($this->questions[$i][0] == $question && in_array($current_question, $possible_questions)) {
				$this->current_question[] = $this->questions[$i][0];
				$this->current_question[] = $this->questions[$i][1];
				return true;
			}
		}

		return false;
	}

	private function setupComplete($uid) {
		$this->query("UPDATE users SET settings_completed = ?, settings_completed_date = ? WHERE uid = ?", array(1, time(), $uid));
		unset($_SESSION['account_setup']);
	}

	public static function hasCompletedSetup($uid) {
		global $db;
		$query = $db->query("SELECT settings_completed FROM users WHERE uid = ?", array($uid));

		if(isset($query[0]['settings_completed']) && $query[0]['settings_completed'] == 1) {
			return true;
		}

		return false;
	}

	public function __construct($question, $answer, $inputs, $uid, $status) {
		DBConnection::__construct();

		if($this->verifyUid($uid)) {
			if($question == null && $answer == null && $inputs == null && $status != null) {
				$this->query("UPDATE users SET settings_status = ? WHERE uid = ?", array(json_encode($status, true), $uid));
				die();
			}
			else if($question == null && $answer == null && $inputs == null && $status == null) {
				$get_settings_status = $this->query("SELECT settings_status FROM users WHERE uid = ?", array($uid));

				if(is_array($get_settings_status) && count($get_settings_status) == 1) {
					$this->settings_status = $get_settings_status[0]['settings_status'];
				}
			}

			$inputs = json_decode($inputs, true);

			if($this->validQuestion($question) && is_array($inputs)) {
				switch($this->current_question[1]) {
					case "account_type":
						$options = ["Personal", "Corporate"];

						if(in_array($answer, $options) && count($inputs) == 0 && 
							$this->query("UPDATE account_setup SET {$this->current_question[1]} = ? WHERE uid = ?", array($answer, $uid))) {
							break;
						}
						else {
							$this->error = "Please select a valid option.";
							break;
						}
					case "net_worth":
						$options = ["$10,000 - $20,000", "$20,000 - $50,000", "$50,000 - $100,000", "$100,000 - $200,000", "More than $200,000", "$200,000 - $500,000", "$500,000 - $1,000,000", "$1,000,000 - $2,000,000", "More than $2,000,000"];

						if(in_array($answer, $options) && count($inputs) == 0 && 
							$this->query("UPDATE account_setup SET {$this->current_question[1]} = ? WHERE uid = ?", array($answer, $uid))) {
							break;
						}
						else {
							$this->error = "Please select a valid option.";
							break;
						}
					case "annual_income":
						$options = ["Less than $10,000", "$10,000 - $30,000", "$30,000 - $60,000", "$60,000 - $100,000", "$100,000 - $150,000", "More than $150,000", "Less than $100,000", "$100,000 - $300,000", "$300,000 - $600,000", "$600,000 - $1,000,000", "$1,000,000 - $1,500,000", "More than $1,500,000"];

						if(in_array($answer, $options) && count($inputs) == 0 && 
							$this->query("UPDATE account_setup SET {$this->current_question[1]} = ? WHERE uid = ?", array($answer, $uid))) {
							break;
						}
						else {
							$this->error = "Please select a valid option.";
							break;
						}
					case "ssn":
						if(isset($inputs['ssn']) && strlen($answer) == 0 && strlen($inputs['ssn']) == 9 && ctype_digit($inputs['ssn']) &&
							$this->query("UPDATE account_setup SET {$this->current_question[1]} = ? WHERE uid = ?", array($inputs['ssn'], $uid))) {
							break;
						}
						else if(strlen($inputs['ssn']) == 0) {
							$this->error = "Your SSN is required.";
							break;
						}
						else if(!ctype_digit($inputs['ssn'])) {
							$this->error = "Invalid characters detected.";
							break;
						}
						else if(strlen($inputs['ssn']) < 9) {
							$this->error = "That is not a valid SSN.";
							break;
						}
					case "ein":
						if(isset($inputs['ein']) && strlen($answer) == 0 && strlen($inputs['ein']) == 9 && ctype_digit($inputs['ein']) &&
							$this->query("UPDATE account_setup SET {$this->current_question[1]} = ? WHERE uid = ?", array($inputs['ein'], $uid))) {
							break;
						}
						else if(strlen($inputs['ein']) == 0) {
							$this->error = "Your EIN is required.";
							break;
						}
						else if(!ctype_digit($inputs['ein'])) {
							$this->error = "Invalid characters detected.";
							break;
						}
						else if(strlen($inputs['ein']) < 9) {
							$this->error = "That is not a valid EIN.";
							break;
						}
					case "investing_exp_equites":
						$options = ["Less than 1 year", "1 - 3 years", "More than 3 years"];

						if(in_array($answer, $options) && count($inputs) == 0 && 
							$this->query("UPDATE account_setup SET {$this->current_question[1]} = ? WHERE uid = ?", array($answer, $uid))) {
							break;
						}
						else {
							$this->error = "Please select a valid option.";
							break;
						}
					case "investing_exp_bonds":
						$options = ["Less than 1 year", "1 - 3 years", "More than 3 years"];

						if(in_array($answer, $options) && count($inputs) == 0 && 
							$this->query("UPDATE account_setup SET {$this->current_question[1]} = ? WHERE uid = ?", array($answer, $uid))) {
							break;
						}
						else {
							$this->error = "Please select a valid option.";
							break;
						}
					case "investing_exp_forex":
						$options = ["Less than 1 year", "1 - 3 years", "More than 3 years"];

						if(in_array($answer, $options) && count($inputs) == 0 && 
							$this->query("UPDATE account_setup SET {$this->current_question[1]} = ? WHERE uid = ?", array($answer, $uid))) {
							break;
						}
						else {
							$this->error = "Please select a valid option.";
							break;
						}
					case "investing_exp_others":
						$options = ["Less than 1 year", "1 - 3 years", "More than 3 years"];

						if(in_array($answer, $options) && count($inputs) == 0 && 
							$this->query("UPDATE account_setup SET {$this->current_question[1]} = ? WHERE uid = ?", array($answer, $uid))) {
							break;
						}
						else {
							$this->error = "Please select a valid option.";
							break;
						}
					case "bank_information":
						if(isset($inputs['bank_name']) && isset($inputs['account_number']) && isset($inputs['routing_number']) && isset($inputs['billing_address']) && strlen($answer) == 0 && ctype_digit($inputs['account_number']) && ctype_digit($inputs['routing_number']) && strlen($inputs['account_number']) > 0 && strlen($inputs['routing_number']) > 0 && strlen($inputs['bank_name']) > 0 && strlen($inputs['billing_address']) > 0 && $this->query("UPDATE account_setup SET {$this->current_question[1]} = ? WHERE uid = ?", array(json_encode($inputs, true), $uid))) {
							break;
						}
						else if(strlen($inputs['bank_name']) == 0 && strlen($inputs['account_number']) == 0 && strlen($inputs['routing_number']) == 0 && strlen($inputs['billing_address']) == 0) {
							break;
						}
					case "investing_purpose":
						$options = ["Long term growth (1 year or more)", "Short term growth (Less than 1 year)", "College", "Savings"];

						if(in_array($answer, $options) && isset($inputs['purpose_other']) && 
							$this->query("UPDATE account_setup SET {$this->current_question[1]} = ? WHERE uid = ?", array(json_encode(array('selection'=>$answer, 'other'=>$inputs['purpose_other']), true), $uid))) {
							break;
						}
						else {
							$this->error = "Please select a valid option.";
							break;
						}
					case "risk_profile":
						$options = ["Low volatility", "Medium volatility", "High volatility"];

						if(in_array($answer, $options) && count($inputs) == 0 && 
							$this->query("UPDATE account_setup SET {$this->current_question[1]} = ? WHERE uid = ?", array($answer, $uid))) {
							break;
						}
						else {
							$this->error = "Please select a valid option.";
							break;
						}
					case "asset_allocation":
						$options = ["International and domestic (US) markets", "US market only"];

						if(in_array($answer, $options) && count($inputs) == 0 && 
							$this->query("UPDATE account_setup SET {$this->current_question[1]} = ? WHERE uid = ?", array($answer, $uid))) {
							$this->setupComplete($uid);
							break;
						}
						else {
							$this->error = "Please select a valid option.";
							break;
						}
					default:
						$this->error = "Action not allowed.";
				}
			}
			else {
				$this->error = "Action not allowed.";
			}
		}
		else {
			$this->error = "Your session has expired.";
		} 
	}

	public function __destruct() {
		DBConnection::__destruct();
	} 
}


?>