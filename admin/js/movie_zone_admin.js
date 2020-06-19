/*
Use onload event to load the page with random movies
*/
var editing_mode; //if we are in editing mode e.g. add/edit movie

window.addEventListener("load", function() {
  makeAjaxGetRequest('movie_zone_admin_main.php', 'cmd_movie_select_all', null, updateContent);
  //show the top navigation panel
  makeAjaxGetRequest('movie_zone_admin_main.php', 'cmd_show_top_nav', null, updateTopNav);
  editing_mode = false; //default when loaded
});

/*
Handles show all movies onlick event to show all movies
 */
function movieShowAllClick() {
  makeAjaxGetRequest('movie_zone_admin_main.php', 'cmd_movie_select_all', null, updateContent);
  //hide the top navigation panel
  document.getElementById('id_topnav').style.display = "none";
}

/*
Handles show all movies onlick event to show all movies
 */
function memberShowAllClick() {
  makeAjaxGetRequest('movie_zone_admin_main.php', 'cmd_member_select_all', null, updateContent);
  //hide the top navigation panel
  document.getElementById('id_topnav').style.display = "none";
}

/*
Handles show all movies onlick event to show new movies
 */
function movieShowNewClick () {
  makeAjaxGetRequest('movie_zone_admin_main.php', 'cmd_movie_new_all', null, updateContent);
  // var newMovie = mysql_query("SELECT * FROM movie ORDER BY movie_id DESC");
  //hide the top navigation panel
  // document.getElementById('newMovie').style.display = "none";
  //hide the top navigation panel
  document.getElementById('id_topnav').style.display = "none";
}

/*
Handles filter movies onclick event to filter movies
*/
function movieFilterClick(typeOfFilter) {
  //load the navigation panel on demand
  document.getElementById('id_content').innerHTML = null;

  if(typeOfFilter === 'director'){
    makeAjaxGetRequest('movie_zone_admin_main.php', 'cmd_show_director_top_nav', null, updateTopNav);
  } else if(typeOfFilter === 'studio'){
    makeAjaxGetRequest('movie_zone_admin_main.php', 'cmd_show_studio_top_nav', null, updateTopNav);
  } else {
    makeAjaxGetRequest('movie_zone_admin_main.php', 'cmd_show_genre_top_nav', null, updateTopNav);
  }
}

/*Updates the content area if success
*/
function updateContent(data) {
  document.getElementById('id_content').innerHTML = data;
}

/*Updates the top navigation panel
*/
function updateTopNav(data = null) {
  var topnav = document.getElementById('id_topnav');
  if (data != null) {
    topnav.innerHTML = data;
    topnav.style.display = "inherit";
  } else {
    topnav.innerHTML = '';
    topnav.style.display = "none";
  }
}

/*
Handles show all movies onlick event to show new movies
 */
function movieCheckAvailable() {
  makeAjaxGetRequest('movie_zone_admin_main.php', 'cmd_check_available', null, updateContent);
  document.getElementById('id_topnav').style.display = "none";
}

/*Handles onclick events to filter/add/edit/delete movies
*/
//validate submitted data
function validateMovie(movieform) {
  //regex
  var regex = [
    /^[A-Z][\w\s]{4,40}$/, //title
    /^[A-Z][\w\s]{4,20}$/, //director
    /^[A-Z][\w\s]{2,128}$/, //studio
    /^\d{4}$/, //year
    /[+-]?([0-9]*[.])?[0-9]+$/ //price
  ];
  //error messages
  var errors = [
    'title begins with Capital letter and between 5-45 characters',
    'director begins with Capital letter and between 5-20 characters',
    'studio begins with Capital letter and between 3-128 characters',
    'year should have 4 digits',
    'price should be between 0 - 999999'
  ];
  var names = [
    'title', 'director', 'studio', 'year', 'DVD_rental_price'
  ];
  //perform the validation
  for (var i = 0; i < names.length; i++) {
    if (!regex[i].test(movieform.elements[names[i]].value)) {
      alert(errors[i]);
      return false;
    }
  }
  return true;
}

/*
Handles onclick events to filter/add/edit/delete members
validate submitted data of member
*/
function validateMember(memberform) {
  regex
  var regex = [
    /^[A-Z][a-z]{0,128}$/, //surname
    /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/, //email
    /^[\+]?[(]?[0-9]{3}[)]?[-\s\.]?[0-9]{3}[-\s\.]?[0-9]{4,6}$/, //mobile
    /^[A-Z][a-z]{4,40}$/, //landline
    /^[0-9]{0,1}$/, //magazine
    /^[A-Z][a-z]{4,40}$/, //street
    /^[A-Z][a-z]{4,40}$/, //suburb
    /^\d{4}$/, //postcode
    /^[A-Z][a-z]{4,10}$/, //username
    /^[A-Z][a-z]{4,10}$/, //password
    /^[A-Z][a-z]{4,20}$/, //occupation
  ];
  //error messages
  var errors = [
    'surname begins with Capital letter and between 1-128 characters',
    'email begins with Capital letter and between 5-40 characters',
    'mobile begins with 10 Number',
    'landline begins with Capital letter and between 5-10 characters',
    'magazine begins with 1 Number',
    'street begins with Capital letter and between 5-40 characters',
    'suburb begins with Capital letter and between 5-40 characters',
    'postcode begins with 4 Number',
    'username begins with Capital letter and between 5-10 characters',
    'password begins with Capital letter and between 5-10 characters',
    'occupation begins with Capital letter and between 5-20 characters',
  ];
  var names = [
    'surname',
    'other_name',
    'contact_method',
    'email',
    'mobile',
    'landline',
    'magazine',
    'street',
    'suburb',
    'postcode',
    'username',
    'password',
    'occupation',
  ];
  //perform the validation
  for (var i = 0; i < names.length; i++) {
    if (!regex[i].test(memberform.elements[names[i]].value)) {
      alert(errors[i]);
      return false;
    }
  }
  return true;
}

/*
submit movie data to server
*/
function btnAddEditMovieSubmitClick(command) {
  if (!validateMovie(document.movie))
  return;
  var moviedata = new FormData(document.movie);
  makeAjaxPostRequest('movie_zone_admin_main.php', command, moviedata, function(data) {
    if (data == '_OK_') {
      if (command == 'cmd_movie_add') {
        alert('The movie data has been successfully updated to the database');
        document.movie.reset(); //reset form
        document.getElementById('id_photo_frame').src = '';
        document.getElementById('id_error').innerHTML = '';
      } else {
        btnAddEditMovieExitClick();
      }
    } else {
      document.getElementById('id_error').innerHTML = data;
    }
  });
}

//submit member data to server
function btnAddEditMemberSubmitClick(command) {
  if (!validateMember(document.member))
  return;
  var memberdata = new FormData(document.member);
  makeAjaxPostRequest('movie_zone_admin_main.php', command, memberdata, function(data) {
    if (data == '_OK_') {
      if (command == 'cmd_member_add') {
        alert('The movie data has been successfully updated to the database');
        document.member.reset(); //reset form
        document.getElementById('id_error').innerHTML = '';
      } else {
        btnAddEditMovieExitClick();
      }
    } else {
      document.getElementById('id_error').innerHTML = data;
    }
  });
}

//exit add/edit mode and return to browsing mode
function btnAddEditMovieExitClick() {
  makeAjaxGetRequest('movie_zone_admin_main.php', 'cmd_movie_select_all', null, function(data) {
    updateContent(data);
    //show the top navigation panel
    makeAjaxGetRequest('movie_zone_admin_main.php', 'cmd_show_top_nav', null, function(data) {
      updateTopNav(data);
      editing_mode = false;
    });
  });
}

//shows movie add/edit form
function addMovieClick() {
  if (!editing_mode) {
    makeAjaxGetRequest('movie_zone_admin_main.php', 'cmd_movie_add_form', null, function(data) {
      updateTopNav(); //reset and hide the search box
      updateContent(data); //load the add/edit form to the content area
      updateMovieForm(); //populate the add/edit form
      editing_mode = true;
    });
  }
}

/*
sends request to server to
*/
function editMovieClick(movie_id) {
  var params = '&movie_id=' + movie_id;
  makeAjaxGetRequest('movie_zone_admin_main.php', 'cmd_movie_edit_form', params, function(data) {
    //reset and hide the search box
    updateTopNav();
    //load the add/edit form to the content area
    updateContent(data);
    //populate the add/edit form
    updateMovieForm(movie_id);
    editing_mode = true;
  });
}

/*
shows member add form
*/
function memberShowAddForm() {
  makeAjaxGetRequest('movie_zone_admin_main.php', 'cmd_member_add_form', null, updateContent);
}

/*
shows member add form
*/
function inventoryShowReport() {
  makeAjaxGetRequest('movie_zone_admin_main.php', 'cmd_inventory_report', null, updateContent);
}



/*
Add member
*/
function addMemberClick() {
  if (!editing_mode) {
    makeAjaxGetRequest('movie_zone_admin_main.php', 'cmd_member_add', null, function(data) {
      //reset and hide the search box
      updateTopNav();
      //load the add/edit form to the content area
      updateContent(data);
      //populate the add/edit form
      updateMemberForm();
      editing_mode = true;
    });
  }
}

/*
sends request to server to
*/
function editMemberClick(member_id) {
  var params = '&member=' + member_id;
  makeAjaxGetRequest('member_zone_admin_main.php', 'cmd_member_edit_form', params, function(data) {
    updateTopNav(); //reset and hide the search box
    updateContent(data); //load the add/edit form to the content area
    updatememberForm(member_id); //populate the add/edit form
    editing_mode = true;
  });
}

/*
sends request to server to display delete movie UI
*/
function deleteMovieClick() {
  if (!editing_mode) {
    if (confirm("Are you sure to delete selected movies?") == true) {
      makeAjaxGetRequest('movie_zone_admin_main.php', 'cmd_movie_delete', null, function(data) {
        if (data == '_OK_') {
          makeAjaxGetRequest('movie_zone_admin_main.php', 'cmd_movie_select_all', null, updateContent);
        } else {
          updateContent(data);
        }
      });
    }
  }
}

//sends request to server to display delete member UI
function deleteMemberClick() {
  if (!editing_mode) {
    if (confirm("Are you sure to delete selected members?") == true) {
      makeAjaxGetRequest('movie_zone_member_main.php', 'cmd_member_delete', null, function(data) {
        if (data == '_OK_') {
          makeAjaxGetRequest('movie_zone_admin_main.php', 'cmd_member_select_all', null, updateContent);
        } else {
          updateContent(data);
        }
      });
    }
  }
}

//exit to the main app
function exitClick() {
  if (editing_mode)
  if (confirm("Data is not saved. Are you sure to exit?") == false)
  return;
  //load the navigation panel on demand
  makeAjaxGetRequest('movie_zone_admin_main.php', 'cmd_admin_logout', null, function(data) {
    if (data == '_OK_') {
      editing_mode = false;
      window.location.replace('../index.php');
    }
  });
}

//handles the movie checkbox click event to sends request to server to check/uncheck movie
function movieCheckClick(check) {
  var params = '&movie_id=' + check.value;
  makeAjaxGetRequest('movie_zone_admin_main.php', 'cmd_movie_check', params, function(data) {
    if (data == '_OK_') {
      check.checked = true;
    } else
    check.checked = false;
  });
  event.stopPropagation(); //so edit event does not fire.
}

//Handles onchange event to filter the movie database
function movieFilterChanged() {
  var director = document.getElementById('id_director');
      director = (director && director.value) || 'all';

  var studio = document.getElementById('id_studio');
      studio = (studio && studio.value) || 'all';

  var genre = document.getElementById('id_genre');
      genre = (genre && genre.value) || 'all';

  //var year = document.getElementById('id_year').value;
  var params = '';
  if (director != 'all')
    params += '&director=' + director;
  if (studio != 'all')
    params += '&studio=' + studio;
  if (genre != 'all')
    params += '&genre=' + genre;

  makeAjaxGetRequest('movie_zone_admin_main.php', 'cmd_movie_filter', params, updateContent);
}

/*Loads photo from local system to input form
*/
function loadPhoto(fileSelector) {
  var files = fileSelector.files;
  // FileReader support
  if (FileReader && files && files.length) {
    var fr = new FileReader();
    fr.onload = function() {
      document.getElementById('id_photo_frame').src = fr.result;
    }
    fr.readAsDataURL(files[0]);
  }

  // Not supported
  else {
    // fallback -- perhaps submit the input to an iframe and temporarily store
    // them on the server until the user's session ends.
  }
}

/*
Gets the genre list from server and create the options on the fly
*/
function updateMovieForm(movie_id) {
  //add movie mode
  makeAjaxGetRequest('movie_zone_admin_main.php', 'cmd_genre_select_all', null, function(data) {
    try {
      var genres = JSON.parse(data);
      for (var i = 0; i < genres.length; i++) {
        var genre = genres[i];
        var option = document.createElement("option");
        option.text = genre.genre_name;
        option.value = genre.genre_id;
        document.movie.genre.add(option); //genre dropbox in movie form
      }

      if (movie_id != null) { //update movie mode
        //update the form with movie data
        var params = '&movie_id=' + movie_id;
        makeAjaxGetRequest('movie_zone_admin_main.php', 'cmd_movie_select_by_id', params,
        function(data) {
          var moviedata = JSON.parse(data);
          document.movie.movie_id.value = moviedata[0].movie_id;
          document.movie.title.value = moviedata[0].title;
          document.movie.tagline.value = moviedata[0].tagline;
          document.movie.plot.value = moviedata[0].plot;
          document.movie.director.value = moviedata[0].director;
          document.movie.studio.value = moviedata[0].studio;
          document.movie.classification.value = moviedata[0].classification;
          document.movie.rental_period.value = moviedata[0].rental_period;
          document.movie.DVD_rental_price.value = moviedata[0].DVD_rental_price;
          document.movie.DVD_purchase_price.value = moviedata[0].DVD_purchase_price;
          document.movie.numDVD.value = moviedata[0].numDVD;
          document.movie.numDVDout.value = moviedata[0].numDVDout;
          document.movie.BluRay_rental_price.value = moviedata[0].BluRay_rental_price;
          document.movie.BluRay_purchase_price.value = moviedata[0].BluRay_purchase_price;
          document.movie.numBluRay.value = moviedata[0].numBluRay;
          document.movie.numBluRayOut.value = moviedata[0].numBluRayOut;
          document.movie.year.value = moviedata[0].year;

          for (var i = 0; i < document.movie.genre.options.length; i++) {
            if (document.movie.genre.options[i].text == moviedata[0].genre) {
              document.movie.genre.selectedIndex = i;
              break;
            }
          }

          document.movie.photo.value = moviedata[0].thumbpath || 'default.jpg';
          document.getElementById('id_photo_frame').src = '../photos/' + moviedata[0].thumbpath;
          document.movie.btnSubmit.onclick = function() {
            btnAddEditMovieSubmitClick('cmd_movie_edit');
          }
        });
      } else {
        document.movie.btnSubmit.onclick = function() {
          btnAddEditMovieSubmitClick('cmd_movie_add');
        }
      }
    } catch (error) {
      //error, simply display data
      document.getElementById('id_error').innerHTML = error;
    }
  });
  //assign event handlers to other components of the form
  /*Important note: the following is a common mistake ->
  document.movie.btnExit.onclick = btnAddEditMovieExitClick(); //this will invoke the function and
  assign the result to onlick instead of assigning the function to onlick!
  */
  //this is a correct way of doing the assignment
  document.movie.btnExit.onclick = function() {
    btnAddEditMovieExitClick();
  }
  //another way of assigning functions to events but this does not work for IE9 and below
  document.movie.director.addEventListener('keyup', function() {
    makeKeyupHandler(this) //this represents the event owner i.e. the director input in this case
  });
  //this represents the event owner i.e. the studio input in this case
  document.movie.studio.addEventListener('keyup', function() {
    studioKeyupHandler(this)
  });
}

/*Gets the member list from server and create the options on the fly
*/
function updateMemberForm(member_id) {
  //add member mode
  makeAjaxGetRequest('movie_zone_admin_main.php', 'cmd_member_select_all', null, function(data) {
    try {
      var members = JSON.parse(data);
      for (var i = 0; i < members.length; i++) {
        var member = members[i];
        var option = document.createElement("option");
        option.text = member.name;
        option.value = member.member_id;
        document.member.add(option); //genre dropbox in member form
      }

      if (member_id != null) { //update member mode
        //update the form with member data
        var params = '&member_id=' + member_id;
        makeAjaxGetRequest('movie_zone_admin_main.php', 'cmd_member_select_by_id', params,
        function(data) {
          var memberdata = JSON.parse(data);
          document.member.member_id.value = memberdata[0].member_id;
          document.member.surname.value = memberdata[0].surname;
          document.member.other_name.value = memberdata[0].other_name;
          document.member.contact_method.value = memberdata[0].contact_method;
          document.member.email.value = memberdata[0].email;
          document.member.mobile.value = memberdata[0].mobile;
          document.member.landline.value = memberdata[0].landline;
          document.member.magazine.value = memberdata[0].magazine;
          document.member.street.value = memberdata[0].street;
          document.member.suburb.value = memberdata[0].suburb;
          document.member.postcode.value = memberdata[0].postcode;
          document.member.password.value = memberdata[0].password;
          document.member.occupation.value = memberdata[0].occupation;
          for (var i = 0; i < document.member.options.length; i++) {
            if (document.member.options[i].text == memberdata[0]) {
              document.member.selectedIndex = i;
              break;
            }
          }
          // document.getElementById('id_photo_frame').src = '../photos/' + memberdata[0].photo;
          document.member.btnSubmit.onclick = function() {
            btnAddEditMemberSubmitClick('cmd_member_edit');
          }
        });
      } else {
        document.member.btnSubmit.onclick = function() {
          btnAddEditMemberSubmitClick('cmd_member_add');
        }
      }
    } catch (ex) {
      //error, simply display data
      document.getElementById('id_error').innerHTML = data;
    }
  });
}

/*Handles director input keyup event and contact with server via Ajax to ger the list of directors*/
function makeKeyupHandler(director) {
  var params = "&keyword=" + director.value;
  makeAjaxGetRequest('movie_zone_admin_main.php', 'cmd_director_filter', params, function(data) {
    document.getElementById('id_director_filter').innerHTML = data;
  });
}

/*Handle director filter onlick event and update director with the value and hide the director filter*/
function directorInputUpdate(value) {
  document.movie.director.value = value;
  document.getElementById('id_director_filter').innerHTML = '';
}

/*Handles director input keyup event and contact with server via Ajax to ger the list of directors*/
function studioKeyupHandler(studio) {
  var params = "&keyword=" + studio.value;
  makeAjaxGetRequest('movie_zone_admin_main.php', 'cmd_studio_filter', params, function(data) {
    document.getElementById('id_studio_filter').innerHTML = data;
  });
}

/*Handle director filter onlick event and update director with the value and hide the director filter*/
function studioInputUpdate(value) {
  document.movie.studio.value = value;
  document.getElementById('id_studio_filter').innerHTML = '';
}
/*Handles genre input keyup event and contact with server via Ajax to ger the list of genres*/
function genreKeyupHandler(genre) {
  var params = "&keyword=" + genre.value;
  makeAjaxGetRequest('movie_zone_admin_main.php', 'cmd_genre_filter', params, function(data) {
    document.getElementById('id_genre_filter').innerHTML = data;
  });
}
/*Handle genre filter onlick event and update genre with the value and hide the genre filter*/
function genreInputUpdate(value) {
  document.movie.genre.value = value;
  document.getElementById('id_genre_filter').innerHTML = '';
}
