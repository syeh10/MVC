<?php
/*-------------------------------------------------------------------------------------------------
@Module: movie_zone_model.php
This server-side module provides all required functionality i.e. to select, update, delete movies

@Author: Vinh Bui (vinh.bui@scu.edu.au)
@Modified by: Shu Wei, Yeh (s.yeh.10@student.scu.edu.au)
@Date: 19/09/2017
--------------------------------------------------------------------------------------------------*/
require_once('movie_zone_config.php');

class MovieZoneModel {
	private $error;
	private $dbAdapter;

	/* Add initialization code here
	*/
	public function __construct() {
		$this->dbAdapter = new DBAdaper(DB_CONNECTION_STRING, DB_USER, DB_PASS);
		/* uncomment to create the database tables for the first time
		$this->dbAdapter->dbOpen();
		$this->dbAdapter->dbCreate();
		$this->dbAdapter->dbClose();
		*/
	}

	/* Add code to free any unused resource
	*/
	public function __destruct() {
		$this->dbAdapter->dbClose();
	}

	/*Returns last error
	*/
	public function getError() {
		return $this->error;
	}

	/*
	Selects all movies from the database
	*/
	public function selectAllMovies() {
		$this->dbAdapter->dbOpen();
		$result = $this->dbAdapter->movieSelectAll();
		$this->dbAdapter->dbClose();
		$this->error = $this->dbAdapter->lastError();

		return $result;
	}

	/*
	Selects all movies from the database
	*/
	public function selectNewMovies() {
		$this->dbAdapter->dbOpen();
		$result = $this->dbAdapter->movieSelectNew();
		$this->dbAdapter->dbClose();
		$this->error = $this->dbAdapter->lastError();

		return $result;
	}

	/*
	Selects all movies from the database
	*/
	public function selectAvailableMovies() {
		$this->dbAdapter->dbOpen();
		$result = $this->dbAdapter->movieSelectAvailable();
		$this->dbAdapter->dbClose();
		$this->error = $this->dbAdapter->lastError();

		return $result;
	}

	/*Filter movies from the database
	*/
	public function filterMovies($condition) {
		$this->dbAdapter->dbOpen();
		$result = $this->dbAdapter->movieFilter($condition);
		$this->dbAdapter->dbClose();
		$this->error = $this->dbAdapter->lastError();

		return $result;
	}

	/*
	Selects randomly a $max number of movies from the database
	*/
	public function selectRandomMovies($max) {
		$this->dbAdapter->dbOpen();
		$result = $this->dbAdapter->movieSelectRandom($max);
		$this->dbAdapter->dbClose();
		$this->error = $this->dbAdapter->lastError();

		return $result;
	}

	/*
	Return the list of genres
	*/
	public function selectAllGenres() {
		$this->dbAdapter->dbOpen();
		$result = $this->dbAdapter->genreSelectAll();
		$this->dbAdapter->dbClose();
		$this->error = $this->dbAdapter->lastError();

		return $result;
	}

	/*
	Return the list of directors
	*/
	public function selectAllDirectors() {
		$this->dbAdapter->dbOpen();
		$result = $this->dbAdapter->directorSelectAll();
		$this->dbAdapter->dbClose();
		$this->error = $this->dbAdapter->lastError();

		return $result;
	}

	/*
	Return the list of studio
	*/
	public function selectAllStudios() {
		$this->dbAdapter->dbOpen();
		$result = $this->dbAdapter->studioSelectAll();
		$this->dbAdapter->dbClose();
		$this->error = $this->dbAdapter->lastError();

		return $result;
	}

	/*
	Add a new member to the database
	*/
	public function addMember($memberdata){
		$result = null;
		$this->error = null; //reset the error first
		/*begin database transaction so we can rollback if error
		since the task involves a number of related database operation
		use transaction ensures the database integrity.
		*/
		$this->dbAdapter->dbOpen();
		$dbConn = $this->dbAdapter->getDbConnection();
		$dbConn->beginTransaction();
		/*
		insert the member data to member table.
		*/
		$memberid = $this->dbAdapter->memberAdd($memberdata);

		/*then save the uploaded member photo with filename is movie+movieid
		*/
		if ($memberid != null) {
			//update the member record
			$memberdata['member_id'] = $memberid;
			$result = $this->dbAdapter->memberUpdate($memberdata);
		} else {
			$result == null;
		}
		//check the result if all successful then commit the transaction otherwise we rollback
		if ($result != null) {
			$dbConn->commit();
		} else {
			$dbConn->rollback();
			$this->error = $this->dbAdapter->lastError();
		}
		$this->dbAdapter->dbClose();
		return $result;
	}

	/*
	Update member data in the database
	*/
	public function updateMember(){
		$result = null;
		$this->error = null; //reset the error first

		/*begin database transaction so we can rollback if error
		since the task involves a number of related database operation
		use transaction ensures the database integrity.
		*/
		$this->dbAdapter->dbOpen();
		$dbConn = $this->dbAdapter->getDbConnection();
		$dbConn->beginTransaction();
		//update the member record
		$result = $this->dbAdapter->memberUpdate($memberdata);
		//check the result if all successful then commit the transaction otherwise we rollback
		if ($result != null) {
			$dbConn->commit();
		} else {
			$dbConn->rollback();
			$this->error = $this->dbAdapter->lastError();
		}
		$this->dbAdapter->dbClose();
		return $result;
	}
	/*
	Authenticates the admin user.
	*/
	function memberLogin($user) {
		if ($memberdata['username'] = $username && $memberdata['password'] = $password) {
			$this->error = ERR_SUCCESS;
			return true;
		} else {
			$this->error = ERR_AUTHENTICATION;
			return false;
		}
	}
}
?>
