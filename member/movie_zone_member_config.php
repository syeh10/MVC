<?php
/*-------------------------------------------------------------------------------------------------
@Module: movie_zone_member_config.php
This server-side module defines all required settings and dependencies for the application

@Author: Vinh Bui (vinh.bui@scu.edu.au)
@Modified by: Shu Wei, Yeh (s.yeh.10@student.scu.edu.au)
@Date: 19/09/2017
--------------------------------------------------------------------------------------------------*/

/*define all required messages and commands for the session checking purpose
*/
//request and login/logout commands
define ('CMD_REQUEST','request'); //the key to access submitted command via POST or GET
define ('CMD_MEMBER_LOGIN', 'cmd_member_login');
define ('CMD_MEMBER_LOGOUT', 'cmd_member_logout');

//error messages
define ('ERR_SUCCESS', '_OK_'); //no error, command is successfully executed
define ('ERR_AUTHENTICATION', "Wrong username or password");

/*Perform session checking, if already logged in then just put user through
otherwise, show login dialog */
$php_version = phpversion();
if (floatval($php_version) >= 5.4) {
	if (session_status() == PHP_SESSION_NONE) {//need the session to start
		session_start();
	}
} else {
	if (session_id() == '') {
		session_start();
	}
}

/*We use 'authorised' keyword to identify if the user hasn't logged in
if the keyword has not been set check if this is the login session then continue
if not simply terminate (a good security practice to check for eligibility
before execute any php code)
*/
if (empty($_SESSION['authorised'])) {
	//no authorisation so check if user is trying to log in
	if (empty($_REQUEST[CMD_REQUEST])||($_REQUEST[CMD_REQUEST] != CMD_MEMBER_LOGIN)) {
		//if no request or request is not login request
		die();
	}
}
/* ... continue the execution otherwise ...
(this is a good security practice to check for the eligibility before executing any code)
*/
/*This is a good practice to define all constants which may be used at different places*/
// define ('DB_CONNECTION_STRING', "mysql:host=localhost;dbname=movie_zone_db");
define ('DB_CONNECTION_STRING', "mysql:host=infotech.scu.edu.au;dbname=syeh10CSC10217Ass1");
define ('DB_USER', "syeh10");
define ('DB_PASS', "22862541");
define ('MSG_ERR_CONNECTION', "Open connection to the database first");
//maximum number of random movies will be shown
define ('MAX_RANDOM_MOVIES', 4);
define ('_MOVIE_PHOTO_FOLDER_', "../photos/");
define ('CMD_MOVIE_SELECT_RANDOM', 'cmd_movie_select_random');
// Search
define ('CMD_MOVIE_FILTER', 'cmd_movie_filter'); //filter movies by submitted parameters
define ('CMD_MOVIE_SELECT_ALL', 'cmd_movie_select_all');
define ('CMD_MOVIE_SELECT_NEW', 'cmd_movie_new_all');
define ('CMD_CHECK_AVAILABLE', 'cmd_check_available');
define ('CMD_SHOW_DIRECTOR_TOP_NAV', 'cmd_show_director_top_nav'); //create and show top navigation panel
define ('CMD_SHOW_STUDIO_TOP_NAV', 'cmd_show_studio_top_nav'); //create and show top navigation panel
define ('CMD_SHOW_GENRE_TOP_NAV', 'cmd_show_genre_top_nav'); //create and show top navigation panel
// CHECK
define ('CMD_MOVIE_CHECK', 'cmd_movie_check'); //mark/unmark movies
define ('CMD_MOVIE_SELECT_BY_ID', 'cmd_movie_select_by_id'); //select movies by id (returns in JSON)
//Member
define ('CMD_MEMBER_EDIT_FORM', 'cmd_member_edit_form'); //show form to edit a movie
define ('CMD_MEMBER_EDIT', 'cmd_member_edit'); //edit a movie
define ('CMD_MEMBER_DELETE', 'cmd_member_delete'); //delete checked movies
//cart
define ('CMD_MOVIE_SELECT', 'cmd_movie_select');
define ('CMD_MOVIE_ADD_TO_CART', 'cmd_movie_add_to_cart');
define ('CMD_MOVIE_REMOVE_FROM_CART', 'cmd_movie_remove_from_cart');
define ('CMD_CONFIRM_BOOK_FORM', 'cmd_movie_remove_from_cart');
define ('CMD_BOOK', 'cmd_book');
define ('CMD_SHOW_BOOKED_REPORT', 'cmd_show_booked_report');

define ('CMD_GENRE_SELECT_ALL', 'cmd_genre_select_all');
define ('CMD_DIRECTOR_FILTER', 'cmd_director_filter'); //filter movie director
define ('CMD_STUDIO_FILTER', 'cmd_studio_filter'); //filter movie studio
define ('CMD_GENRE_FILTER', 'cmd_genre_filter'); //filter movie genre
//application modules
require_once('movie_zone_member_dba.php');
require_once('movie_zone_member_model.php');
require_once('movie_zone_member_view.php');
require_once('movie_zone_member_controller.php');
?>
