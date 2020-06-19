<?php
/*-------------------------------------------------------------------------------------------------
@Module: movie_zone_admin_controller.php
This server-side module provides all required functionality to format and display movies in html

@Author: Vinh Bui (vinh.bui@scu.edu.au)
@Modified by: Shu Wei, Yeh (s.yeh.10@student.scu.edu.au)
@Date: 19/09/2017
--------------------------------------------------------------------------------------------------*/

require_once('movie_zone_admin_config.php');

class MovieZoneAdminController {
	private $model;
	private $view;

	/*Class contructor
	*/
	public function __construct($model, $view) {
		$this->model = $model;
		$this->view = $view;
	}

	/*Class destructor
	*/
	public function __destruct() {
		$this->model = null;
		$this->view = null;
	}

	/*
	Processes user requests and call the corresponding functions
	The request and data are submitted via POST or GET methods
	*/
	public function processRequest($request) {
		switch ($request) {
			case CMD_ADMIN_LOGIN:
			$this->handleAdminLoginRequest();
			break;
			case CMD_ADMIN_LOGOUT:
			$this->handleAdminLogoutRequest();
			break;
			case CMD_SHOW_DIRECTOR_TOP_NAV:
			$this->loadTopNavPanel('directors');
			break;
			case CMD_SHOW_STUDIO_TOP_NAV:
			$this->loadTopNavPanel('studios');
			break;
			case CMD_SHOW_GENRE_TOP_NAV:
			$this->loadTopNavPanel('genres');
			break;
			case CMD_MOVIE_SELECT_ALL:
			$this->handleSelectAllMovieRequest();
			break;
			case CMD_MOVIE_SELECT_RANDOM:
			$this->handleSelectRandomMovieRequest();
			break;
			case CMD_MOVIE_SELECT_NEW:
			$this->handleSelectNewMovieRequest();
			break;
			case CMD_CHECK_AVAILABLE:
			$this->handleCheckAvailableMovieRequest();
			break;
			case CMD_MOVIE_FILTER:
			$this->handleFilterMovieRequest();
			break;
			case CMD_MOVIE_SELECT_BY_ID:
			$this->handleSelectMovieByIdRequest();
			break;
			case CMD_MOVIE_CHECK://Check available movies
			$this->handleMovieCheckRequest();
			break;
			case CMD_MOVIE_ADD_FORM:
			$this->handleShowMovieAddFormRequest();
			break;
			case CMD_MOVIE_ADD:
			$this->handleMovieAddRequest();
			break;
			case CMD_MOVIE_EDIT_FORM:
			$this->handleShowMovieEditFormRequest();
			break;
			case CMD_MOVIE_EDIT:
			$this->handleMovieEditRequest();
			break;
			case CMD_MOVIE_DELETE:
			$this->handleMovieDeleteRequest();
			break;
			case CMD_GENRE_SELECT_ALL:
			$this->handleSelectAllGenresRequest();
			break;
			case CMD_DIRECTOR_FILTER:
			$this->handleDirectorFilterRequest();
			break;
			case CMD_STUDIO_FILTER:
			$this->handleStudioFilterRequest();
			break;
			case CMD_GENRE_FILTER:
			$this->handleGenreFilterRequest();
			break;
			case CMD_MEMBER_ADD_FORM:
			$this->handleShowMemberAddFormRequest();
			break;
			case CMD_INVENTORY_REPORT:
			$this->handleShowInventoryReportRequest();
			break;
			case CMD_MEMBER_ADD:
			$this->handleMemberAddRequest();
			break;
			case CMD_MEMBER_EDIT_FORM:
			$this->handleShowMemberEditFormRequest();
			break;
			case CMD_MEMBER_EDIT:
			$this->handleMemberEditRequest();
			break;
			case CMD_MEMBER_DELETE:
			$this->handleMemberDeleteRequest();
			break;
			case CMD_MEMBER_SELECT_ALL:
			$this->handleMemberAllSelectRequest();
			break;
			case CMD_MEMBER_SELECT_BY_ID:
			$this->handleSelectMemberByIdRequest();
			break;
			default:
			break;
		}
	}

	/*Loads left navigation panel*/
	public function loadLeftNavPanel() {
		$this->view->leftNavPanel();
	}

	/*
	Loads top navigation panel
	*/
	public function loadTopNavPanel($typeOfFilter) {
		$model = null;
		$directors = null;
		$studios = null;
		$genres = null;

		if($typeOfFilter === 'directors'){
			$directors = $this->model->selectAllDirectors();
			$model = $directors;

		} else if ($typeOfFilter === 'studios'){
			$studios = $this->model->selectAllStudios();
			$model = $studios;

		} else {
			$genres = $this->model->selectAllGenres();
			$model = $genres;

		}

		if ($model != null) {
			$this->view->topNavPanel($directors, $studios, $genres);
		} else {
			$error = $this->model->getError();
			if (!empty($error))
			$this->view->showError($error);
		}
	}

	/*
	Handles select new movies request
	*/
	private function handleSelectNewMovieRequest() {
		$movies = $this->model->selectNewMovies();
		if ($movies != null) {
			$this->view->showMovies($movies);
		} else {
			$error = $this->model->getError();
			if (!empty($error))
			$this->view->showError($error);
		}
	}

	/*
	Handles select new movies request
	*/
	private function handleCheckAvailableMovieRequest() {
		$movies = $this->model->selectAvailableMovies();
		if ($movies != null) {
			$this->view->showMovies($movies);
		} else {
			$error = $this->model->getError();
			if (!empty($error))
			$this->view->showError($error);
		}
	}


	// /*Loads top navigation panel*/
	// public function loadTopNavPanel() {
	// 	$directors = $this->model->selectAllDirectors();
	// 	$studios = $this->model->selectAllStudios();
	// 	$genres = $this->model->selectAllGenres();
	// 	if (($directors != null) && ($studios != null) && ($genres != null)) {
	// 		$this->view->topNavPanel($directors, $studios, $genres);
	// 	}
	// 	else {
	// 		$error = $this->model->getError();
	// 		if (!empty($error))
	// 		$this->view->showError($error);
	// 	}
	// }

	/* Notifies client machine about the outcome of operations
	This is used for M2M communication when Ajax is used.
	*/
	private function notifyClient($code) {
		/*simply print out the notification code for now
		but in the future JSON can be used to encode the
		communication protocol between client and server
		*/
		print $code;
	}

	/* Notifies client machine about the outcome of operations
	This is used for M2M communication when Ajax is used.
	*/
	private function sendJSONData($data) {
		//using JSON
		header('Content-Type: application/json');
		echo json_encode($data);
	}

	/*
	Handles add member request to add a new member
	*/
	private function handleMemberAddRequest() {
		$keys = array('surname','other_name', 'contact_method', 'email', 'mobile',
		'landline', 'magazine', 'street', 'suburb', 'postcode',
		'username', 'password', 'occupation', 'join_date');
		//retrive submiteed data
		$memberdata = array();
		foreach ($keys as $key) {
			if (!empty($_REQUEST[$key])) {
				//more server side checking can be done here
				$memberdata[$key] = $_REQUEST[$key];
			}
			else {
				//check required field
				$this->view->showError($key.' cannot be blank');
				return;
			}
		}
		$result = $this->model->addMember($memberdata);
		if ($result != null)
		$this->notifyClient(ERR_SUCCESS);
		else {
			$error = $this->model->getError();
			if (!empty($error))
			$this->view->showError($error);
		}
		$this->view->showMembers($memberdata);
	}

	/*
	Handles admin login request
	*/
	private function handleAdminLoginRequest() {
		//take username and password and perform authentication
		//if successful, initialize the user session
		//echo 'OK';
		$keys = array('username','password');
		//retrive submiteed data
		$user = array();
		foreach ($keys as $key) {
			if (!empty($_REQUEST[$key])) {
				//more server side checking can be done here
				$user[$key] = $_REQUEST[$key];
			} else {
				//check required field
				$this->view->showError($key.' cannot be blank');
				return;
			}
		}

		$result = $this->model->adminLogin($user);

		if ($result) {
			//authorise user with the username to access
			$_SESSION['authorised'] = $user['username'];

			/*and notify the caller about the successful login
			the notification protocol should be predefined so
			the client and server can understand each other
			*/
			$this->notifyClient(ERR_SUCCESS); //send '_OK_' code to client
		} else {
			//not successful show error to user
			$error = $this->model->getError();
			if (!empty($error))
			$this->view->showError($error);
		}
	}


	/*
	Handles admin logout request
	*/
	private function handleAdminLogoutRequest() {
		// Unset all of the session variables.
		$_SESSION = array();

		// If it's desired to kill the session, also delete the session cookie.
		// Note: This will destroy the session, and not just the session data!
		if (ini_get("session.use_cookies")) {
			$params = session_get_cookie_params();
			setcookie(session_name(), '', time() - 42000,
			$params["path"], $params["domain"],
			$params["secure"], $params["httponly"]
		);
	}

	// Finally, destroy the session.
	session_destroy();

	//send '_OK_' code to client
	$this->notifyClient(ERR_SUCCESS);
}

/*
Handles select all movies request
*/
private function handleSelectAllMovieRequest() {
	$movies = $this->model->selectAllMovies();
	if ($movies != null) {
		$this->view->showMovies($movies);
	} else {
		$error = $this->model->getError();
		if (!empty($error))
		$this->view->showError($error);
	}
}

/*
Handles filter movies request
*/
private function handleFilterMovieRequest() {
	$condition = array();
	if (!empty($_REQUEST['director']))
	$condition['director'] = $_REQUEST['director']; //submitted is director name
	if (!empty($_REQUEST['studio']))
	$condition['studio'] = $_REQUEST['studio']; //submitted is studio name
	if (!empty($_REQUEST['genre']))
	$condition['genre'] = $_REQUEST['genre']; //submitted is genre name

	//call the dbAdapter function
	$movies = $this->model->filterMovies($condition);
	if ($movies != null) {
		$this->view->showMovies($movies);
	} else {
		$error = $this->model->getError();
		if (!empty($error))
		$this->view->showError($error);
	}
}

/*Handles select movies request
*/
private function handleSelectMovieByIdRequest() {
	if (!empty($_REQUEST['movie_id'])) {
		$condition = array();
		$condition['movie_id'] = $_REQUEST['movie_id'];

		//call the dbAdapter function
		$movies = $this->model->filterMovies($condition);
		if ($movies != null) {
			$this->sendJSONData($movies);
		}
	}
}

/*
Handles check/uncheck movie request
We use session to remember checked movies
*/
private function handleMovieCheckRequest() {
	//$checked_movies is an associtive array with movie ids are the keys
	if (!empty($_REQUEST['movie_id'])) { //only if the request is valid
		if (empty($_SESSION['checked_movies']))
		$checked_movies = array(); //create new array
		else
		$checked_movies = $_SESSION['checked_movies']; //or retrive it from session storage
		//get the movie id from the request
		$movie_id =  (string)$_REQUEST['movie_id']; //convert to string is important
		if (empty($checked_movies[$movie_id])) { //check if movie_id already exists
			$checked_movies[$movie_id] = 1; //check
		} else {
			unset($checked_movies[$movie_id]); //uncheck by removing the movie id
		}
		//put the array in session so we can access next time
		$_SESSION['checked_movies'] = $checked_movies;
		//notify the client about the check/uncheck
		if (!empty($checked_movies[$movie_id]))
		$this->notifyClient(ERR_SUCCESS); //send _OK_ if checked
		//and send nothing back if unchecked
	}
}

/*
Handles delete movies request
This function read all checked movies (stored in the session) and delete them from database
*/
private function handleMovieDeleteRequest() {
	if (!empty($_SESSION['checked_movies'])) {
		//get the list of movie ids, which are the keys of the checked_movie array
		$movieids = array_keys($_SESSION['checked_movies']);
		//and call the corresponding model function to delete the movies
		$result = $this->model->deleteMoviesById($movieids);
		if ($result == null) {
			$error = $this->model->getError();
			if ($error != null)
			$this->view->showError($error);
			else
			$this->view->showError("You cannot delete movies.
			<button style='padding: 5px 30px' onclick='location.reload();'>OK</button>");
		} else
		$this->notifyClient(ERR_SUCCESS);
		unset($_SESSION['checked_movies']); //clear the checked movies
	} else {
		$this->view->showError("No movies were selected!
		<button style='padding: 5px 30px' onclick='location.reload();'>OK</button>");
	}
}

private function handleMemberDeleteRequest() {
	if (!empty($_SESSION['checked_members'])) {
		//get the list of $member ids, which are the keys of the checked_$member array
		$memberids = array_keys($_SESSION['checked_members']);
		//and call the corresponding model function to delete the movies
		$result = $this->model->deleteMemberById($memberids);
		if ($result == null) {
			$error = $this->model->getError();
			if ($error != null)
			$this->view->showError($error);
			else
			$this->view->showError("You cannot delete members.
			<button style='padding: 5px 30px' onclick='location.reload();'>OK</button>");
		} else
		$this->notifyClient(ERR_SUCCESS);
		unset($_SESSION['checked_members']); //clear the checked movies
	} else {
		$this->view->showError("No members were selected!
		<button style='padding: 5px 30px' onclick='location.reload();'>OK</button>");
	}
}

/*Handles add movie request to add a new movie*/
private function handleMovieAddRequest() {
	$keys = array('title','tagline', 'plot', 'thumbpath', 'director_id',
	'studio_id', 'genre_id', 'classification', 'rental_period', 'year',
	'DVD_rental_price', 'DVD_purchase_price', 'numDVD', 'numDVDout',
	'BluRay_rental_price', 'BluRay_purchase_price', 'numBluRay', 'numBluRayOut');
	//retrive submiteed data
	$moviedata = array();
	foreach ($keys as $key) {
		if (!empty($_REQUEST[$key])) {
			//more server side checking can be done here
			$moviedata[$key] = $_REQUEST[$key];
		} else {
			//check required field
			$this->view->showError($key.' cannot be blank');
			return;
		}
	}

	//we will change it later to actual photo file name if photo upload is OK
	$result = $this->model->addMovie($moviedata);
	if ($result != null)
	$this->notifyClient(ERR_SUCCESS);
	else {
		$error = $this->model->getError();
		if (!empty($error))
		$this->view->showError($error);
	}
}

/*
Handle Movie Edit Request
*/
private function handleMovieEditRequest() {
	// $keys = array('movie_id', 'title', 'tagline');
	$keys = array('movie_id', 'title', 'tagline', 'plot', 'director', 'photo',
	'studio', 'genre', 'classification', 'rental_period', 'year',
	'DVD_rental_price', 'DVD_purchase_price', 'numDVD',
	'BluRay_rental_price', 'BluRay_purchase_price', 'numBluRay');
	//retrive submiteed data
	$moviedata = array();
	foreach ($keys as $key) {
		if (!empty($_REQUEST[$key])) {
			//more server side checking can be done here
			$moviedata[$key] = $_REQUEST[$key];
		} else {
			//check required field
			$this->view->showError($key.' cannot be blank');
			return;
		}
	}
	$result = $this->model->updateMovie($moviedata);
	if ($result != null){
	$this->notifyClient(ERR_SUCCESS);
	}else {
		$error = $this->model->getError();
		if (!empty($error))
		$this->view->showError($error);
	}
}

/*
Handler for show movies add form request
*/
private function handleShowMovieAddFormRequest() {
	print file_get_contents('html/movie_add_edit_form.html');
}

/*
Handler for show movies edit form request
*/
private function handleShowMovieEditFormRequest() {
	//$checked_movies is an associtive array with movie ids are the keys
	if (!empty($_REQUEST['movie_id'])) { //only if the request is valid
		print file_get_contents('html/movie_add_edit_form.html');
	}
}

/*
Handler for show movies edit form request
*/
private function handleShowMemberEditFormRequest() {
	//$checked_movies is an associtive array with movie ids are the keys
	if (!empty($_REQUEST['member_id'])) { //only if the request is valid
		print file_get_contents('html/member_add_edit_form.html');
	}
}

/*
Handles the client request to get all genres from the database
@return: all genres in JSON format
*/
private function handleSelectAllGenresRequest() {
	$genres = $this->model->selectAllGenres();
	if ($genres != null) {
		$this->sendJSONData($genres);
	}
}

/*Handles director filter request and returns matched directors of a given keyword*/
private function handleDirectorFilterRequest() {
	if (!empty($_REQUEST['keyword'])) { //only if the request is valid
		$keyword = $_REQUEST['keyword'];
		$result = $this->model->filterDirectors($keyword);
		if ($result != null)
		$this->view->showDirectorFilterList($result);
	}
}

/*Handles studio filter request and returns matched directors of a given keyword*/
private function handleStudioFilterRequest() {
	if (!empty($_REQUEST['keyword'])) { //only if the request is valid
		$keyword = $_REQUEST['keyword'];
		$result = $this->model->filterStudios($keyword);
		if ($result != null)
		$this->view->showStudioFilterList($result);
	}
}
/*
Handles genre filter request and returns matched genres of a given keyword
*/
private function handleGenreFilterRequest() {
	if (!empty($_REQUEST['keyword'])) { //only if the request is valid
		$keyword = $_REQUEST['keyword'];
		$result = $this->model->filterGenres($keyword);
		if ($result != null)
		$this->view->showGenreFilterList($result);
	}
}

/*
Handles select all members request
*/
private function handleMemberAllSelectRequest () {
	$members = $this->model->selectAllMembers();
	if ($members != null) {
		$this->view->showMembers($members);
	} else {
		$error = $this->model->getError();
		if (!empty($error))
		$this->view->showError($error);
	}
}

/*
Handler for show members add form request
*/
private function handleShowMemberAddFormRequest() {
	print file_get_contents('html/member_add_edit_form.html');
}

/*
Handler for show inventory report of movies
*/
private function handleShowInventoryReportRequest() {
	$movies = $this->model->selectMoviesInventory();
	if ($movies != null) {
		$this->view->showInventory($movies);
	} else {
		$error = $this->model->getError();
		if (!empty($error))
		$this->view->showError($error);
	}
}

}
?>
