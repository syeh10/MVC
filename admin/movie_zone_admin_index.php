<?php
/*-------------------------------------------------------------------------------------------------
@Module: movie_zone_admin_index.php
This server-side module provides main UI for the application (admin part)

@Author: Vinh Bui (vinh.bui@scu.edu.au)
@Modified by: Shu Wei, Yeh (s.yeh.10@student.scu.edu.au)
@Date: 19/09/2017
--------------------------------------------------------------------------------------------------*/
require_once('movie_zone_admin_main.php');
?>

<html>
<head>
	<link rel="stylesheet" type="text/css" href="css/movie_zone_admin.css">
	<link rel="stylesheet" type="text/css" href="css/movie_zone_form.css">
	<script src="js/ajax.js"></script>
	<script src="js/movie_zone_admin.js"></script>
</head>

<body>
	<div id="id_container">
		<header>
			<h1>MOVIE ZONE ADMINISTRATOR</h1>
			<h2><?php echo "(Logon as ".$_SESSION['authorised'].")"?></h2>
		</header>
		<!-- left navigation area -->
		<div id="id_left">
			<!-- load the navigation panel by embedding php code -->
			<?php $controller->loadLeftNavPanel()?>
		</div>
		<!-- right area -->
		<div id="id_right">
			<!-- top navigation area -->
			<div id="id_topnav">
				<!-- the top navigation panel is loaded on demand using Ajax (see js code) -->
			</div>
			<div id="id_content">
			</div>
		</div>
		<!-- footer area -->
		<footer>Copyright &copy; WebDev-II (s.yeh.10@student.scu.edu.au)
			<script type="text/javascript">
			document.write(document.lastModified);
			</script>
		</footer>
	</div>
</body>
</html>
