<?php
/*dbAdapter: this module acts as the database abstraction layer for the application
@Author: Vinh Bui (vinh.bui@scu.edu.au)
@Modified by: Shu Wei, Yeh (s.yeh.10@student.scu.edu.au)
@Date: 19/09/2017
--------------------------------------------------------------------------------------------------*/

/*
Connection paramaters
*/
require_once('movie_zone_member_config.php');

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
				$sql = "CREATE TABLE IF NOT EXISTS `member` (
					`member_id` int(10) UNSIGNED NOT NULL,
					`surname` varchar(128) NOT NULL,
					`other_name` varchar(128) NOT NULL,
					`contact_method` varchar(10) NOT NULL DEFAULT '',
					`email` varchar(40) DEFAULT '',
					`mobile` varchar(40) DEFAULT '',
					`landline` varchar(40) DEFAULT '',
					`magazine` tinyint(1) NOT NULL DEFAULT '0',
					`street` varchar(40) DEFAULT '',
					`suburb` varchar(40) DEFAULT '',
					`postcode` int(4) DEFAULT NULL,
					`username` varchar(10) NOT NULL DEFAULT '',
					`password` varchar(10) NOT NULL DEFAULT '',
					`occupation` varchar(20) DEFAULT '',
					`join_date` date NOT NULL
				) ENGINE=InnoDB DEFAULT CHARSET=utf8";
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
					Adds a member to the member table
					@param: $member is an associative array of member details
					@return: last-insert-id if successful and 0 (FALSE) otherwise
					*/
					public function memberAdd($member) {
						$result = null;
						$this->dbError = null; //reset the error message before any execution
						if ($this->dbConn != null) {
							//Try and insert the member, if there is a DB exception return
							//the error message to the caller.
							try {
								//Make a prepared query so that we can use data binding and avoid SQL injections.
								$smt = $this->dbConn->prepare('INSERT INTO `member` (`member_id`, `surname`,
									`other_name`, `contact_method`, `email`, `mobile`, `landline`, `magazine`,
									`street`, `suburb`, `postcode`, `username`, `password`, `occupation`, `join_date`)
									VALUES
									(:member_id, :surname, :other_name, :contact_method, :email, :mobile,
										:landline, :magazine, :street, :suburb, :postcode,
										:username, :password, :occupation, :join_date)');

										//Bind the data from the form to the query variables.
										//Doing it this way means PDO sanitises the input which prevents SQL injection.
										$smt->bindParam(':member_id', $movie['timember_idtle'], PDO::PARAM_STR);
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
										$smt->bindParam(':password', $movie['password'], PDO::PARAM_STR);
										$smt->bindParam(':occupation', $member['occupation'], PDO::PARAM_STR);
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
							@param: $member is an associative array of member details to be updated
							@return: TRUE if successful and FALSE if not
							*/
							function memberUpdate($member) {
								$result = null;
								$this->dbError = null; //reset the error message before any execution
								if ($this->dbConn != null) {
									//Make a prepared query so that we can use data binding and avoid SQL injections.
									//(modify suit the A2 member table)
									//Try and insert the member, if there is a DB exception return the error message to the caller.
									try {
										$smt = $this->dbConn->prepare('UPDATE member SET
											-- member_id = :member_id,
											surname = :surname,
											other_name = :other_name,
											contact_method = :contact_method,
											email	= :email,
											mobile = :mobile,
											landline = :landline,
											magazine = :magazine,
											street = :street,
											suburb = :suburb,
											postcode = :postcode,
											-- username = :username,
											password = :password,
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
											$smt->bindParam(':password', $movie['password'], PDO::PARAM_STR);
											$smt->bindParam(':occupation', $member['occupation'], PDO::PARAM_STR);
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
								Deletes all existing movies with movie_id in the $movieids list from the movies table
								@params: $movie_id is an array of movie ids to be deleted
								@return: the number of movies have been deleted from database
								*/
								// public function movieDeleteById($movieids) {
								// 	$result = null;
								// 	$this->dbError = null; //reset the error message before any execution
								// 	if ($this->dbConn != null) {
								// 		try {
								// 			//stringnify the list of movieids with comma separated
								// 			$movieid_string = implode(",", $movieids);
								// 			//sql to delete a movie based on given params
								// 			$sql = "DELETE FROM movie WHERE movie_id IN ($movieid_string)";
								// 			//AND movied_id > 4 (hey you cannot delete Bill's and Vinh's movies :D)
								// 			$sql = "DELETE FROM movie WHERE movie_id IN ($movieid_string) AND (movie_id > 4)";
								// 			$result = $this->dbConn->exec($sql);
								// 		} catch (PDOException $e) {
								// 			//Return the error message to the caller
								// 			$this->dbError = $e->getMessage();
								// 			$result = null;
								// 		}
								// 	} else {
								// 		$this->dbError = MSG_ERR_CONNECTION;
								// 	}
								// 	return $result;
								// }

								// public function movieDeletePhotoById($movieids) {
								// 	$result = null;
								// 	$this->dbError = null; //reset the error message before any execution
								// 	if ($this->dbConn != null) {
								// 		try {
								// 			//stringnify the list of movieids with comma separated
								// 			$movieid_string = implode(",", $movieids);
								// 			//sql to delete a movie based on given params
								// 			$sql = "SELECT thumbpath FROM movies WHERE movie_id IN ($movieid_string)";
								// 			//AND movied_id > 4 (hey you cannot delete Bill's and Vinh's movies :D)
								// 			//$sql = "SELECT thumbpath FROM movies WHERE movie_id IN ($movieid_string) AND (movie_id > 4)";
								// 			$smt = $this->dbConn->prepare($sql);
								// 			//Execute the query
								// 			$smt->execute();
								// 			$result = $smt->fetchAll(PDO::FETCH_ASSOC);
								// 			foreach ($result as $thumbpath) {
								// 				if (file_exists(_MOVIE_PHOTO_FOLDER_.$thumbpath['thumbpath']))
								// 					unlink(_MOVIE_PHOTO_FOLDER_.$thumbpath['thumbpath']);
								// 			}
								// 		} catch (PDOException $e) {
								// 			//Return the error message to the caller
								// 			$this->dbError = $e->getMessage();
								// 			$result = null;
								// 		}
								// 	} else {
								// 		$this->dbError = MSG_ERR_CONNECTION;
								// 	}
								//
								// 	return $result;
								// }
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
								$member = array(
									`member_id` =>'2',
									`surname` =>'Fire',
									`other_name` =>'Archaeologist',
									`contact_method` =>'mobile',
									`email` =>'bill.smart@scu.edu.au',
									`mobile` =>'0448 924 574',
									`landline` =>'(07) 77777777',
									`magazine` =>1,
									`street` =>'A3.05 Southern Cross University',
									`suburb` =>'Beachside, NSW',
									`postcode` =>2018,
									`username` =>'smart',
									`password` =>'Home',
									`occupation` =>'Education',
									`join_date` =>'2014-06-21',
								);

								$dbAdapter->dbOpen();
								$dbAdapter->dbCreate();
								$genre = array(
									'member_id' => 13,
									'surname' => 'New 1'
								);
								if ($result != null)
								print_r($result);
								else
								echo $dbAdapter->lastError();
								$dbAdapter->dbClose();
							}
							?>
