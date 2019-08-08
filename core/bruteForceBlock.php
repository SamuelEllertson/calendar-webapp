<?PHP

class BruteForceBlock {
	// array of throttle settings. # failed_attempts => response
	private static $default_throttle_settings = [
			6 => 3, 		//delay in seconds 	
			12 => 'captcha' //captcha 
	];
	
	//database config
	private static $_db = [
		'driver' => "mysql",
		'host'	=> "db.yhscs.us",
		'database'	=> "yhscs_calendar",
		'charset' => "utf8",
		'username' => "sam2017",
		'password' => "yhs1703198",
		'auto_clear' => true
	];
	
	//time frame to use when retrieving the number of recent failed logins from database
	private static $time_frame_minutes = 10;
	
	//setup and return database connection
	private static function _databaseConnect(){
		//connect to database
		$db = new \PDO(self::$_db['driver'].
			':host='.self::$_db['host'].
			';dbname='.self::$_db['database'].
			';charset='.self::$_db['charset'], 
			self::$_db['username'], 
			self::$_db['password']);
		
		$db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
		$db->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
		
		//return the db connection object
		return $db;
	}
	//add a failed login attempt to database. returns true, or error 
	public static function addFailedLoginAttempt($user_id, $ip_address, $location = "login"){
		//get db connection
		$db = BruteForceBlock::_databaseConnect();
		
		//get current timestamp
		$timestamp = date('Y-m-d H:i:s');
		
		//attempt to insert failed login attempt
		try{
			$stmt = $db->query('INSERT INTO user_failed_logins SET user_id = '.$user_id.', ip_address = "'.$ip_address.'", attempted_at = NOW(), location = "'.$location.'";');
			//$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
			return true;
		} catch(\PDOException $ex){
			//return errors
			return $ex;
		}
	}
	//get the current login status. either safe, delay, catpcha, or error
	public static function getLoginStatus($user_id, $ip_address, $options = null, $location = "login"){
		//get db connection
		$db = BruteForceBlock::_databaseConnect();
		
		//setup response array
		$response_array = array(
			'status' => 'safe',
			'message' => null
		);
		
		//attempt to retrieve latest failed login attempts
		$stmt = null;
		$latest_failed_logins = null;
		$row = null;
		$latest_failed_attempt_datetime = null;
		try{
			$stmt = $db->query('SELECT MAX(attempted_at) AS attempted_at FROM user_failed_logins WHERE (`user_id` = '.$user_id.' OR `ip_address` = "'.$ip_address.'") AND `location` = "'.$location.'";');
			$latest_failed_logins = $stmt->rowCount();
			$row = $stmt-> fetch();
			//get latest attempt's timestamp
			$latest_failed_attempt_datetime = (int) date('U', strtotime($row['attempted_at']));
		} catch(\PDOException $ex){
			//return error
			$response_array['status'] = 'error';
			$response_array['message'] = $ex;
		}
		
		//get local var of throttle settings. check if options parameter set
		if($options == null){
			$throttle_settings = self::$default_throttle_settings;
		}else{
			//use options passed in
			$throttle_settings = $options;
		}
		//grab first throttle limit from key
		reset($throttle_settings);
		$first_throttle_limit = key($throttle_settings);
		//attempt to retrieve latest failed login attempts
		try{
			//get all failed attempst within time frame
			$get_number = $db->query('SELECT * FROM user_failed_logins WHERE (attempted_at > DATE_SUB(NOW(), INTERVAL '.self::$time_frame_minutes.' MINUTE)) AND (`user_id` = '.$user_id.' OR `ip_address` = "'.$ip_address.'") AND (`location` = "'.$location.'");');
			$number_recent_failed = $get_number->rowCount();
			//reverse order of settings, for iteration
			krsort($throttle_settings);
			
			//if number of failed attempts is >= the minimum threshold in throttle_settings, react
			if($number_recent_failed >= $first_throttle_limit ){				
				//it's been decided the # of failed logins is troublesome. time to react accordingly, by checking throttle_settings
				foreach ($throttle_settings as $attempts => $delay) {
					if ($number_recent_failed > $attempts) {
						// we need to throttle based on delay
						if (is_numeric($delay)) {
							//find the time of the next allowed login
							$next_login_minimum_time = $latest_failed_attempt_datetime + $delay;
							
							//if the next allowed login time is in the future, calculate the remaining delay
							if(time() < $next_login_minimum_time){
								$remaining_delay = $next_login_minimum_time - time();
								// add status to response array
								$response_array['status'] = 'delay';
								$response_array['message'] = $remaining_delay;
							}else{
								// delay has been passed, safe to login
								$response_array['status'] = 'safe';
							}
							//$remaining_delay = $delay - (time() - $latest_failed_attempt_datetime); //correct
							//echo 'You must wait ' . $remaining_delay . ' seconds before your next login attempt';
							
						} else {
							// add status to response array
							$response_array['status'] = 'captcha';
						}
						break;
					}
				}  
				
			}
			//clear database if config set
			if(self::$_db['auto_clear'] == true){
				//attempt to delete all records that are no longer recent/relevant
				try{
					//get current timestamp
					$now = date('Y-m-d H:i:s');
					$stmt = $db->query('DELETE from user_failed_logins WHERE attempted_at < DATE_SUB(NOW(), INTERVAL '.(self::$time_frame_minutes * 2).' MINUTE)');
					$stmt->execute();
					
				} catch(\PDOException $ex){
					$response_array['status'] = 'error';
					$response_array['message'] = $ex;
				}
			}
			
		} catch(\PDOException $ex){
			//return error
			$response_array['status'] = 'error';
			$response_array['message'] = $ex;
		}
		
		//return the response array containing status and message 
		return $response_array;
	}
	
	//clear the database
	public static function clearDatabase(){
		//get db connection
		$db = BruteForceBlock::_databaseConnect();
		
		//attempt to delete all records
		try{
			$stmt = $db->query('DELETE from user_failed_logins');
			return true;
		} catch(\PDOException $ex){
			//return errors
			return $ex;
		}
	}
}


?>