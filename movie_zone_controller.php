<?php
/*-------------------------------------------------------------------------------------------------
@Module: movie_zone_controller.php
This server-side module provides all required functionality to format and display movies in html

@Author: Vinh Bui (vinh.bui@scu.edu.au)
@Modified by: Shu Wei, Yeh (s.yeh.10@student.scu.edu.au)
@Date: 19/09/2017
--------------------------------------------------------------------------------------------------*/
require_once('movie_zone_config.php');

class MovieZoneController {
	//global varibables for this class
	private $model;
	private $view;

	/*Class contructor
	*/
	public function __construct($model, $view) {
		//local variables for this method
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
	Notifies client machine about the outcome of operations
	This is used for M2M communication when Ajax is used.
	*/
	private function notifyClient($code) {
		/*simply print out the notification code for now
		but in the future JSON can be used to encode the
		communication protocol between client and server
		*/
		print $code;
	}
	/*
	Notifies client machine about the outcome of operations
	This is used for M2M communication when Ajax is used.
	*/
	private function sendJSONData($data) {
		//using JSON
		header('Content-Type: application/json');
		echo json_encode($data);
	}
	/*
	Loads left navigation panel
	*/
	public function loadLeftNavPanel() {
		$this->view->leftNavPanel();
	}

	/*
	Loads left panel
	*/
	public function loadLeftPanel() {
		$this->view->leftPanel();
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
	Processes user requests and call the corresponding functions
	The request and data are submitted via POST methods
	*/
	public function processRequest($request) {
		switch ($request) {
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
			case CMD_MEMBER_ADD_FORM:
			$this->handleShowMemberAddFormRequest();
			break;
			case CMD_MEMBER_ADD:
			$this->handleMemberAddRequest();
			break;
			case CMD_CONTACT:
			$this->handleShowContactRequest();
			break;
			case CMD_ONLINE_REQUEST:
			$this->handleSubmitRequest();
			break;
			case CMD_ONLINE_SUPPORT:
			$this->handleLinkSupporRequest();
			break;
			case CMD_TECHZONE:
			$this->handleShowTechzoneRequest();
			break;
			default:
			$this->handleSelectRandomMovieRequest();
			break;
		}
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
	Handles select random movies request
	*/
	private function handleSelectRandomMovieRequest() {
		$movies = $this->model->selectRandomMovies(MAX_RANDOM_MOVIES);
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


	/*
	Handles filter movies request
	*/
	private function handleFilterMovieRequest() {
		//local variables for a collection in an array
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
	}

	/*
	Handles edit member request
	*/
	// private function handleMemberEditRequest() {
	// 	$keys = array('member_id', 'surname','other_name', 'contact_method', 'email', 'mobile',
	// 	'landline', 'magazine', 'street', 'suburb', 'postcode',
	// 	'username', 'password', 'occupation');
	// 	//retrive submiteed data
	// 	$memberdata = array();
	// 	foreach ($keys as $key) {
	// 		if (!empty($_REQUEST[$key])) {
	// 			//more server side checking can be done here
	// 			$memberdata[$key] = $_REQUEST[$key];
	// 		} else {
	// 			//check required field
	// 			$this->view->showError($key.' cannot be blank');
	// 			return;
	// 		}
	// 	}
	// 	$result = $this->model->updateMember($memberdata);
	// 	if ($result != null)
	// 	$this->notifyClient(ERR_SUCCESS);
	// 	else {
	// 		$error = $this->model->getError();
	// 		if (!empty($error))
	// 		$this->view->showError($error);
	// 	}
	// }
	/*
	Handles show contact request
	*/
	private function handleShowContactRequest() {
		print file_get_contents('html/contact.html');
	}

	/*
	Handles submit request
	*/
	private function handleSubmitRequest() {
		$to = "s.yeh.10@student.scu.edu.au";
		$subject = "Request";
		$message = "The request is...";
		$from = "someonelse@example.com";
		$headers = "From: $from";
		mail($to,$subject,$message,$headers);
		echo "Mail Sent.";
	}

	/*
	Handles link suppor Request
	*/
	private function handleLinkSupporRequest() {
		$to = "s.yeh.10@student.scu.edu.au";
		$subject = "Support";
		$message = "The needs of support is...";
		$from = "someonelse@example.com";
		$headers = "From: $from";
		mail($to,$subject,$message,$headers);
		echo "Mail Sent.";
	}

	/*
	Handles show techzone request
	*/
	private function handleShowTechzoneRequest() {
		print file_get_contents('html/techzone.html');
	}
	/*
	Handles show member add form request
	*/
	private function handleShowMemberAddFormRequest() {
		print file_get_contents('html/member_add_edit_form.html');
	}
	// /*
	// Handles show member edit form request
	// */
	// private function handleShowMemberEditFormRequest() {
	// 	//$checked_movies is an associtive array with movie ids are the keys
	// 	if (!empty($_REQUEST['member_id'])) { //only if the request is valid
	// 		print file_get_contents('html/member_add_edit_form.html');
	// 	}
	// }
	/*
	Handles admin login request
	*/
	private function handleMemberLoginRequest() {
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
		$result = $this->model->memberLogin($user);
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
	Handles member logout request
	*/
	private function handleMemberLogoutRequest() {
		// Unset all of the session variables.
		$_SESSION = array();

		// If it's desired to kill the session, also delete the session cookie.
		// Note: This will destroy the session, and not just the session data!
		if (ini_get("session.use_cookies"))
		{
			$params = session_get_cookie_params();
			setcookie(
				session_name(), '', time() - 42000,
				$params["path"], $params["domain"],
				$params["secure"], $params["httponly"]);
			}
			// Finally, destroy the session.
			session_destroy();
			//send '_OK_' code to client
			$this->notifyClient(ERR_SUCCESS);
		}

	}
	?>
