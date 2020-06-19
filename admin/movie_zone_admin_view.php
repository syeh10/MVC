<?php
/*-------------------------------------------------------------------------------------------------
@Module: movie_zone_admin_view.php
This server-side module provides all required functionality to format and display movies in html

@Author: Vinh Bui (vinh.bui@scu.edu.au)
@Modified by: Shu Wei, Yeh (s.yeh.10@student.scu.edu.au)
@Date: 19/09/2017
--------------------------------------------------------------------------------------------------*/
require_once('movie_zone_admin_config.php');

class MovieZoneAdminView {
	/*Class contructor: performs any initialization
	*/
	public function __construct() {
	}

	/*Class destructor: performs any deinitialiation
	*/
	public function __destruct() {
	}

	/*
	Creates left navigation panel
	*/
	public function leftNavPanel() {
		print file_get_contents('html/leftnav.html');
	}

	/*
	Creates top navigation panel
	*/
	public function topNavPanel($directors, $studios, $genres) {
		// Opening nav-div
		print "<div style='color: #0e5968; float:left;'>";

		if($directors != null){
			// DIRECTOR FILTER
			print "
			<div class='topnav'>
			<label for='director'><b>Director:</b></label><br>
			<select name='director' id='id_director'>
			<option value='all'>Select all</option>
			";
			//------------------
			foreach ($directors as $director) {
				print "<option value='".$director['director_name']."'>".$director['director_name']."</option>";
			}
			print "
			</select>
			<button onclick='movieFilterChanged();'>Search</button>
			</div>";
		}


		if($studios != null){
			// STUDIO FILTER
			print "
			<div class='topnav'>
			<label for='studio'><b>Studio:</b></label><br>
			<select name='studio' id='id_studio'>
			<option value='all'>Select all</option>
			";
			//------------------
			foreach ($studios as $studio) {
				print "<option value='".$studio['studio_name']."'>".$studio['studio_name']."</option>";
			}
			print "
			</select>
			<button onclick='movieFilterChanged();'>Search</button>
			</div>";
		}


		if($genres != null){
			// GENRE FILTER
			print "
			<div class='topnav'>
			<label for='genre'><b>Genre:</b></label><br>
			<select name='genre' id='id_genre'>
			<option value='all'>Select all</option>
			";
			//------------------
			foreach ($genres as $genre) {
				print "<option value='".$genre['genre_name']."'>".$genre['genre_name']."</option>";
			}
			print "
			</select>
			<button onclick='movieFilterChanged();'>Search</button>
			</div>";
		}

		// Closing nav-div
		print "</div>";
	}

	/*
	Displays error message
	*/
	public function showError($error) {
		print "<h2 style='color: red'>Error: $error</h2>";
	}
	/*Displays an array of movies
	*/
	public function showMovies($movie_array) {
		if (!empty($movie_array)) {
			foreach ($movie_array as $movie) {
				$this->printMovieInHtml($movie);
			}
		}
	}

	/*
	Format a movie into html
	*/
	private function printMovieInHtml($movie) {
		//print_r($movie);
		$title = $movie['title'];
		$tagline = $movie['tagline'];
		$plot = $movie['plot'];
		if (empty($movie['thumbpath'])) {
			$thumbpath = _MOVIE_PHOTO_FOLDER_."default.jpg";
		} else {
			$thumbpath = _MOVIE_PHOTO_FOLDER_.$movie['thumbpath'];
		}
		$director = $movie['director'];
		$studio = $movie['studio'];
		$genre = $movie['genre'];
		$classification = $movie['classification'];
		$rental_period = $movie['rental_period'];
		$year = $movie['year'];
		$DVD_rental_price = $movie['DVD_rental_price'];
		$DVD_purchase_price = $movie['DVD_purchase_price'];
		$numDVD = $movie['numDVD'];
		$numDVDout = $movie['numDVDout'];
		$BluRay_rental_price = $movie['BluRay_rental_price'];
		$BluRay_purchase_price = $movie['BluRay_purchase_price'];
		$numBluRay = $movie['numBluRay'];
		$numBluRayOut = $movie['numBluRayOut'];
		$movie_id = $movie['movie_id'];
		//
		$checked = ''; //check the movie checkbox if the movie is previously selected
		if (!empty($_SESSION['checked_movies'])) {
			$checked_movies = $_SESSION['checked_movies'];
			if (isset($checked_movies[$movie_id]))
			$checked = 'checked';
		}
		print "
		<div class='movie_card'>
		<div class='title'>
		<input type='submit' value='Edit' onclick='editMovieClick($movie_id);'>
		<input type='submit' value='Delete' onclick='deleteMovieClick($movie_id);'>
		<input type='checkbox' id='id_check' value='$movie_id' onclick='movieCheckClick(this);' $checked>
		$title
		</div>
		<div class='photo_container'>
		<img src= '$thumbpath' alt='movie photo' class='photo'>
		</div>
		<div class='content'>
		<b>DVD_rental_price: \$$DVD_rental_price</b><br>
		Director: $director<br>
		Studio: $studio<br>
		Year: $year<br>
		Classification: $classification<br>
		Genre: $genre<br>
		</div>
		</div>
		";
	}

	/*
	Format a member into html
	*/
	private function printMemberInHtml($member) {
		//print_r($member);
		$surname = $member['surname'];
		$other_name = $member['other_name'];
		$contact_method = $member['contact_method'];
		$email = $member['email'];
		$mobile = $member['mobile'];
		$landline = $member['landline'];
		$magazine = $member['magazine'];
		$street = $member['street'];
		$suburb = $member['suburb'];
		$postcode = $member['postcode'];
		$password = $member['password'];
		$occupation = $member['occupation'];
		$member_id = $member['member_id'];
		//
		$checked = ''; //check the movie checkbox if the member is previously selected
		if (!empty($_SESSION['checked_members'])) {
			$checked_members = $_SESSION['checked_members'];
			if (isset($checked_members[$member_id]))
			$checked = 'checked';
		}
		print "
		<div class='member_card'>
		<div class='surname'>
		$surname
		<input type='submit' value='Edit' onclick='editMemberClick($member_id);'>
		<input type='submit' value='Delete' onclick='deleteMemberClick($member_id);'>
		<input type='checkbox' id='id_check' value='$member_id' onclick='memberCheckClick(this);' $checked>
		</div>
		<div class='content'>
		<b>Other_name: \$$other_name</b><br>
		Contact_method: $contact_method<br>
		Email: $email<br>
		Landline: $landline<br>
		Magazine: $magazine<br>
		Street: $street<br>
		Suburb: $suburb<br>
		Postcode: $postcode<br>
		Password: $password<br>
		Occupation: $occupation<br>
		</div>
		</div>
		";
	}

	/*
	Displays an array of movies
	*/
	public function showInventory($movies_array) {
		print "
		<div class='inventory_table'>
		<div class='content'>
		<table>
		<caption>Movies Inventory</caption>
		<tr>
		<th>Movie ID</th>
		<th>Title</th>
		<th>Inventory DVD</th>
		<th>Inventory BluRay</th>
		</tr>
		";
		if (!empty($movies_array)) {
			foreach ($movies_array as $movies) {
				$this->showInventoryInHtml($movies);
			}
		}
		print "
		</table>
		</div>
		</div>
		";
	}

	/*
	Format inventory into html
	*/
	private function showInventoryInHtml($movies) {
		//print_r($movies);
		$movie_id= $movies['movie_id'];
		$title = $movies['title'];
		$InventoryDVD = $movies['InventoryDVD'];
		$InventoryBluRay = $movies['InventoryBluRay'];

		print "
		<tr>
		<td>$movie_id</td>
		<td>$title</td>
		<td>$InventoryDVD</td>
		<td>$InventoryBluRay</td>
		</tr>
		";
	}

	/*Shows director filter list */
	public function showDirectorFilterList($directors) {
		print "<div class='filter-content'>";
		foreach ($directors as $director) {
			print "<a onclick='directorInputUpdate(this.text);'>".$director['director_name']."</a>";
		}
		print "</div>";
	}

	/*Shows director filter list */
	public function showStudioFilterList($studios) {
		print "<div class='filter-content'>";
		foreach ($studios as $studio) {
			print "<a onclick='studioInputUpdate(this.text);'>".$studio['studio_name']."</a>";
		}
		print "</div>";
	}
	/*Shows genre filter list */
	public function showGenreFilterList($genres) {
		print "<div class='filter-content'>";
		foreach ($genres as $genre) {
			print "<a onclick='genreInputUpdate(this.text);'>".$genre['genre_name']."</a>";
		}
		print "</div>";
	}

	/*
	Displays an array of movies
	*/
	public function showMembers($member_array) {
		if (!empty($member_array)) {
			foreach ($member_array as $member) {
				$this->printMemberInHtml($member);
			}
		}
	}

}
?>
