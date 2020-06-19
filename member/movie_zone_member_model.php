<?php
/*-------------------------------------------------------------------------------------------------
@Module: movie_zone_member_model.php
This server-side module provides all required functionality i.e. to select, update, delete movies

@Author: Vinh Bui (vinh.bui@scu.edu.au)
@Modified by: Shu Wei, Yeh (s.yeh.10@student.scu.edu.au)
@Date: 19/09/2017
--------------------------------------------------------------------------------------------------*/
require_once('movie_zone_member_config.php');

class MovieZoneMemberModel {
	private $error;
	private $dbAdapter;

	/* Add initialization code here
	*/
	public function __construct() {
		$this->dbAdapter = new DBAdaper(DB_CONNECTION_STRING, DB_USER, DB_PASS);
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
	Authenticates the member user.
	*/
	public function memberLogin($user) {
		if ($user['password']) {
			$this->error = ERR_SUCCESS;
			return true;
		} else {
			$this->error = ERR_AUTHENTICATION;
			return false;
		}
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

	/*Add a new movie to the database
	*/
	public function addMovie($moviedata) {
		$result = null;
		$this->error = null; //reset the error first

		/*begin database transaction so we can rollback if error
		since the task involves a number of related database operation
		use transaction ensures the database integrity.
		*/
		$this->dbAdapter->dbOpen();
		$dbConn = $this->dbAdapter->getDbConnection();
		$dbConn->beginTransaction();

		/*first, get the id of the director from its name or create a new director
		*/
		$director = array(
			'director_name' => $moviedata['director']
		);
		$result = $this->dbAdapter->directorSelect($director);
		//assign director id to the moviedata in place of the director name
		if ($result == null) {
			$moviedata['director'] = $this->dbAdapter->directorAdd($director);
		} else {
			$moviedata['director'] = $result[0]['director_id'];
		}

		/*next, get the id of the studio or create a new studio
		*/
		$studio = array(
			'studio_name' => $moviedata['studio']
		);
		$result = $this->dbAdapter->studioSelect($studio);
		if ($result == null) {
			$moviedata['studio'] = $this->dbAdapter->studioAdd($studio);
		} else {
			$moviedata['studio'] = $result[0]['studio_id'];
		}

		//print_r($moviedata);
		/*then insert the movie data to movie table. the result is the last insert id,
		which is used to name the movie photo file
		*/
		$movieid = $this->dbAdapter->movieAdd($moviedata);

		/*then save the uploaded movie photo with filename is movie+movieid
		*/
		if ($movieid != null) {
			//save the photo and return the filename
			$thumbpath_file = $this->saveMoviePhoto('photo_loader', _MOVIE_PHOTO_FOLDER_, 'movie'.$movieid, true);

			/* finally update the movie record with the actual photo file name
			*/
			$moviedata['movie_id'] = $movieid;
			$moviedata['thumbpath'] = $thumbpath_file;
			$result = $this->dbAdapter->movieUpdate($moviedata);
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

	/*Update movie data in the database
	*/
	public function updateMovie($moviedata) {
		$result = null;
		$this->error = null; //reset the error first

		/*begin database transaction so we can rollback if error
		since the task involves a number of related database operation
		use transaction ensures the database integrity.
		*/
		$this->dbAdapter->dbOpen();
		$dbConn = $this->dbAdapter->getDbConnection();
		$dbConn->beginTransaction();

		/*first, get the name or create a new director
		*/
		$director = array(
			'director_name' => $moviedata['director']
		);
		$result = $this->dbAdapter->directorSelect($director);
		//assign director name to the moviedata
		if ($result == null) {
			$moviedata['director'] = $this->dbAdapter->directorAdd($director);
		} else {
			$moviedata['director'] = $result[0]['director_id'];
		}

		/*next, get the name of the studio or create a new studio
		*/
		$studio = array(
			'studio_name' => $moviedata['studio']
		);
		$result = $this->dbAdapter->studioSelect($studio);
		if ($result == null) {
			$moviedata['studio'] = $this->dbAdapter->studioAdd($studio);
		} else {
			$moviedata['studio'] = $result[0]['studio_id'];
		}

		/*then save the uploaded movie photo with filename is movie+movieid
		*/
		$thumbpath_file = $this->saveMoviePhoto('photo_loader', _MOVIE_PHOTO_FOLDER_, 'movie'.$moviedata['movie_id'], true);
		if ($thumbpath_file != 'default.jpg')
		$moviedata['thumbpath'] = $thumbpath_file;

		/* finally update the movie record with the actual photo file name
		*/
		$result = $this->dbAdapter->movieUpdate($moviedata);

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
	public function updateMember($memberdata) {
		$result = null;
		//reset the error first
		$this->error = null;
		//begin database transaction so we can rollback if error
		//since the task involves a number of related database operation
		//use transaction ensures the database integrity.
		$this->dbAdapter->dbOpen();
		$dbConn = $this->dbAdapter->getDbConnection();
		$dbConn->beginTransaction();
		//first, get the name or create a new member
		$member = array(
			'member_id' => $moviedata['username']
		);
		$result = $this->dbAdapter->memberSelect($member);
		//assign username name to the moviedata
		if ($result == null) {
			$moviedata['username'] = $this->dbAdapter->memberAdd($member);
		} else {
			$moviedata['username'] = $result[0]['member_id'];
		}
		//finally update the member record
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
	Selects all movies from the database
	*/
	public function selectAllMovies() {
		$this->error = null; //reset the error first
		$this->dbAdapter->dbOpen();
		$result = $this->dbAdapter->movieSelectAll();
		$this->dbAdapter->dbClose();
		if ($result == null)
		$this->error = $this->dbAdapter->lastError();

		return $result;
	}

	/*Filter movies from the database
	*/
	public function filterMovies($condition) {
		$this->error = null; //reset the error first
		$this->dbAdapter->dbOpen();
		$result = $this->dbAdapter->movieFilter($condition);
		$this->dbAdapter->dbClose();
		if ($result == null)
		$this->error = $this->dbAdapter->lastError();

		return $result;
	}

	/*Delete all movies with given ids*/
	public function deleteMoviesById($movieids) {
		$this->error = null; //reset the error first
		$this->dbAdapter->dbOpen();
		//first delete movie photos
		$result = $this->dbAdapter->movieDeletePhotoById($movieids);
		if ($result != null) {
			//then delete movies from database
			$result = $this->dbAdapter->movieDeleteById($movieids);
		}
		$this->dbAdapter->dbClose();
		if ($result == null)
		$this->error = $this->dbAdapter->lastError();

		return $result;
	}

	/*Delete all members with given ids*/
	public function deleteMemberById($memberids) {
		$this->error = null; //reset the error first
		$this->dbAdapter->dbOpen();
		//first delete movie photos
		$result = $this->dbAdapter->memberDeletePhotoById($memberids);
		if ($result != null) {
			//then delete movies from database
			$result = $this->dbAdapter->memberDeleteById($memberids);
		}
		$this->dbAdapter->dbClose();
		if ($result == null)
		$this->error = $this->dbAdapter->lastError();

		return $result;
	}

	/*
	Return the list of states
	*/
	public function selectAllGenres() {
		$this->error = null; //reset the error first
		$this->dbAdapter->dbOpen();
		$result = $this->dbAdapter->genreSelectAll();
		$this->dbAdapter->dbClose();
		if ($result == null)
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
	Return the list of directors
	*/
	public function selectAllDirectors() {
		$this->error = null; //reset the error first
		$this->dbAdapter->dbOpen();
		$result = $this->dbAdapter->directorSelectAll();
		$this->dbAdapter->dbClose();
		if ($result == null)
		$this->error = $this->dbAdapter->lastError();

		return $result;
	}

	/*
	Return the list of directors that match the serach key
	*/
	public function filterDirectors($keyword) {
		$this->error = null; //reset the error first
		$this->dbAdapter->dbOpen();
		$result = $this->dbAdapter->directorFilter($keyword);
		$this->dbAdapter->dbClose();
		if ($result == null)
		$this->error = $this->dbAdapter->lastError();

		return $result;
	}

	/*Return the list of studiotypes
	*/
	public function selectAllStudios() {
		$this->error = null; //reset the error first
		$this->dbAdapter->dbOpen();
		$result = $this->dbAdapter->studioSelectAll();
		$this->dbAdapter->dbClose();
		if ($result == null)
		$this->error = $this->dbAdapter->lastError();

		return $result;
	}

	/*Return the list of studio that match the serach key
	*/
	public function filterStudios($keyword) {
		$this->error = null; //reset the error first
		$this->dbAdapter->dbOpen();
		$result = $this->dbAdapter->studioFilter($keyword);
		$this->dbAdapter->dbClose();
		if ($result == null)
		$this->error = $this->dbAdapter->lastError();

		return $result;
	}
	/*Return the list of genre that match the serach key
	*/
	public function filterGenres($keyword) {
		$this->error = null; //reset the error first
		$this->dbAdapter->dbOpen();
		$result = $this->dbAdapter->genreFilter($keyword);
		$this->dbAdapter->dbClose();
		if ($result == null)
		$this->error = $this->dbAdapter->lastError();

		return $result;
	}

	/* This function receive the upload photo and save it to a directory on server
	@params:
	+uploader: name of the file uploader (to be used with $_FILES
	+target_dir: the directory where the image will be saved
	+file_name: the target image file name
	+override: override the existing file if true
	+returns the destination filename is OK or default.jpg if error
	*/
	private function saveMoviePhoto($uploader, $target_dir, $filename, $override) {
		try {
			// Undefined | Multiple Files | $_FILES Corruption Attack
			// If this request falls under any of them, treat it invalid.
			if (!isset($_FILES[$uploader]['error']) || is_array($_FILES[$uploader]['error'])) {
				throw new RuntimeException('Invalid parameters.');
			}
			// Check $_FILES[$uploader]['error'] value.
			switch ($_FILES[$uploader]['error']) {
				case UPLOAD_ERR_OK:
				break;
				case UPLOAD_ERR_NO_FILE:
				throw new RuntimeException('No file sent.');
				case UPLOAD_ERR_INI_SIZE:
				case UPLOAD_ERR_FORM_SIZE:
				throw new RuntimeException('Exceeded filesize limit.');
				default:
				throw new RuntimeException('Unknown errors.');
			}
			// You should also check filesize here ( > 1 MegaBytes).
			define ("MAX_FILE_SIZE", 10000000);
			if ($_FILES[$uploader]['size'] > MAX_FILE_SIZE) {
				throw new RuntimeException('Exceeded filesize limit.');
			}
			// DO NOT TRUST $_FILES[$uploader]['mime'] VALUE !!
			// Check MIME Type by yourself.
			$finfo = new finfo(FILEINFO_MIME_TYPE);
			if (false === $ext = array_search(
				$finfo->file($_FILES[$uploader]['tmp_name']),
				array(
					'jpg' => 'image/jpeg',
					'png' => 'image/png',
					'gif' => 'image/gif',
				),
				true
			)) {
				throw new RuntimeException('Invalid file format.');
			}
			// Check if file already exists
			$target_file = $target_dir . $filename . "." . $ext; //get the fullpath to the file
			if ((!$override) && (file_exists($target_file))) {
				throw new RuntimeException('File already exists');
			}
			// You should name it uniquely.
			// DO NOT USE $_FILES[$uploader]['name'] WITHOUT ANY VALIDATION !!
			// On this example, obtain safe unique name from its binary data.
			if (!move_uploaded_file($_FILES[$uploader]['tmp_name'], $target_file)) {
				throw new RuntimeException('Failed to move uploaded file.');
			}

			//return null for success
			return $filename . "." . $ext;

		} catch (RuntimeException $e) {
			//we don't throw exception, simply return the default file name
			//return $e->getMessage();
			return 'default.jpg';
		}
	}
}
?>
