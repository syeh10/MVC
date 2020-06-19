<?php
/*dbAdapter: this module acts as the database abstraction layer for the application
@Author: Vinh Bui (vinh.bui@scu.edu.au)
@Modified by: Shu Wei, Yeh (s.yeh.10@student.scu.edu.au)
@Date: 19/09/2017
--------------------------------------------------------------------------------------------------*/

/*
Connection paramaters
*/
require_once('movie_zone_admin_config.php');

/*
DBAdpater class performs all required CRUD functions for the application
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
		$this->dbError = null; //reset the error message before any execution
		try {
			$this->dbConn = new PDO($this->dbConnectionString, $this->dbUser, $this->dbPassword);
			// set the PDO error mode to exception
			$this->dbConn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		}
		catch(PDOException $e) {
			$this->dbError = $e->getMessage();
			$this->dbConn = null;
		}
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
	Returns the database connection so it can be accessible outside the dbAdapter class
	*/
	public function getDbConnection() {
		return $this->dbConn;
	}

	/*
	Creates required tables in the database if not already created
	@return: TRUE if successful and FALSE otherwise
	*/
	public function dbCreate() {
		$this->dbError = null; //reset the error message before any execution
		if ($this->dbConn != null) {
			try {
				//table studio
				$sql = "CREATE TABLE IF NOT EXISTS `studio` (
					`studio_id` int(10) UNSIGNED NOT NULL,
					`studio_name` char(128) NOT NULL
				) ENGINE=InnoDB DEFAULT CHARSET=utf8";
				$result = $this->dbConn->exec($sql);
				//table movies
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
				) ENGINE=InnoDB DEFAULT CHARSET=utf8";
				$result = $this->dbConn->exec($sql);
				//table directors
				$sql = "CREATE TABLE IF NOT EXISTS `director` (
					`director_id` int(10) NOT NULL,
					`director_name` char(128) DEFAULT NULL
				) ENGINE=InnoDB DEFAULT CHARSET=utf8";
				$result = $this->dbConn->exec($sql);
				//table genre
				$sql = "CREATE TABLE IF NOT EXISTS `genre` (
					`genre_id` int(10) UNSIGNED NOT NULL,
					`genre_name` char(128) NOT NULL
				) ENGINE=InnoDB DEFAULT CHARSET=utf8";
				$result = $this->dbConn->exec($sql);
				//create view to simplify the movie selection
				$sql = "CREATE OR REPLACE VIEW movie_detail_view AS
				SELECT
				`movie`.`movie_id` AS `movie_id`,
				`movie`.`title` AS `title`,
				`movie`.`tagline` AS `tagline`,
				`movie`.`plot` AS `plot`,
				`movie`.`thumbpath` AS `thumbpath`,
				group_concat(if((`movie_actor`.`role` = 'star1'),`actor`.`actor_name`,NULL) separator ',') AS `star1`,
				group_concat(if((`movie_actor`.`role` = 'star2'),`actor`.`actor_name`,NULL) separator ',') AS `star2`,
				group_concat(if((`movie_actor`.`role` = 'star3'),`actor`.`actor_name`,NULL) separator ',') AS `star3`,
				group_concat(if((`movie_actor`.`role` = 'costar1'),`actor`.`actor_name`,NULL) separator ',') AS `costar1`,
				group_concat(if((`movie_actor`.`role` = 'costar2'),`actor`.`actor_name`,NULL) separator ',') AS `costar2`,
				group_concat(if((`movie_actor`.`role` = 'costar3'),`actor`.`actor_name`,NULL) separator ',') AS `costar3`,
				`director`.`director_name` AS `director`,
				`studio`.`studio_name` AS `studio`,
				`genre`.`genre_name` AS `genre`,
				`movie`.`classification` AS `classification`,
				`movie`.`rental_period` AS `rental_period`,
				`movie`.`year` AS `year`,
				`movie`.`DVD_rental_price` AS `DVD_rental_price`,
				`movie`.`DVD_purchase_price` AS `DVD_purchase_price`,
				`movie`.`numDVD` AS `numDVD`,
				`movie`.`numDVDout` AS `numDVDout`,
				`movie`.`BluRay_rental_price` AS `BluRay_rental_price`,
				`movie`.`BluRay_purchase_price` AS `BluRay_purchase_price`,
				`movie`.`numBluRay` AS `numBluRay`,
				`movie`.`numBluRayOut` AS `numBluRayOut`
				FROM
				(((((`movie` join `actor`) join `movie_actor`) join `director`) join `studio`) join `genre`) where ((`movie`.`movie_id` = `movie_actor`.`movie_id`) and (`movie_actor`.`actor_id` = `actor`.`actor_id`) and (`movie`.`director_id` = `director`.`director_id`) and (`movie`.`studio_id` = `studio`.`studio_id`) and (`movie`.`genre_id` = `genre`.`genre_id`) and (`movie`.`director_id` = `director`.`director_id`) and (`movie`.`studio_id` = `studio`.`studio_id`) and (`movie`.`genre_id` = `genre`.`genre_id`)) group by `movie`.`title`,`movie`.`movie_id`
				";
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
	Helper functions:
	Build SQL AND conditional clause from the array of condition paramaters
	*/
	protected function sqlBuildConditionalClause($params, $condition) {
		$clause = "";
		$and = false; //so we know when to add AND in the sql genrement
		if ($params != null) {
			foreach ($params as $key => $value) {
				$op = '='; //comparison operator
				if ($key == 'DVD_rental_price')
				$op = '<=';
				if (!empty($value)) {
					if ($and){
						$clause = $clause." $condition $key $op '$value'";
					} else {
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
				$smt = $this->dbConn->prepare(
					'SELECT movie_detail_view.*
					FROM movie_detail_view');
					//Execute the query and thus insert the movie
					$smt->execute();
					$result = $smt->fetchAll(PDO::FETCH_ASSOC);
					//use PDO::FETCH_BOTH to have both column name and column index
					// $result = $sql->fetchAll(PDO::FETCH_BOTH);
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
				Select all existing members from the member table
				@return: an array of matched members
				*/
				public function memberSelectAll() {
					$result = null;
					//reset the error message before any execution
					$this->dbError = null;
					if ($this->dbConn != null) {
						try {
							//Make a prepared query so that we can use data binding and avoid SQL injections.
							$smt = $this->dbConn->prepare(
								'SELECT member.*
								FROM member
								ORDER BY member_id');
								//Execute the query and thus insert the movie
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
					Select all existing members from the member table
					@return: an array of matched members
					*/
					public function moviesInventory() {
						$result = null;
						//reset the error message before any execution
						$this->dbError = null;
						if ($this->dbConn != null) {
							try {
								//Make a prepared query so that we can use data binding and avoid SQL injections.
								$smt = $this->dbConn->prepare(
									'SELECT movie_id, title,
									numDVD-numDVDout AS InventoryDVD ,
									numBluRay-numBluRayOut AS InventoryBluRay
									FROM movie
									ORDER BY movie_id');
									//Execute the query and thus insert the movie
									$smt->execute();
									$result = $smt->fetchAll(PDO::FETCH_ASSOC);
									//use PDO::FETCH_BOTH to have both column name and column index
									// $result = $sql->fetchAll(PDO::FETCH_BOTH);
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
									"SELECT movie_detail_view.*
									FROM movie_detail_view
									ORDER BY RAND()
									LIMIT $max");
									//Execute the query and thus insert the movie
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
						Filter an existing movie from the movie table
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
									$sql =
									'SELECT movie_detail_view.*
									FROM movie_detail_view '
									.$this->sqlBuildConditionalClause($condition, 'AND');
									$smt = $this->dbConn->prepare($sql);
									//Execute the query and thus insert the movie
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
						Adds a movie to the movie table
						@param: $movie is an associative array of movie details
						@return: last-insert-id if successful and 0 (FALSE) otherwise
						*/
						public function movieAdd($movie) {
							$result = null;
							$this->dbError = null; //reset the error message before any execution
							if ($this->dbConn != null) {
								//Try and insert the movie, if there is a DB exception return
								//the error message to the caller.
								try {
									//Make a prepared query so that we can use data binding and avoid SQL injections.
									$smt = $this->dbConn->prepare('INSERT INTO movie
										(`title`, `tagline`, `plot`, `thumbpath`, `director_id`,
											`studio_id`, `genre_id`, `classification`, `rental_period`, `year`,
											`DVD_rental_price`, `DVD_purchase_price`, `numDVD`, `numDVDout`,
											`BluRay_rental_price`, `BluRay_purchase_price`, `numBluRay`, `numBluRayOut`)
											VALUES
											(:title, :tagline, :plot, :thumbpath, :director_id,
												:studio_id, :genre_id, :classification, :rental_period, :year,
												:DVD_rental_price, :DVD_purchase_price, :numDVD, :numDVDout,
												:BluRay_rental_price, :BluRay_purchase_price, :numBluRay, :numBluRayOut)');

												//Bind the data from the form to the query variables.
												//Doing it this way means PDO sanitises the input which prevents SQL injection.
												$smt->bindParam(':title', $movie['title'], PDO::PARAM_STR);
												$smt->bindParam(':tagline', $movie['tagline'], PDO::PARAM_STR);
												$smt->bindParam(':plot', $movie['plot'], PDO::PARAM_STR);
												$smt->bindParam(':thumbpath', $movie['thumbpath'], PDO::PARAM_STR);
												$smt->bindParam(':director_id', $movie['director_id'], PDO::PARAM_STR);
												$smt->bindParam(':studio_id', $movie['studio_id'], PDO::PARAM_STR);
												$smt->bindParam(':genre_id', $movie['genre_id'], PDO::PARAM_STR);
												$smt->bindParam(':classification', $movie['classification'], PDO::PARAM_STR);
												$smt->bindParam(':rental_period', $movie['rental_period'], PDO::PARAM_STR);
												$smt->bindParam(':year', $movie['year'], PDO::PARAM_STR);
												$smt->bindParam(':DVD_rental_price', $movie['DVD_rental_price'], PDO::PARAM_STR);
												$smt->bindParam(':DVD_purchase_price', $movie['DVD_purchase_price'], PDO::PARAM_STR);
												$smt->bindParam(':numDVD', $movie['numDVD'], PDO::PARAM_STR);
												$smt->bindParam(':numDVDout', $movie['numDVDout'], PDO::PARAM_STR);
												$smt->bindParam(':BluRay_rental_price', $movie['BluRay_rental_price'], PDO::PARAM_STR);
												$smt->bindParam(':BluRay_purchase_price', $movie['BluRay_purchase_price'], PDO::PARAM_STR);
												$smt->bindParam(':numBluRay', $movie['numBluRay'], PDO::PARAM_STR);
												$smt->bindParam(':numBluRayOut', $movie['numBluRayOut'], PDO::PARAM_STR);
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
									Updates an existing movie in the movies table
									@param: $movie is an associative array of movie details to be updated
									@return: TRUE if successful and FALSE if not
									*/
									function movieUpdate($movie) {
										$result = null;
										$this->dbError = null; //reset the error message before any execution
										if ($this->dbConn != null) {
											//Make a prepared query so that we can use data binding and avoid SQL injections.
											//(modify suit the A2 member table)
											//Try and insert the movie, if there is a DB exception return the error message to the caller.
											try {
												$smt = $this->dbConn->prepare('UPDATE movie SET
													title = :title,
													tagline = :tagline,
													plot = :plot,
													thumbpath = :thumbpath,
													director_id = :director_id,
													studio_id = :studio_id,
													genre_id = :genre_id,
													classification = :classification,
													rental_period = :rental_period,
													year = :year,
													DVD_rental_price = :DVD_rental_price,
													DVD_purchase_price = :DVD_purchase_price,
													numDVD = :numDVD,
													numDVDout = :numDVDout,
													BluRay_rental_price = :BluRay_rental_price,
													BluRay_purchase_price = :BluRay_purchase_price,
													numBluRay = :numBluRay,
													numBluRayOut = :numBluRayOut
													WHERE movie_id = :movie_id');
													//Bind the data from the form to the query variables.
													//Doing it this way means PDO sanitises the input which prevents SQL injection.
													$smt->bindParam(':title', $movie['title'], PDO::PARAM_STR);
													$smt->bindParam(':tagline', $movie['tagline'], PDO::PARAM_STR);
													$smt->bindParam(':plot', $movie['plot'], PDO::PARAM_STR);
													$smt->bindParam(':thumbpath', $movie['thumbpath'], PDO::PARAM_STR);
													$smt->bindParam(':director_id', $movie['director_id'], PDO::PARAM_STR);
													$smt->bindParam(':studio_id', $movie['studio_id'], PDO::PARAM_STR);
													$smt->bindParam(':genre_id', $movie['genre'], PDO::PARAM_STR);
													$smt->bindParam(':classification', $movie['classification'], PDO::PARAM_STR);
													$smt->bindParam(':rental_period', $movie['rental_period'], PDO::PARAM_STR);
													$smt->bindParam(':year', $movie['year'], PDO::PARAM_STR);
													$smt->bindParam(':DVD_rental_price', $movie['DVD_rental_price'], PDO::PARAM_STR);
													$smt->bindParam(':DVD_purchase_price', $movie['DVD_purchase_price'], PDO::PARAM_STR);
													$smt->bindParam(':numDVD', $movie['numDVD'], PDO::PARAM_STR);
													$smt->bindParam(':numDVDout', $movie['numDVDout'], PDO::PARAM_STR);
													$smt->bindParam(':BluRay_rental_price', $movie['BluRay_rental_price'], PDO::PARAM_STR);
													$smt->bindParam(':BluRay_purchase_price', $movie['BluRay_purchase_price'], PDO::PARAM_STR);
													$smt->bindParam(':numBluRay', $movie['numBluRay'], PDO::PARAM_STR);
													$smt->bindParam(':numBluRayOut', $movie['numBluRayOut'], PDO::PARAM_STR);
													$smt->bindParam(':movie_id', $movie['movie_id'], PDO::PARAM_STR);
													//Execute the query and thus insert the movie
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
														(`surname`, `other_name`, `contact_method`, `email`, `mobile`,
															`landline`, `magazine`, `street`, `suburb`, `postcode`, `username`, `password`,
															`occupation`, `join_date`)
															VALUES
															( :surname, :other_name, :contact_method, :email, :mobile,
																:landline, :magazine, :street, :suburb, :postcode, :username, :password,
																:occupation, :join_date)');

																//Bind the data from the form to the query variables.
																//Doing it this way means PDO sanitises the input which prevents SQL injection.
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
																	password = :numDpasswordVD,
																	occupation = :occupation
																	WHERE member_id = :member_id');

																	//Bind the data from the form to the query variables.
																	//Doing it this way means PDO sanitises the input which prevents SQL injection.
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
																	$smt->bindParam(':password', $member['password'], PDO::PARAM_STR);
																	$smt->bindParam(':join_date', $member['join_date'], PDO::PARAM_STR);
																	$smt->bindParam(':member_id', $movie['member_id'], PDO::PARAM_STR);

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

														/*
														Deletes all existing member with member_id in the $memberids list from the member table
														@params: member_id is an array of member ids to be deleted
														@return: the number of members have been deleted from database
														*/
														public function memberDeleteById($memberids) {
															$result = null;
															$this->dbError = null; //reset the error message before any execution
															if ($this->dbConn != null) {
																try {
																	//stringnify the list of movieids with comma separated
																	$$memberid_string = implode(",", $memberids);
																	//sql to delete a movie based on given params
																	$sql = "DELETE FROM member WHERE member_id IN ($$memberid_string)";
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

														/*
														Deletes all existing movies with movie_id in the $movieids list from the movies table
														@params: $movie_id is an array of movie ids to be deleted
														@return: the number of movies have been deleted from database
														*/
														public function movieDeleteById($movieids) {
															$result = null;
															$this->dbError = null; //reset the error message before any execution
															if ($this->dbConn != null) {
																try {
																	//stringnify the list of movieids with comma separated
																	$movieid_string = implode(",", $movieids);
																	//sql to delete a movie based on given params
																	$sql = "DELETE FROM movie WHERE movie_id IN ($movieid_string)";
																	//AND movied_id > 4 (hey you cannot delete Bill's and Vinh's movies :D)
																	$sql = "DELETE FROM movie WHERE movie_id IN ($movieid_string) AND (movie_id > 4)";
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

														public function movieDeletePhotoById($movieids) {
															$result = null;
															$this->dbError = null; //reset the error message before any execution
															if ($this->dbConn != null) {
																try {
																	//stringnify the list of movieids with comma separated
																	$movieid_string = implode(",", $movieids);
																	//sql to delete a movie based on given params
																	$sql = "SELECT thumbpath FROM movie WHERE movie_id IN ($movieid_string)";
																	//AND movied_id > 4 (hey you cannot delete Bill's and Vinh's movies :D)
																	//$sql = "SELECT thumbpath FROM movies WHERE movie_id IN ($movieid_string) AND (movie_id > 4)";
																	$smt = $this->dbConn->prepare($sql);
																	//Execute the query
																	$smt->execute();
																	$result = $smt->fetchAll(PDO::FETCH_ASSOC);
																	foreach ($result as $thumbpath) {
																		if (file_exists(_MOVIE_PHOTO_FOLDER_.$thumbpath['thumbpath'])){
																			unlink(_MOVIE_PHOTO_FOLDER_.$thumbpath['thumbpath']);
																		}
																	}
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

														/*
														Select all existing genres from the genre table
														@return: an array of genres with column name as the keys;
														*/
														public function genreSelectAll() {
															$result = null;
															$this->dbError = null; //reset the error message before any execution
															if ($this->dbConn != null) {
																try {
																	//Make a prepared query so that we can use data binding and avoid SQL injections.
																	//(modify suit the A2 member table)
																	$smt = $this->dbConn->prepare('SELECT * FROM genre');
																	//Execute the query and thus insert the movie
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
														Select an existing genre from the genre table
														@param $condition: is an associative array of genre details you want to match
														@return: an array of matched genre
														*/
														public function genreSelect($condition) {
															$result = null;
															$this->dbError = null; //reset the error message before any execution
															if ($this->dbConn != null) {
																try {
																	//Make a prepared query so that we can use data binding and avoid SQL injections.
																	//(modify suit the A2 member table)
																	$sql = 'SELECT * FROM genre '.$this->sqlBuildConditionalClause($condition, 'AND');
																	$smt = $this->dbConn->prepare($sql);
																	//Execute the query and thus insert the movie
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
														Filters existing genre from the genre table based on keyword
														@param $keyword: is an keyword you want to match with the genre name
														@return: an array of matched genres
														*/
														public function genreFilter($keyword) {
															$result = null;
															$this->dbError = null; //reset the error message before any execution
															if ($this->dbConn != null) {
																try {
																	//Make a prepared query so that we can use data binding and avoid SQL injections.
																	//(modify suit the A2 member table)
																	$sql = "SELECT * FROM genre WHERE genre_name LIKE '$keyword%'";
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
														Select all existing director from the directors table
														@return: an array of director with column name as the keys;
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
														Select an existing director from the directors table
														@param $keyword: a keyword to match the director name
														@return: an array of matched directors
														*/
														public function directorSelect($condition) {
															$result = null;
															$this->dbError = null; //reset the error message before any execution
															if ($this->dbConn != null) {
																try {
																	//Make a prepared query so that we can use data binding and avoid SQL injections.
																	$sql = "SELECT * FROM director ".$this->sqlBuildConditionalClause($condition, 'AND');
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
														Filter existing directors from the directors table
														@param $keyword: a keyword to match the director name
														@return: an array of matched directors
														*/
														public function directorFilter($keyword) {
															$result = null;
															$this->dbError = null; //reset the error message before any execution
															if ($this->dbConn != null) {
																try {
																	//Make a prepared query so that we can use data binding and avoid SQL injections.
																	//(modify suit the A2 member table)
																	$sql = "SELECT * FROM director WHERE director_name LIKE '$keyword%'";
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
														Adds a director to the directors table
														@param: $director is an associative array of director details
														@return: last-insert-id if successful and 0 (FALSE) otherwise
														*/
														public function directorAdd($director) {
															$result = null;
															$this->dbError = null; //reset the error message before any execution
															if ($this->dbConn != null) {
																try {
																	//Make a prepared query so that we can use data binding and avoid SQL injections.
																	$smt = $this->dbConn->prepare('INSERT INTO director (director_name) VALUES (:director_name)');
																	//Bind the data from the form to the query variables.
																	//Doing it this way means PDO sanitises the input which prevents SQL injection.
																	$smt->bindParam(':director_name', $director['director_name'], PDO::PARAM_STR);
																	//Execute the query
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
														Select all existing studio from the studio table
														@return: an array of studio with column name as the keys;
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
														Select an existing studio from the studio table
														@param $keyword: a keyword to match the studio name
														@return: an array of matched studio types
														*/
														public function studioSelect($condition) {
															$result = null;
															$this->dbError = null; //reset the error message before any execution
															if ($this->dbConn != null) {
																try {
																	//Make a prepared query so that we can use data binding and avoid SQL injections.
																	$sql = "SELECT * FROM studio ".$this->sqlBuildConditionalClause($condition, 'AND');
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
														Filter existing studio from the studio table based on keyword
														@param $keyword: is an keyword you want to match with the studio name
														@return: an array of matched studio
														*/
														public function studioFilter($keyword) {
															$result = null;
															$this->dbError = null; //reset the error message before any execution
															if ($this->dbConn != null) {
																try {
																	//Make a prepared query so that we can use data binding and avoid SQL injections.
																	//(modify suit the A2 member table)
																	$sql = "SELECT * FROM studio WHERE studio_name LIKE '$keyword%'";
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
														Adds a studio to the studio table
														@param: $studio is an associative array of studio details
														@return: last-insert-id if successful and 0 (FALSE) otherwise
														*/
														public function studioAdd($studio) {
															$result = null;
															$this->dbError = null; //reset the error message before any execution
															if ($this->dbConn != null) {
																try {
																	//Make a prepared query so that we can use data binding and avoid SQL injections.
																	$smt = $this->dbConn->prepare('INSERT INTO studio (studio_name) VALUES (:studio_name)');

																	//Bind the data from the form to the query variables.
																	//Doing it this way means PDO sanitises the input which prevents SQL injection.
																	$smt->bindParam(':studio_name', $studio['studio_name'], PDO::PARAM_STR);

																	//Execute the query
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
													}


													/*----------------------------------------------------------------------------------------------
													TEST FUNCTIONS
													----------------------------------------------------------------------------------------------*/
													//Your task: implement the test function to test each function in this dbAdapter
													/*
													Tests database functions
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
														$genre = array(
															'genre_id' => 13,
															'genre_name' => 'New genre 1'
														);

														$director = array(
															'director_id' => 53,
															'director_name' => 'New director 1'
														);
														$studio = array(
															'studio_id' => 42,
															'studio_name' => 'New studio'
														);
														if ($result != null)
														print_r($result);
														else
														echo $dbAdapter->lastError();
														$dbAdapter->dbClose();
													}
													?>
