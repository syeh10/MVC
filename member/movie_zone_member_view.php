<?php
/*-------------------------------------------------------------------------------------------------
@Module: movie_zone_member_view.php
This server-side module provides all required functionality to format and display movies in html

@Author: Vinh Bui (vinh.bui@scu.edu.au)
@Modified by: Shu Wei, Yeh (s.yeh.10@student.scu.edu.au)
@Date: 19/09/2017
--------------------------------------------------------------------------------------------------*/
require_once('movie_zone_member_config.php');

class MovieZoneMemberView {
	/*Class contructor: performs any initialization
	*/
	public function __construct() {
	}

	/*Class destructor: performs any deinitialiation
	*/
	public function __destruct() {
	}

	/*Creates left navigation panel
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

	/*Displays error message
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

	/*Format a movie into html
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
				<input type='submit' value='Rent/Purchase' onclick='movieSelectClick($movie_id);'>
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

}
?>
