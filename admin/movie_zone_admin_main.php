<?php
/*-------------------------------------------------------------------------------------------------
@Module: movie_zone_admin_main.php
This server-side main module interacts with UI to process user's requests

@Author: Vinh Bui (vinh.bui@scu.edu.au)
@Modified by: Shu Wei, Yeh (s.yeh.10@student.scu.edu.au)
@Date: 19/09/2017
--------------------------------------------------------------------------------------------------*/
require_once('movie_zone_admin_config.php');

/*initialize the model and view
*/
$model = new MovieZoneAdminModel();
$view = new MovieZoneAdminView();
$controller = new MovieZoneAdminController($model, $view);
/*interacts with UI via GET/POST methods and process all requests
*/
if (!empty($_REQUEST[CMD_REQUEST])) { //check if there is a request to process
	$request = $_REQUEST[CMD_REQUEST];
	$controller->processRequest($request);
}
?>
