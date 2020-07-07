<?php
/*
We set the correspondence between all URLs and the Controller through config php.
*/
//modify suit your A2 database)
// // DEVELOPMENT
// define ('DB_CONNECTION_STRING', "mysql:host=localhost;dbname=confidential");
define ('DB_CONNECTION_STRING', "mysql:host=confidential;dbname=confidential");
define ('DB_USER', "confidential");
define ('DB_PASS', "confidential");
define ('MSG_ERR_CONNECTION', "Open connection to the database first");
//maximum number of random movies will be shown
define ('MAX_RANDOM_MOVIES', 4);
//the folder where movie photos are stored
define ('_MOVIE_PHOTO_FOLDER_', "photos/");
//request command messages for client-server communication using AJAX
define ('CMD_REQUEST','request'); //the key to access submitted command via POST or GET
define ('CMD_MOVIE_SELECT_RANDOM', 'cmd_movie_select_random');
// Search
define ('CMD_MOVIE_FILTER', 'cmd_movie_filter'); //filter movies by submitted parameters
define ('CMD_MOVIE_SELECT_ALL', 'cmd_movie_select_all');
define ('CMD_MOVIE_SELECT_NEW', 'cmd_movie_new_all');
define ('CMD_CHECK_AVAILABLE', 'cmd_check_available');
define ('CMD_SHOW_DIRECTOR_TOP_NAV', 'cmd_show_director_top_nav'); //create and show top navigation panel
define ('CMD_SHOW_STUDIO_TOP_NAV', 'cmd_show_studio_top_nav'); //create and show top navigation panel
define ('CMD_SHOW_GENRE_TOP_NAV', 'cmd_show_genre_top_nav'); //create and show top navigation panel
//Register
define ('CMD_MEMBER_ADD_FORM', 'cmd_member_add_form'); //show form to add a member
define ('CMD_MEMBER_ADD', 'cmd_member_add'); //add a member
define ('CMD_ONLINE_REQUEST', 'cmd_online_request');
define ('CMD_ONLINE_SUPPORT', 'cmd_online_support');
define ('CMD_CONTACT', 'cmd_contact'); //show form to add a member
define ('CMD_TECHZONE', 'cmd_techzone'); //show form to add a member
define ('CMD_MEMBER_LOGIN', 'cmd_member_login');
define ('CMD_ADMIN_LOGIN', 'cmd_admin_login');

//define error messages
define ('errSuccess', 'SUCCESS'); //no error, command is successfully executed
define ('errAdminRequired', "Login as admin to perform this task");
define ('ERR_AUTHENTICATION', "Wrong username or password");

require_once('movie_zone_dba.php');
require_once('movie_zone_model.php');
require_once('movie_zone_view.php');
require_once('movie_zone_controller.php');

?>
