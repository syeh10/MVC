<?php
/*dbAdapter: this module acts as the database abstraction layer for the application

@Author: Vinh Bui (vinh.bui@scu.edu.au)
@Modified by: Shu Wei, Yeh (s.yeh.10@student.scu.edu.au)
@Date: 19/09/2017
--------------------------------------------------------------------------------------------------*/

/*Connection paramaters
*/
require_once('movie_zone_config.php');

/* DBAdpater class performs all required CRUD functions for the application
*/
class DBAdaper {
	/*
	local variables
	*/
	private $dbConnectionString;
	private $dbUser;
	private $dbPassword;
	private $dbConn; //holds connection object
	private $dbError; //holds last error message

	/*
	The class constructor
	*/
	public function __construct($dbConnectionString, $dbUser, $dbPassword) {
		$this->dbConnectionString = $dbConnectionString;
		$this->dbUser = $dbUser;
		$this->dbPassword = $dbPassword;
	}
	/*
	Opens connection to the database
	*/
	public function dbOpen() {
		try {
			$this->dbConn = new PDO($this->dbConnectionString, $this->dbUser, $this->dbPassword);
			// set the PDO error mode to exception
			$this->dbConn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$this->dbError = null;
		}
		catch(PDOException $e) {
			$this->dbError = $e->getMessage();
			$this->dbConn = null;
		}
	}

	/* Returns the database connection so it can be accessible outside the dbAdapter class
	*/
	public function getDbConnection() {
		return $this->dbConn;
	}
	/*
	Closes connection to the database
	*/
	public function dbClose() {
		//in PDO assigning null to the connection object closes the connection
		$this->dbConn = null;
	}
	/*
	Return last database error
	*/
	public function lastError() {
		return $this->dbError;
	}
	/*
	Creates required tables in the database if not already created
	@return: TRUE if successful and FALSE otherwise
	*/
	public function dbCreate() {
		if ($this->dbConn != null) {
			try {
				//table movie
				$sql = "CREATE TABLE IF NOT EXISTS `movie` (
					`movie_id` int(10) NOT NULL,
					`title` varchar(45) NOT NULL,
					`tagline` varchar(128) NOT NULL,
					`plot` varchar(256) NOT NULL,
					`thumbpath` varchar(40) NOT NULL,
					`director_id` int(10) NOT NULL,
					`studio_id` int(10) NOT NULL,
					`genre_id` int(10) NOT NULL,
					`classification` varchar(128) NOT NULL,
					`rental_period` varchar(128) NOT NULL,
					`year` int(4) NOT NULL,
					`DVD_rental_price` decimal(4,2) NOT NULL DEFAULT '0.00',
					`DVD_purchase_price` decimal(4,2) NOT NULL DEFAULT '0.00',
					`numDVD` int(3) NOT NULL DEFAULT '0',
					`numDVDout` int(3) NOT NULL DEFAULT '0',
					`BluRay_rental_price` decimal(4,2) NOT NULL DEFAULT '0.00',
					`BluRay_purchase_price` decimal(4,2) NOT NULL DEFAULT '0.00',
					`numBluRay` int(3) NOT NULL DEFAULT '0',
					`numBluRayOut` int(3) NOT NULL DEFAULT '0'
				) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
				$result = $this->dbConn->exec($sql);
				//table movie_actor
				$sql = "CREATE TABLE `movie_actor` (
					`movie_id` int(10) NOT NULL,
					`actor_id` int(10) NOT NULL,
					`role` varchar(10) NOT NULL
				) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
				$result = $this->dbConn->exec($sql);
				//table actor
				$sql = "CREATE TABLE `actor` (
					`actor_id` int(10) NOT NULL,
					`actor_name` char(128) DEFAULT NULL
				) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4";
				$result = $this->dbConn->exec($sql);
				//table director
				$sql = "CREATE TABLE IF NOT EXISTS `director` (
					`director_id` int(10) NOT NULL,
					`director_name` char(128) DEFAULT NULL
				) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
				$result = $this->dbConn->exec($sql);
				//table studio
				$sql = "CREATE TABLE IF NOT EXISTS `studio` (
					`studio_id` int(10) UNSIGNED NOT NULL,
					`studio_name` char(128) NOT NULL
				) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
				$result = $this->dbConn->exec($sql);
				//table genre
				$sql = "CREATE TABLE IF NOT EXISTS `genre` (
					`genre_id` int(10) UNSIGNED NOT NULL,
					`genre_name` char(128) NOT NULL
				) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
				$result = $this->dbConn->exec($sql);
				//table member
				$sql = "CREATE TABLE IF NOT EXISTS `member` (
					`member_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
					`surname` varchar(128) NOT NULL,
					`other_name` varchar(128) NOT NULL,
					`contact_method` varchar(10) NOT NULL DEFAULT,
					`email` varchar(40) DEFAULT,
					`mobile` varchar(40) DEFAULT,
					`landline` varchar(40) DEFAULT,
					`magazine` tinyint(1) NOT NULL DEFAULT,
					`street` varchar(40) DEFAULT,
					`suburb` varchar(40) DEFAULT,
					`postcode` int(4) DEFAULT NULL,
					`username` varchar(10) NOT NULL DEFAULT,
					`password` varchar(10) NOT NULL DEFAULT,
					`occupation` varchar(20) DEFAULT,
					`join_date` date NOT NULL,
				) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
				$result = $this->dbConn->exec($sql);
				//create view to simplify the movie selection
				$sql = "CREATE OR REPLACE VIEW movie_detail_view AS
				SELECT `movie`.`movie_id` ,
				`movie`.`title`,
				`movie`.`tagline`,
				`movie`.`plot`,
				`movie`.`thumbpath`,
				`movie`.`director_id`,
				group_concat(if((`movie_actor`.`role` = 'star1'),`actor`.`actor_name`,NULL) separator ',') AS `star1`,
				group_concat(if((`movie_actor`.`role` = 'star2'),`actor`.`actor_name`,NULL) separator ',') AS `star2`,
				group_concat(if((`movie_actor`.`role` = 'star3'),`actor`.`actor_name`,NULL) separator ',') AS `star3`,
				group_concat(if((`movie_actor`.`role` = 'costar1'),`actor`.`actor_name`,NULL) separator ',') AS `costar1`,
				group_concat(if((`movie_actor`.`role` = 'costar2'),`actor`.`actor_name`,NULL) separator ',') AS `costar2`,
				group_concat(if((`movie_actor`.`role` = 'costar3'),`actor`.`actor_name`,NULL) separator ',') AS `costar3`,
				`director`.`director_name` AS `director`,
				`studio`.`studio_name` AS `studio`,
				`genre`.`genre_name` AS `genre`,
				`movie`.`classification`,
				`movie`.`rental_period`,
				`movie`.`year`,
				`movie`.`DVD_rental_price`,
				`movie`.`DVD_purchase_price`,
				`movie`.`numDVD`,
				`movie`.`numDVDout`,
				`movie`.`BluRay_rental_price`,
				`movie`.`BluRay_purchase_price`,
				`movie`.`numBluRay`,
				`movie`.`numBluRayOut`
				FROM `movie`
				JOIN `movie_actor`
				ON `movie`.`movie_id` = `movie_actor`.`movie_id`
				JOIN `actor`
				ON `movie_actor`.`actor_id` = `actor`.`actor_id`
				JOIN `director`
				ON `movie`.`director_id` = `director`.`director_id`
				JOIN `studio`
				ON `movie`.`studio_id` = `studio`.`studio_id`
				JOIN `genre`
				ON `movie`.`genre_id` = `genre`.`genre_id`
				GROUP BY `movie`.`title`,`movie`.`movie_id`";
				$result = $this->dbConn->exec($sql);
			} catch (PDOException $e) {
				//Return the error message to the caller
				$this->dbError = $e->getMessage();
				$result = null;
			}
		} else {
			$this->dbError = MSG_ERR_CONNECTION;
		}
		return $result;
	}

	/*-------------------------------------------------------------------------------------------
	DATABASE MANIPULATION FUNCTIONS
	-------------------------------------------------------------------------------------------*/

	/*
	Helper function:
	Build SQL AND conditional clause from the array of condition parameters
	*/
	protected function sqlBuildConditionalClause($params, $condition) {
		$clause = "";
		$and = false; //so we know when to add AND in the sql statement
		if ($params != null) {
			foreach ($params as $key => $value) {
				$op = '='; //comparison operator
				if ($key == 'year')
				$op = '<=';
				if (!empty($value)) {
					if ($and){
						$clause = $clause." $condition $key $op '$value'";
					}
					else {
						//the first AND condition
						$clause = "WHERE $key $op '$value'";
						$and = true;
					}
				}
			}
		}

		return $clause;
	}

	/*
	Select all existing movies from the movie table
	@return: an array of matched movies
	*/
	public function movieSelectAll() {
		$result = null;
		$this->dbError = null; //reset the error message before any execution
		if ($this->dbConn != null) {
			try {
				//Make a prepared query so that we can use data binding and avoid SQL injections.
				//(modify suit the A2 member table)
				$smt = $this->dbConn->prepare(
					'SELECT movie_detail_view.*
					FROM movie_detail_view');
					//Execute the query
					$smt->execute();
					$result = $smt->fetchAll(PDO::FETCH_ASSOC);
					//use PDO::FETCH_BOTH to have both column name and column index
					//$result = $sql->fetchAll(PDO::FETCH_BOTH);
				}catch (PDOException $e) {
					//Return the error message to the caller
					$this->dbError = $e->getMessage();
					$result = null;
				}
			} else {
				$this->dbError = MSG_ERR_CONNECTION;
			}

			return $result;
		}

		/*
		Select ramdom movies from the movie table
		@param: $max - the maximum number of movies will be selected
		@return: an array of matched movies (default 1 movie)
		*/
		public function movieSelectRandom($max=1) {
			$result = null;
			$this->dbError = null; //reset the error message before any execution
			if ($this->dbConn != null) {
				try {
					//Make a prepared query so that we can use data binding and avoid SQL injections.
					//(modify suit the A2 member table)
					$smt = $this->dbConn->prepare(
						"	SELECT movie_detail_view.*
						FROM movie_detail_view
						ORDER BY RAND()
						LIMIT $max");
						//Execute the query
						$smt->execute();
						$result = $smt->fetchAll(PDO::FETCH_ASSOC);
						//use PDO::FETCH_BOTH to have both column name and column index
						//$result = $sql->fetchAll(PDO::FETCH_BOTH);
					}catch (PDOException $e) {
						//Return the error message to the caller
						$this->dbError = $e->getMessage();
						$result = null;
					}
				} else {
					$this->dbError = MSG_ERR_CONNECTION;
				}

				return $result;
			}

			/*
			Select new movies from the movie table
			*/
			public function movieSelectNew() {
				$result = null;
				$this->dbError = null; //reset the error message before any execution
				if ($this->dbConn != null) {
					try {
						//Make a prepared query so that we can use data binding and avoid SQL injections.
						//(modify suit the A2 member table)
						$smt = $this->dbConn->prepare(
							"	SELECT movie_detail_view.*
							FROM movie_detail_view
							ORDER BY movie_id DESC
							");
							//Execute the query
							$smt->execute();
							$result = $smt->fetchAll(PDO::FETCH_ASSOC);
							//use PDO::FETCH_BOTH to have both column name and column index
							//$result = $sql->fetchAll(PDO::FETCH_BOTH);
						}catch (PDOException $e) {
							//Return the error message to the caller
							$this->dbError = $e->getMessage();
							$result = null;
						}
					} else {
						$this->dbError = MSG_ERR_CONNECTION;
					}

					return $result;
				}

				/*
				Select new movies from the movie table
				*/
				public function movieSelectAvailable() {
					$result = null;
					$this->dbError = null; //reset the error message before any execution
					if ($this->dbConn != null) {
						try {
							//Make a prepared query so that we can use data binding and avoid SQL injections.
							//(modify suit the A2 member table)
							$smt = $this->dbConn->prepare(
								"SELECT movie_detail_view.*
								FROM movie_detail_view
								WHERE ((numDVD-numDVDout)>0 OR (numBluRay-numBluRayOut)>0)
								ORDER BY numDVDout, numBluRayOut DESC
								");
								//Execute the query
								$smt->execute();
								$result = $smt->fetchAll(PDO::FETCH_ASSOC);
								//use PDO::FETCH_BOTH to have both column name and column index
								//$result = $sql->fetchAll(PDO::FETCH_BOTH);
							}catch (PDOException $e) {
								//Return the error message to the caller
								$this->dbError = $e->getMessage();
								$result = null;
							}
						} else {
							$this->dbError = MSG_ERR_CONNECTION;
						}

						return $result;
					}

					/*
					Select an existing movie from the movie table
					@param $condition: is an associative array of movie's details you want to match
					@return: an array of matched movies
					*/
					public function movieFilter($condition) {

						$result = null;
						$this->dbError = null; //reset the error message before any execution
						if ($this->dbConn != null) {
							try {
								//Make a prepared query so that we can use data binding and avoid SQL injections.
								//(modify suit the A2 member table)
								$sql = 'SELECT movie_detail_view.* FROM movie_detail_view '
								.$this->sqlBuildConditionalClause($condition, 'AND');
								$smt = $this->dbConn->prepare($sql);
								//Execute the query
								$smt->execute();
								$result = $smt->fetchAll(PDO::FETCH_ASSOC);
								//use PDO::FETCH_BOTH to have both column name and column index
								//$result = $sql->fetchAll(PDO::FETCH_BOTH);
							}catch (PDOException $e) {
								//Return the error message to the caller
								$this->dbError = $e->getMessage();
								$result = null;
							}
						} else {
							$this->dbError = MSG_ERR_CONNECTION;
						}

						return $result;
					}


					/*
					Select all existing states from the states table
					@return: an array of states with column name as the keys;
					*/
					public function genreSelectAll() {
						$result = null;
						$this->dbError = null; //reset the error message before any execution
						if ($this->dbConn != null) {
							try {
								//Make a prepared query so that we can use data binding and avoid SQL injections.
								//(modify suit the A2 member table)
								$smt = $this->dbConn->prepare('SELECT * FROM genre');
								//Execute the query
								$smt->execute();
								$result = $smt->fetchAll(PDO::FETCH_ASSOC);
								//use PDO::FETCH_BOTH to have both column name and column index
								//$result = $sql->fetchAll(PDO::FETCH_BOTH);
							}catch (PDOException $e) {
								//Return the error message to the caller
								$this->dbError = $e->getMessage();
								$result = null;
							}
						} else {
							$this->dbError = MSG_ERR_CONNECTION;
						}

						return $result;
					}


					/*
					Select all existing make from the makes table
					@return: an array of make with column name as the keys;
					*/
					public function directorSelectAll() {
						$result = null;
						$this->dbError = null; //reset the error message before any execution
						if ($this->dbConn != null) {
							try {
								//Make a prepared query so that we can use data binding and avoid SQL injections.
								//(modify suit the A2 member table)
								$smt = $this->dbConn->prepare('SELECT * FROM director');
								//Execute the query
								$smt->execute();
								$result = $smt->fetchAll(PDO::FETCH_ASSOC);
								//use PDO::FETCH_BOTH to have both column name and column index
								//$result = $sql->fetchAll(PDO::FETCH_BOTH);
							}catch (PDOException $e) {
								//Return the error message to the caller
								$this->dbError = $e->getMessage();
								$result = null;
							}
						} else {
							$this->dbError = MSG_ERR_CONNECTION;
						}

						return $result;
					}


					/*
					Select all existing body type from the bodytypes table
					@return: an array of body type with column name as the keys;
					*/
					public function studioSelectAll() {
						$result = null;
						$this->dbError = null; //reset the error message before any execution
						if ($this->dbConn != null) {
							try {
								//Make a prepared query so that we can use data binding and avoid SQL injections.
								//(modify suit the A2 member table)
								$smt = $this->dbConn->prepare('SELECT * FROM studio');
								//Execute the query
								$smt->execute();
								$result = $smt->fetchAll(PDO::FETCH_ASSOC);
								//use PDO::FETCH_BOTH to have both column name and column index
								//$result = $sql->fetchAll(PDO::FETCH_BOTH);
							}catch (PDOException $e) {
								//Return the error message to the caller
								$this->dbError = $e->getMessage();
								$result = null;
							}
						} else {
							$this->dbError = MSG_ERR_CONNECTION;
						}
						return $result;
					}

					/*
					Adds a member to the member table
					@param: $member is an associative array of member details
					@return: last-insert-id if successful and 0 (FALSE) otherwise
					*/
					function memberAdd($member) {
						$result = null;
						$this->dbError = null; //reset the error message before any execution
						if ($this->dbConn != null) {
							//Try and insert the movie, if there is a DB exception return
							//the error message to the caller.
							try {
								//Make a prepared query so that we can use data binding and avoid SQL injections.
								$smt = $this->dbConn->prepare('INSERT INTO member
									(`member_id`, `surname`, `other_name`, `contact_method`, `email`, `mobile`,
										`landline`, `magazine`, `street`, `suburb`, `postcode`, `username`, `password`,
										`occupation`, `join_date`)
										VALUES
										( :member_id, :surname, :other_name, :contact_method, :email, :mobile,
											:landline, :magazine, :street, :suburb, :postcode, :username, :password,
											:occupation, :join_date)');

											//Bind the data from the form to the query variables.
											//Doing it this way means PDO sanitises the input which prevents SQL injection.
											$smt->bindParam(':member_id', $member['member_id'], PDO::PARAM_STR);
											$smt->bindParam(':surname', $member['surname'], PDO::PARAM_STR);
											$smt->bindParam(':other_name', $member['other_name'], PDO::PARAM_STR);
											$smt->bindParam(':contact_method', $member['contact_method'], PDO::PARAM_STR);
											$smt->bindParam(':email', $member['email'], PDO::PARAM_STR);
											$smt->bindParam(':mobile', $member['mobile'], PDO::PARAM_STR);
											$smt->bindParam(':landline', $member['landline'], PDO::PARAM_STR);
											$smt->bindParam(':magazine', $member['magazine'], PDO::PARAM_STR);
											$smt->bindParam(':street', $member['street'], PDO::PARAM_STR);
											$smt->bindParam(':suburb', $member['suburb'], PDO::PARAM_STR);
											$smt->bindParam(':postcode', $member['postcode'], PDO::PARAM_STR);
											$smt->bindParam(':username', $member['username'], PDO::PARAM_STR);
											$smt->bindParam(':password', $member['password'], PDO::PARAM_STR);
											$smt->bindParam(':join_date', $member['join_date'], PDO::PARAM_STR);
											//Execute the query and thus insert the movie
											$smt->execute();
											$result = $this->dbConn->lastInsertId();

										}catch (PDOException $e) {
											//Return the error message to the caller
											$this->dbError = $e->getMessage();
											$result = null;
										}
									} else {
										$this->dbError = MSG_ERR_CONNECTION;
									}

									return $result;
								}
								/*
								Updates an existing member in the members table
								@param: $member is an associative array of movie details to be updated
								@return: TRUE if successful and FALSE if not
								*/
								function memberUpdate($member) {
									$result = null;
									$this->dbError = null; //reset the error message before any execution
									if ($this->dbConn != null) {
										//Make a prepared query so that we can use data binding and avoid SQL injections.
										//Try and insert the member, if there is a DB exception return the error message to the caller.
										try {
											$smt = $this->dbConn->prepare('UPDATE member SET
												member_id = :member_id,
												surname = :surname,
												other_name = :other_name,
												contact_method = :contact_method,
												email = :email,
												mobile = :mobile,
												landline = :landline,
												magazine = :magazine,
												street = ;street,
												suburb = :suburb,
												postcode = :postcode,
												username = ;username,
												password = :numDpasswordVD,
												occupation = :occupation
												WHERE movie_id = :movie_id');

												//Bind the data from the form to the query variables.
												//Doing it this way means PDO sanitises the input which prevents SQL injection.
												$smt->bindParam(':member_id', $member['member_id'], PDO::PARAM_STR);
												$smt->bindParam(':surname', $member['surname'], PDO::PARAM_STR);
												$smt->bindParam(':other_name', $member['other_name'], PDO::PARAM_STR);
												$smt->bindParam(':contact_method', $member['contact_method'], PDO::PARAM_STR);
												$smt->bindParam(':email', $member['email'], PDO::PARAM_STR);
												$smt->bindParam(':mobile', $member['mobile'], PDO::PARAM_STR);
												$smt->bindParam(':landline', $member['landline'], PDO::PARAM_STR);
												$smt->bindParam(':magazine', $member['magazine'], PDO::PARAM_STR);
												$smt->bindParam(':street', $member['street'], PDO::PARAM_STR);
												$smt->bindParam(':suburb', $member['suburb'], PDO::PARAM_STR);
												$smt->bindParam(':postcode', $member['postcode'], PDO::PARAM_STR);
												$smt->bindParam(':username', $member['username'], PDO::PARAM_STR);
												$smt->bindParam(':password', $member['password'], PDO::PARAM_STR);
												$smt->bindParam(':join_date', $member['join_date'], PDO::PARAM_STR);

												//Execute the query and thus insert the member
												$result = $smt->execute();
											}catch (PDOException $e) {
												//Return the error message to the caller
												$this->dbError = $e->getMessage();
												$result = null;
											}
										} else {
											$this->dbError = MSG_ERR_CONNECTION;
										}

										return $result;
									}
								}

								/*----------------------------------------------------------------------------------------------
								TEST FUNCTIONS
								----------------------------------------------------------------------------------------------*/

								//Your task: implement the test function to test each function in this dbAdapter


								/*Tests database functions
								*/
								function testDBA() {
									$dbAdapter = new DBAdaper(DB_CONNECTION_STRING, DB_USER, DB_PASS);

									$movie = array(
										`title` =>'Tomorrow',
										`tagline` =>'Fire burns brighter in the darkness',
										`plot` =>'Archaeologist and adventurer Indiana Jones is hired by the US government to find the Ark of the Covenant before the Nazis.',
										`thumbpath` =>'1435650757crow.jpg',
										`director_id` =>9,
										`studio_id` =>7,
										`genre_id` =>10,
										`classification` =>'MA',
										`rental_period` =>'Weekly',
										`year` =>2018,
										`DVD_rental_price` =>'5.00',
										`DVD_purchase_price` =>'20.00',
										`numDVD` =>15,
										`numDVDout` =>7,
										`BluRay_rental_price` =>'6.00',
										`BluRay_purchase_price` =>'30.00',
										`numBluRay` =>11,
										`numBluRayOut` =>10,
									);
									$dbAdapter->dbOpen();
									$dbAdapter->dbCreate();
									if ($result != null)
									print_r($result);
									else
									echo $dbAdapter->lastError();
									$dbAdapter->dbClose();
								}

								//execute the test
								//testDBA();

								?>
