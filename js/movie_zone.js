/*
Use onload event to load the page with random movies
*/
window.addEventListener("load", function() {
  makeAjaxGetRequest('movie_zone_main.php', 'cmd_movie_select_random', null, updateContent);
  //show the top navigation panel
  document.getElementById('id_topnav').style.display = "none";
});
//assign event handlers to other components of the form
/*Important note: the following is a common mistake ->
  document.member.btnExit.onclick = btnAddEditMemberExitClick(); //this will invoke the function and
  assign the result to onlick instead of assigning the function to onlick!
*/
/*
Handles onchange event to filter the movie database
*/
// The event handler function for password checking
function chkPasswords() {
  var init = document.getElementById("initial");
  var sec = document.getElementById("second");
  if (init.value == "") {
    alert("You did not enter a password \n" +
      "Please enter one now");
    return false;
  }
  if (init.value != sec.value) {
    alert("The two passwords you entered are not the same \n" +
      "Please re-enter both now");
    return false;
  } else
    return true;
}

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

  makeAjaxGetRequest('movie_zone_main.php', 'cmd_movie_filter', params, updateContent);
}

/*
shows contact
*/
function contactShow() {
  makeAjaxGetRequest('movie_zone_main.php', 'cmd_contact', null, updateContent);
}

/*
shows online request
*/
function onlineRequest() {
  makeAjaxGetRequest('movie_zone_main.php', 'cmd_request', null, updateContent);
}

/*
shows online support
*/
function onlineSupport() {
  makeAjaxGetRequest('movie_zone_main.php', 'cmd_support', null, updateContent);
}

/*
shows contact
*/
function techzoneShow() {
  makeAjaxGetRequest('movie_zone_main.php', 'cmd_techzone', null, updateContent);
}

/*
shows member add form
*/
function memberShowAddForm() {
  makeAjaxGetRequest('movie_zone_main.php', 'cmd_member_add_form', null, updateContent);
}

//The join date for new member
function joinDate() {
  var now = new Date();
  document.write(now.getYear()+"-"+now.getMonth()+"-"+now.getDate());
}

//shows member add/edit form
function addMemberClick() {
	if (!editing_mode) {
		makeAjaxGetRequest('movie_zone_main.php','cmd_member_add_form', null, function(data) {
			updateTopNav(); //reset and hide the search box
			updateContent(data); //load the add/edit form to the content area
			updateMemberForm(); //populate the add/edit form
			editing_mode = true;
		});
	}
}

/*
Handles show all movies onlick event to show all movies
*/
function movieShowAllClick() {
  makeAjaxGetRequest('movie_zone_main.php', 'cmd_movie_select_all', null, updateContent);
  //hide the top navigation panel
  document.getElementById('id_topnav').style.display = "none";
}

/*
Handles show all movies onlick event to show new movies
 */
function movieShowNewClick() {
  makeAjaxGetRequest('movie_zone_main.php', 'cmd_movie_new_all', null, updateContent);
  document.getElementById('id_topnav').style.display = "none";
}

/*
Handles show all movies onlick event to show new movies
 */
function movieCheckAvailable() {
  makeAjaxGetRequest('movie_zone_main.php', 'cmd_check_available', null, updateContent);
  document.getElementById('id_topnav').style.display = "none";
}

/*
Handles filter movies onclick event to filter movies
*/
function movieFilterClick(typeOfFilter) {
  //load the navigation panel on demand
  document.getElementById('id_content').innerHTML = null;

  if(typeOfFilter === 'director'){
    makeAjaxGetRequest('movie_zone_main.php', 'cmd_show_director_top_nav', null, updateTopNav);
  } else if(typeOfFilter === 'studio'){
    makeAjaxGetRequest('movie_zone_main.php', 'cmd_show_studio_top_nav', null, updateTopNav);
  } else {
    makeAjaxGetRequest('movie_zone_main.php', 'cmd_show_genre_top_nav', null, updateTopNav);
  }
}

/*
Updates the content area if success
*/
function updateContent(data) {
  document.getElementById('id_content').innerHTML = data;
}

// /*
// Updates the left area if success
// */
// function updateLeftContent(data) {
//   left = document.getElementById('left');
//   left.innerHTML = data;
//   left.style.display = "inherit";
// }

/*
Updates the top navigation panel
*/
function updateTopNav(data) {
  var topnav = document.getElementById('id_topnav');
  topnav.innerHTML = data;
  topnav.style.display = "inherit";
}
//validate submitted data
function validate(memberform) {
  regex
  var regex = [
    /^[A-Z][a-z]{4,128}$/, //surname
    /^[A-Z][a-z]{4,128}$/, //other_name
    /^[A-Z][a-z]{4,10}$/, //contact_method
    /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/, //email
    /^(\()?\d{3}(\))?(-|\s)?\d{3}(-|\s)\d{4}$/, //mobile
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
    'surname begins with Capital letter and between 5-128 characters',
    'other_name begins with Capital letter and between 5-128 characters',
    'contact_method begins with Capital letter and between 5-10 characters',
    'email begins with Capital letter and between 5-40 characters',
    'mobile begins with 9 Number',
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
submit member data to server
*/
function btnAddEditMemberSubmitClick(command) {
  if (!validate(document.member))
    return;
  var memberdata = new FormData(document.member);
  makeAjaxPostRequest('movie_zone_main.php', command, memberdata, function(data) {
    if (data == '_OK_') {
      if (command == 'cmd_member_add') {
        alert('The member data has been successfully updated to the database');
        document.member.reset(); //reset form
        document.getElementById('id_error').innerHTML = '';
      } else {
        btnAddEditMemberExitClick();
      }
    } else {
      document.getElementById('id_error').innerHTML = data;
    }
  });
}
//exit add/edit mode and return to browsing mode
function btnAddEditMemberExitClick() {
  makeAjaxGetRequest('movie_zone_main.php', 'cmd_movie_select_all', null, function(data) {
    updateContent(data);
    //show the top navigation panel
    makeAjaxGetRequest('movie_zone_main.php', 'cmd_show_top_nav', null, function(data) {
      updateTopNav(data);
      editing_mode = false;
    });
  });
}
//shows member add/edit form
function addMemberClick() {
  if (!editing_mode) {
    makeAjaxGetRequest('movie_zone_main.php', 'cmd_member_add_form', null, function(data) {
      updateTopNav(); //reset and hide the search box
      updateContent(data); //load the add/edit form to the content area
      updateMemberForm(); //populate the add/edit form
      editing_mode = true;
    });
  }
}
//sends request to server to
function editMemberClick(member_id) {
  var params = '&member_id=' + member_id;
  makeAjaxGetRequest('movie_zone_main.php', 'cmd_member_edit_form', params, function(data) {
    updateTopNav(); //reset and hide the search box
    updateContent(data); //load the add/edit form to the content area
    updateMemberForm(member_id); //populate the add/edit form
    editing_mode = true;
  });
}
//exit to the main app
function exitClick() {
  if (editing_mode)
    if (confirm("Data is not saved. Are you sure to exit?") == false)
      return;
  //load the navigation panel on demand
  makeAjaxGetRequest('movie_zone_main.php', 'cmd_member_logout', null, function(data) {
    if (data == '_OK_') {
      editing_mode = false;
      window.location.replace('../index.php');
    }
  });
}
