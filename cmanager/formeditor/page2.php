<?php
/* --------------------------------------------------------
 *
 *
 * This page is the main form editor for the course request
 * manager tool. This page allows you to dynamically and
 * and remove different form fields.
 *
 * Kyle Goslin & Daniel Mc Sweeney 2012
 * Institute of Technology Blanchardstown
 * ---------------------------------------------------------
 */
require_once("../../../config.php");
global $CFG, $DB;
require_once("$CFG->libdir/formslib.php");

require_login();
require_once('../validate_admin.php');

/** Navigation Bar **/
$PAGE->navbar->ignore_active();
$PAGE->navbar->add(get_string('cmanagerDisplay', 'block_cmanager'), new moodle_url('/blocks/cmanager/cmanager_admin.php'));
$PAGE->navbar->add(get_string('configurecoursemanagersettings', 'block_cmanager'), new moodle_url('/blocks/cmanager/cmanager_confighome.php'));
$PAGE->navbar->add(get_string('formpage2', 'block_cmanager'));

$PAGE->set_url('/blocks/cmanager/formeditor/page2.php');
$PAGE->set_context(get_system_context());
$PAGE->set_heading(get_string('pluginname', 'block_cmanager'));
$PAGE->set_title(get_string('pluginname', 'block_cmanager'));
echo $OUTPUT->header();


if(isset($_GET['id'])){

	$formId = $_GET['id'];
	$current_record =  $DB->get_record('block_cmanager_config', array('id'=>$formId));
  	$formName =  $current_record->value;
	$_SESSION['formid'] = $formId;
}

else {
	echo get_string('formBuilder_p2_error','block_cmanager');
	die;
}


echo '<script>
       var num = 1; // Used to count the number of fields added.
       var formId = '.$formId .';
	   var formName = \''.$formName.'\';
       var movedownEnabled = 1;
	   var numberOfFields = 0;
      </script>';

// Deleting dropdown menus
if(isset($_GET['t']) && isset($_GET['del'])){

  
	if($_GET['t'] == 'dropitem'){ // Delete a dropdown menu item
		$itemid = $_GET['del'];
		$fieldid = $_GET['fid'];
		$DB->delete_records('block_cmanager_form_data', array('fieldid'=>$fieldid,'id'=>$itemid));
	}
	
	if($_GET['t'] == 'drop'){ // Delete all dropdown field items
	
		$fieldid = $_GET['del'];
		$DB->delete_records('block_cmanager_form_data', array('fieldid'=>$fieldid));
	}

}


// Delete Field
if(isset($_GET['del'])){

	$formid = $_GET['id'];
	$delId = $_GET['del'];

    $DB->delete_records_select('block_cmanager_formfields', "id = $delId");

	//Update the position numbers
	$selectQuery = "SELECT * FROM ".$CFG->prefix."block_cmanager_formfields WHERE formid = $formid order by id ASC";
	$positionItems = $DB->get_records_sql($selectQuery, null);

	$newposition = 1;
	$dataobject = new stdClass();
    foreach($positionItems as $item){

		$dataobject->id = $item->id;
		$dataobject->position = $newposition;
		$DB->update_record('block_cmanager_formfields', $dataobject);

		$newposition++;

	  }


}





// Move field up
if(isset($_GET['up'])){


	$currentId = $_GET['up'];

	$currentRecord = $DB->get_record('block_cmanager_formfields', array('id'=>$currentId), $fields='*', IGNORE_MULTIPLE);
	$currentPosition = $currentRecord->position;

	$higherpos = $currentPosition-1;
    $higherRecord = $DB->get_record('block_cmanager_formfields', array('position'=>$higherpos), $fields='*', IGNORE_MULTIPLE);

	// Update the records
	$dataobject = new stdClass();
	$dataobject->id = $currentRecord->id;
	$dataobject->position = $higherRecord->position;
	$DB->update_record('block_cmanager_formfields', $dataobject);

	$dataobject2 = new stdClass();
	$dataobject2->id = $higherRecord->id;
	$dataobject2->position = $currentRecord->position;
	$DB->update_record('block_cmanager_formfields', $dataobject2);


}



// Move field down
if(isset($_GET['down'])){

	$currentId = $_GET['down'];

	$currentRecord = $DB->get_record('block_cmanager_formfields', array('id'=>$currentId), $fields='*', IGNORE_MULTIPLE);
	$currentPosition = $currentRecord->position;

	$higherpos = $currentPosition+1;
    $higherRecord = $DB->get_record('block_cmanager_formfields', array('position'=>$higherpos), $fields='*', IGNORE_MULTIPLE);




	// Update the records
	$dataobject = new stdClass();
	$dataobject->id = $currentRecord->id;
	$dataobject->position = $higherRecord->position;
	$DB->update_record('block_cmanager_formfields', $dataobject);

	$dataobject2 = new stdClass();
	$dataobject2->id = $higherRecord->id;
	$dataobject2->position = $currentRecord->position;
	$DB->update_record('block_cmanager_formfields', $dataobject2);







}


?>


<script src="../js/jquery/jquery-1.7.2.min.js"></script>


<script>

function saveOptionalStatus(id){

		var value1 = document.getElementById('optional_' + id).value;

		$.post("ajax_functions.php", { type: 'saveoptionalvalue', value: value1, id: id },

  		function(data) {
  			alert('<?php echo get_string('ChangesSaved', 'block_cmanager');?>');

	   });

	}


	//onscreen language variables and default values
	var dropdownTxt = "";
	var radioTxt = "";
	var textAreaTxt = "";
	var textFieldTxt = "";
	var leftTxt = "";
	var saveTxt = "";
	var addedItemsTxt = "";
	var addItemBtnTxt = "";


	//Accept values for onscreen language variables from PHP
	function setLangStrings(lang_dropdownTxt,lang_radioTxt,lang_textAreaTxt,lang_textFieldTxt,lang_leftTxt,lang_saveTxt,lang_addedItemsTxt,lang_addItemBtnTxt)
	{

		dropdownTxt = lang_dropdownTxt;
		radioTxt = lang_radioTxt;
		textAreaTxt = lang_textAreaTxt;
		textFieldTxt = lang_textFieldTxt;
		leftTxt = lang_leftTxt;
		saveTxt = lang_saveTxt;
		addedItemsTxt = lang_addedItemsTxt;
		addItemBtnTxt = lang_addItemBtnTxt;

	}


	function enableSave(id){

		//alert(id);

		var saveButton = document.getElementById(id);
		saveButton.disabled=!saveButton.disabled;

	}

	// Select which field type to add based on fval
	function addNewField(fval){


		num++;
		var field = fval.value;

		if(field == 'tf' ){
		  createTextField();
		}
		if(field == 'ta'){
			createTextArea();
       	}
       	if(field == 'dropdown'){
       		createDropdown();
       	}
       	if(field == 'radio'){
       		createRadio();
       	}


	}

	// Create a new blank text field on the page
	function createTextField(){


			var ni = document.getElementById('formdiv');
			var newdiv = document.createElement('div');
			//newdiv.style.backgroundColor = "gray";
			newdiv.style.borderWidth = 1;
			newdiv.style.borderStyle = 'dotted';

			newdiv.style.width = 450;
			newdiv.style.height = 110;
	        newdiv.style.marginBottom = 5;
	        newdiv.style.marginLeft = 5;

			var divIdName = 'my'+num+'Div';
	        newdiv.setAttribute('id',num);
	        ni.appendChild(newdiv);


	        var uniqueId;
	        // Add to database
	        $.ajaxSetup({async:false});
	         $.post("ajax_functions.php", { type: 'page2addfield', fieldtype: 'textfield', formid: formId},
   				function(data) {

   					/*
		     		uniqueId = data;

		     		if(num == 1){
		     		 newdiv.innerHTML = '<b>'+textFieldTxt+':</b> <img src="../images/move_up_dis.gif" width="20" height="20" alt="move up" />  <a href="page2.php?id=' + formId + '&down=' + uniqueId + '"> '+
		     		 '<img src="../images/move_down.gif" width="20" height="20" alt="move down" /></a>  <a href="page2.php?id=' + formId + '&del=' + uniqueId + '"> '+
		     		 '<img src="../images/deleteIcon.png" width="20" height="20" alt="delete" /></a> '+
		     		 '<p></p><table><tr><td>'+leftTxt+':</td><td><input type="text" size="30" id="'+ uniqueId+'" onfocus="enableSave(\''+uniqueId+'_savebtn\');"></input></td></tr></table><input type="button" value="'+saveTxt+'" id="'+uniqueId+'_savebtn" disabled="disabled" onclick="saveFieldValue(' + uniqueId + ');"/> ';
	       			}
	       			else if(movedownEnabled == 0){
					   newdiv.innerHTML = '<b>'+textFieldTxt+':</b> <a href="page2.php?id=' + formId + '&up=' + uniqueId + '"> '+
					   '<img src="../images/move_up.gif" width="20" height="20" alt="move up" /></a> <img src="../images/move_down_dis.gif" width="20" height="20" alt="move down" /> <a href="page2.php?id=' + formId + '&del=' + uniqueId + '"> '+
					   '<img src="../images/deleteIcon.png" width="20" height="20" alt="delete" /></a> ' +
					   '<p></p><table><tr><td>'+leftTxt+':</td><td><input type="text" size="30" id="'+ uniqueId+'" onfocus="enableSave(\''+uniqueId+'_savebtn\');"></input></td></tr></table><input type="button" value="'+saveTxt+'" disabled="disabled" id="'+uniqueId+'_savebtn" onclick="saveFieldValue(' + uniqueId+');"/>';

			        }
	       			else {
	       	 			newdiv.innerHTML = '<b>'+textFieldTxt+':</b> <a href="page2.php?id=' + formId + '&up=' + uniqueId + '"> ' +
	       	 			'<img src="../images/move_up.gif" width="20" height="20" alt="move up" /></a> '+
	       	 			'<img src="../images/move_down_dis.gif" width="20" height="20" alt="move down" /> <a href="page2.php?id=' + formId + '&del=' + uniqueId + '"> ' +
	       	 			'<img src="../images/deleteIcon.png" width="20" height="20" alt="delete" /></a> '+
	       	 			'<p></p><table><tr><td>'+leftTxt+':</td><td><input type="text" size="30" id="'+ uniqueId+'" onfocus="enableSave(\''+uniqueId+'_savebtn\');"></input></td></tr></table><input type="button" value="'+saveTxt+'" disabled="disabled" id="'+uniqueId+'_savebtn" onclick="saveFieldValue(' + uniqueId+');"/>';

	       			}*/


			   });


			   num++;
			   window.location = 'page2.php?id=' + formId + '&name='+formName;
	}


	// If the text field already existed, rebuilt it using data from the db.
	function recreateTextField(uniqueId, leftText, requiredFieldValue){


			var ni = document.getElementById('formdiv');
			var newdiv = document.createElement('div');
			//newdiv.style.backgroundColor = "gray";
			newdiv.style.borderWidth = '1px';
			newdiv.style.borderStyle = 'dotted';

			newdiv.style.width = 400;
			newdiv.style.height = 110;
	        newdiv.style.marginBottom = 5;
	        newdiv.style.marginLeft = 5;

			var divIdName = 'my'+num+'Div';
	        newdiv.setAttribute('id',num);
	        ni.appendChild(newdiv);

	   	    var selectedText = '';
	   	    if(requiredFieldValue == '1'){

	   	    	       	selectedText = 'selected="selected"';
	   	    }


	   	    if(numberOfFields == 1){
	   	    		newdiv.innerHTML = '<b>'+textFieldTxt+':</b> <img src="../images/move_up_dis.gif" width="20" height="20" alt="move up" /> '+
	   	    		'<img src="../images/move_down_dis.gif" width="20" height="20" alt="move down" />  <a href="page2.php?id=' + formId + '&del=' + uniqueId + '"> ' +
	   	    		'<img src="../images/deleteIcon.png" width="20" height="20" alt="delete" /></a> '+
	   	    		'<select id = "optional_'+uniqueId+'" onchange="saveOptionalStatus('+uniqueId+')"> <option value="0"> <?php echo get_string('optional_field', 'block_cmanager'); ?> </option>  <option '+selectedText+' value="1"> <?php echo get_string('required_field', 'block_cmanager'); ?></option>  </select><p></p><table><tr><td>'+leftTxt+':</td><td><input type="text" id = "'+uniqueId +'" size="30" value="' + leftText+ '" onfocus="enableSave(\''+uniqueId+'_savebtn\');"></input></td></tr></table><input type="button" value="'+saveTxt+'" disabled="disabled" id="'+uniqueId+'_savebtn" onclick="saveFieldValue(' + uniqueId+')"/>';

	   	    } else {
	   	   		 	if(num == 1){
				   	   		newdiv.innerHTML = '<b>'+textFieldTxt+':</b> '+
				   	   		'<img src="../images/move_up_dis.gif" width="20" height="20" alt="move up" />  <a href="page2.php?id=' + formId + '&down=' + uniqueId + '">'+
				   	   		'<img src="../images/move_down.gif" width="20" height="20" alt="move down" /></a>  <a href="page2.php?id=' + formId + '&del=' + uniqueId + '">'+
				   	   		'<img src="../images/deleteIcon.png" width="20" height="20" alt="delete" /></a>'+
				   	   		'<select id = "optional_'+uniqueId+'" onchange="saveOptionalStatus('+uniqueId+')"> <option value="0"> <?php echo get_string('optional_field', 'block_cmanager'); ?> </option>  <option '+selectedText+' value="1"> <?php echo get_string('required_field', 'block_cmanager'); ?></option>  </select> <p></p><table><tr><td>'+leftTxt+':</td><td><input type="text" id = "'+uniqueId +'" size="30" value="' + leftText+ '" onfocus="enableSave(\''+uniqueId+'_savebtn\');"></input></td></tr></table><input type="button" value="'+saveTxt+'" disabled="disabled" id="'+uniqueId+'_savebtn" onclick="saveFieldValue(' + uniqueId+')"/> ';
				       	}
						else if(movedownEnabled == 0){
							 newdiv.innerHTML = '<b>'+textFieldTxt+':</b> <a href="page2.php?id=' + formId + '&up=' + uniqueId + '">'+
							 '<img src="../images/move_up.gif" width="20" height="20" alt="move up" /></a>   '+
							 '<img src="../images/move_down_dis.gif" width="20" height="20" alt="move down" />   <a href="page2.php?id=' + formId + '&del=' + uniqueId + '"> '+
							 '<img src="../images/deleteIcon.png" width="20" height="20" alt="delete" /></a> '+
							 '<select id = "optional_'+uniqueId+'" onchange="saveOptionalStatus('+uniqueId+')"> <option value="0"> <?php echo get_string('optional_field', 'block_cmanager'); ?> </option>  <option '+selectedText+' value="1"> <?php echo get_string('required_field', 'block_cmanager'); ?></option>  </select><p></p><table><tr><td>'+leftTxt+':</td><td><input type="text" id = "'+uniqueId +'" size="30" value="' + leftText+ '" onfocus="enableSave(\''+uniqueId+'_savebtn\');"></input></td></tr></table><input type="button" value="'+saveTxt+'" disabled="disabled" id="'+uniqueId+'_savebtn" onclick="saveFieldValue(' + uniqueId+')"/>';
				       	}
					    else {
					    	 newdiv.innerHTML = '<b>'+textFieldTxt+':</b> <a href="page2.php?id=' + formId + '&up=' + uniqueId + '"><img src="../images/move_up.gif" width="20" height="20" alt="move up" /></a>  <a href="page2.php?id=' + formId + '&down=' + uniqueId + '"><img src="../images/move_down.gif" width="20" height="20" alt="move down" /></a><a href="page2.php?id=' + formId + '&del=' + uniqueId + '"><img src="../images/deleteIcon.png" width="20" height="20" alt="delete" /></a>'+
					    	 '<select id = "optional_'+uniqueId+'" onchange="saveOptionalStatus('+uniqueId+')"> <option value="0"> <?php echo get_string('optional_field', 'block_cmanager'); ?> </option>  <option '+selectedText+' value="1"> <?php echo get_string('required_field', 'block_cmanager'); ?></option>  </select>'+
					    	 ' <p></p><table><tr><td>'+leftTxt+':</td><td><input type="text" id = "'+uniqueId +'" size="30" value="' + leftText+ '" onfocus="enableSave(\''+uniqueId+'_savebtn\');"></input></td></tr></table><input type="button" value="'+saveTxt+'" disabled="disabled" id="'+uniqueId+'_savebtn" onclick="saveFieldValue(' + uniqueId+')"/>';
				       	}

	       		}
	       			num++;
	}

		// Create a new blank text field on the page
	function createTextArea(){


			var ni = document.getElementById('formdiv');
			var newdiv = document.createElement('div');
			//newdiv.style.backgroundColor = "gray";
			newdiv.style.borderWidth = '1px';
			newdiv.style.borderStyle = 'dotted';

			newdiv.style.width = 450;
			newdiv.style.height = 110;
	        newdiv.style.marginBottom = 5;
	        newdiv.style.marginLeft = 5;

			var divIdName = 'my'+num+'Div';
	        newdiv.setAttribute('id',num);
	        ni.appendChild(newdiv);


	        var uniqueId;
	        // Add to database
	        $.ajaxSetup({async:false});
	         $.post("ajax_functions.php", { type: 'page2addfield', fieldtype: 'textarea', formid: formId},
   				function(data) {

   					/*
		     		uniqueId = data;

		     		if(num == 1){
		     		 newdiv.innerHTML = '<b>'+textAreaTxt+':</b>  <img src="../images/move_up_dis.gif" width="20" height="20" alt="move up" /> <a href="page2.php?id=' + formId + '&down=' + uniqueId + '"><img src="../images/move_down.gif" width="20" height="20" alt="move down" /></a>  <a href="page2.php?id=' + formId + '&del=' + uniqueId + '"><img src="../images/deleteIcon.png" width="20" height="20" alt="delete" /></a> <p></p><table><tr><td>'+leftTxt+':</td><td><input type="text" size="30" id="'+ uniqueId+'" onfocus="enableSave(\''+uniqueId+'_savebtn\');"></input></td></tr></table><input type="button" value="'+saveTxt+'" disabled="disabled" id="'+uniqueId+'_savebtn" onclick="saveFieldValue(' + uniqueId+')"/>';
	       			}
	       			else if(movedownEnabled == 0){
	       	 			newdiv.innerHTML = '<b>'+textAreaTxt+':</b> <a href="page2.php?id=' + formId + '&up=' + uniqueId + '"><img src="../images/move_up.gif" width="20" height="20" alt="move up" /></a> <img src="../images/move_down_dis.gif" width="20" height="20" alt="move down" /> <a href="page2.php?id=' + formId + '&del=' + uniqueId + '"><img src="../images/deleteIcon.png" width="20" height="20" alt="delete" /></a><p></p><table><tr><td>'+leftTxt+':</td><td><input type="text" size="30" id="'+ uniqueId+'" onfocus="enableSave(\''+uniqueId+'_savebtn\');"></input></td></tr></table><input type="button" value="'+saveTxt+'" disabled="disabled" id="'+uniqueId+'_savebtn" onclick="saveFieldValue(' + uniqueId+')"/>';

	       			}
	       			else {
	       	 			newdiv.innerHTML = '<b>'+textAreaTxt+':</b> <a href="page2.php?id=' + formId + '&up=' + uniqueId + '"><img src="../images/move_up.gif" width="20" height="20" alt="move up" /></a>  <a href="page2.php?id=' + formId + '&down=' + uniqueId + '"><img src="../images/move_down.gif" width="20" height="20" alt="move down" /></a>  <a href="page2.php?id=' + formId + '&del=' + uniqueId + '"><img src="../images/deleteIcon.png" width="20" height="20" alt="delete" /></a><p></p><table><tr><td>'+leftTxt+':</td><td><input type="text" size="30" id="'+ uniqueId+'" onfocus="enableSave(\''+uniqueId+'_savebtn\');"></input></td></tr></table><input type="button" value="'+saveTxt+'" disabled="disabled" id="'+uniqueId+'_savebtn" onclick="saveFieldValue(' + uniqueId+')"/>';

	       			}*/
			   });


			   num++;

			  window.location = 'page2.php?id=' + formId;
	}




	// This function is used to take the value from the textfield beside the "Add New item"
	// button on dropdown menus
	function addNewItem(id){

	//alert(id);

    var value = document.getElementById('newitem'+id).value;


     $.post("ajax_functions.php", { value: value, id: id, type: 'addvaluetodropdown'},

   		function(data) {
     		//alert("Data Loaded: " + data);
	   });

	 //alert('A new item has been added: ' + value);
       window.location = 'page2.php?id=' + formId;
	}



	// If the text field already existed, rebuilt it using data from the db.
	function recreateTextArea(uniqueId, leftText, requiredFieldValue){


			var ni = document.getElementById('formdiv');
			var newdiv = document.createElement('div');
			//newdiv.style.backgroundColor = "gray";
			newdiv.style.borderWidth = '1px';
			newdiv.style.borderStyle = 'dotted';

			newdiv.style.width = 400;
			newdiv.style.height = 110;
	        newdiv.style.marginBottom = 5;
	        newdiv.style.marginLeft = 5;

			var divIdName = 'my'+num+'Div';
	        newdiv.setAttribute('id',num);
	        ni.appendChild(newdiv);

	   	   var selectedText = '';
	   	    if(requiredFieldValue == '1'){

	   	    	       	selectedText = 'selected="selected"';
	   	    }


	   	   if(numberOfFields == 1){
	   	    		newdiv.innerHTML = '<b>'+textAreaTxt+':</b> <img src="../images/move_up_dis.gif" width="20" height="20" alt="move up" /> <img src="../images/move_down_dis.gif" width="20" height="20" alt="move down" />  <a href="page2.php?id=' + formId + '&del=' + uniqueId + '"><img src="../images/deleteIcon.png" width="20" height="20" alt="delete" /></a> '+
	   	   	 						 '<select id = "optional_'+uniqueId+'" onchange="saveOptionalStatus('+uniqueId+')"> <option value="0"> <?php echo get_string('optional_field', 'block_cmanager'); ?> </option>  <option '+selectedText+' value="1"> <?php echo get_string('required_field', 'block_cmanager'); ?></option>  </select>'+
	   	    						'<p></p><table><tr><td>'+leftTxt+':</td><td><input type="text" id = "'+uniqueId +'" size="30" value="' + leftText+ '" onfocus="enableSave(\''+uniqueId+'_savebtn\');"></input></td></tr></table><input type="button" value="'+saveTxt+'" disabled="disabled" id="'+uniqueId+'_savebtn" onclick="saveFieldValue(' + uniqueId+')"/>';


	   	    } else {
					   	   	if(num == 1){
						     		 newdiv.innerHTML = '<b>'+textAreaTxt+':</b> <img src="../images/move_up_dis.gif" width="20" height="20" alt="move up"/>  <a href="page2.php?id=' + formId + '&down=' + uniqueId + '">'+
						     		 '<img src="../images/move_down.gif" width="20" height="20" alt="move down" /></a>  <a href="page2.php?id=' + formId + '&del=' + uniqueId + '"> '+
						     		 '<img src="../images/deleteIcon.png" width="20" height="20" alt="delete" /></a> '+
						     		 '<select id = "optional_'+uniqueId+'" onchange="saveOptionalStatus('+uniqueId+')"> <option value="0"> <?php echo get_string('optional_field', 'block_cmanager'); ?> </option>  <option '+selectedText+' value="1"> <?php echo get_string('required_field', 'block_cmanager'); ?></option>  </select><p></p><table><tr><td>'+leftTxt+':</td><td><input type="text" id = "'+ uniqueId+ '" value = "' + leftText+ '" size="30" onfocus="enableSave(\''+uniqueId+'_savebtn\');"></input></td></tr></table><input type="button" value="'+saveTxt+'" disabled="disabled" id="'+uniqueId+'_savebtn" onclick="saveFieldValue(' + uniqueId+')"/>';
					       			}
					       	else if(movedownEnabled == 0){
					       	 		newdiv.innerHTML = '<b>'+textAreaTxt+':</b> <a href="page2.php?id=' + formId + '&up=' + uniqueId + '"> ' +
					       	 		'<img src="../images/move_up.gif" width="20" height="20" alt="move up" /></a> <img src="../images/move_down_dis.gif" width="20" height="20" alt="move down" /> <a href="page2.php?id=' + formId + '&del=' + uniqueId + '"> '+
					       	 		'<img src="../images/deleteIcon.png" width="20" height="20" alt="delete" /></a> '+
					       	 		'<select id = "optional_'+uniqueId+'" onchange="saveOptionalStatus('+uniqueId+')"> <option value="0"> <?php echo get_string('optional_field', 'block_cmanager'); ?> </option>  <option '+selectedText+' value="1"> <?php echo get_string('required_field', 'block_cmanager'); ?></option>  </select><p></p><table><tr><td>'+leftTxt+':</td><td><input type="text" id = "'+uniqueId +'" size="30" value="' + leftText+ '" onfocus="enableSave(\''+uniqueId+'_savebtn\');"></input></td></tr></table><input type="button" value="'+saveTxt+'" disabled="disabled" id="'+uniqueId+'_savebtn" onclick="saveFieldValue(' + uniqueId+')"/>';

					       	}
					       	else {
					       	 		newdiv.innerHTML = '<b>'+textAreaTxt+':</b> <a href="page2.php?id=' + formId + '&up=' + uniqueId + '"> '+
					       	 		'<img src="../images/move_up.gif" width="20" height="20" alt="move up" /></a> <a href="page2.php?id=' + formId + '&down=' + uniqueId + '"> '+
					       	 		'<img src="../images/move_down.gif" width="20" height="20" alt="move down" /></a>  <a href="page2.php?id=' + formId + '&del=' + uniqueId + '"> '+
					       	 		'<img src="../images/deleteIcon.png" width="20" height="20" alt="delete" /></a> '+
					       	 		'<select id = "optional_'+uniqueId+'" onchange="saveOptionalStatus('+uniqueId+')"> <option value="0"> <?php echo get_string('optional_field', 'block_cmanager'); ?> </option>  <option '+selectedText+' value="1"> <?php echo get_string('required_field', 'block_cmanager'); ?></option>  </select><p></p><table><tr><td>'+leftTxt+':</td><td><input type="text" id = "'+uniqueId +'" size="30" value="' + leftText+ '" onfocus="enableSave(\''+uniqueId+'_savebtn\');"></input></td></tr></table><input type="button" value="'+saveTxt+'" disabled="disabled" id="'+uniqueId+'_savebtn" onclick="saveFieldValue(' + uniqueId+')"/>';

					       			}
	       		}
	       			num++;
	}



			// Create a new blank text field on the page
	function createDropdown(){

		  var fieldsInHTML = '';
		  var leftText = '';
			var ni = document.getElementById('formdiv');
			var newdiv = document.createElement('div');
			//newdiv.style.backgroundColor = "gray";
			newdiv.style.borderWidth = '1px';
			newdiv.style.borderStyle = 'dotted';

			newdiv.style.width = 450;
			newdiv.style.height = 400;
	        newdiv.style.marginBottom = 5;
	        newdiv.style.marginLeft = 5;

			var divIdName = 'my'+num+'Div';
	        newdiv.setAttribute('id',num);
	        ni.appendChild(newdiv);


	        var uniqueId;
	        // Add to database
	        $.ajaxSetup({async:false});
	         $.post("ajax_functions.php", { type: 'page2addfield', fieldtype: 'dropdown', formid: formId},
   				function(data) {
		     		uniqueId = data;
		     		/*
			          	if(num == 1){
       					newdiv.innerHTML = '<b>'+dropdownTxt+':</b><img src="../images/move_up_dis.gif" width="20" height="20" alt="move up" /><a href="page2.php?id=' + formId + '&down=' + uniqueId + '"><img src="../images/move_down.gif" width="20" height="20" alt="move down" /></a> <a href="page2.php?id=' + formId + '&t=drop&del=' + uniqueId + '"><img src="../images/deleteIcon.png" width="20" height="20" alt="delete" /></a> <p></p><table><tr><td>'+leftTxt+':</td><td><input type="text" id = "'+uniqueId +'" size="30" value="' + leftText+ '" onfocus="enableSave(\''+uniqueId+'_savebtn\');"></input></td></tr></table><input type="button" value="'+saveTxt+'" disabled="disabled" id="'+uniqueId+'_savebtn" onclick="saveFieldValue(' + uniqueId+')"/><p></p> <input type="text" id="newitem'+uniqueId +'"></input><input type="button" name="submitbutton" value="'+addItemBtnTxt+'" onclick="addNewItem('+ uniqueId +');"><p></p>'+addedItemsTxt+':<p></p>' + fieldsInHTML;
	       				}
	       				else if(movedownEnabled == 0){
	       					newdiv.innerHTML = '<b>'+dropdownTxt+':</b> <a href="page2.php?id=' + formId + '&up=' + uniqueId + '"><img src="../images/move_up.gif" width="20" height="20" alt="move up" /></a> <img src="../images/move_down_dis.gif" width="20" height="20" alt="move down" /> <a href="page2.php?id=' + formId + '&t=drop&del=' + uniqueId + '"><img src="../images/deleteIcon.png" width="20" height="20" alt="delete" /></a> <p></p><table><tr><td>'+leftTxt+':</td><td><input type="text" id = "'+uniqueId +'" size="30" value="' + leftText+ '" onfocus="enableSave(\''+uniqueId+'_savebtn\');"></input></td></tr></table><input type="button" value="'+saveTxt+'" disabled="disabled" id="'+uniqueId+'_savebtn" onclick="saveFieldValue(' + uniqueId+')"/><p></p> <input type="text" id="newitem'+uniqueId +'"></input><input type="button" name="submitbutton" value="'+addItemBtnTxt+'" onclick="addNewItem('+ uniqueId +');"><p></p>'+addedItemsTxt+':<p></p>' + fieldsInHTML;

	       				}
	       				else {
	       	 			newdiv.innerHTML = '<b>'+dropdownTxt+':</b> <a href="page2.php?id=' + formId + '&up=' + uniqueId + '"><img src="../images/move_up.gif" width="20" height="20" alt="move up" /></a>  <a href="page2.php?id=' + formId + '&down=' + uniqueId + '"><img src="../images/move_down.gif" width="20" height="20" alt="move down" /></a>  <a href="page2.php?id=' + formId + '&t=drop&del=' + uniqueId + '"><img src="../images/deleteIcon.png" width="20" height="20" alt="delete" /></a> <p></p><table><tr><td>'+leftTxt+':</td><td><input type="text" id = "'+uniqueId +'" size="30" value="' + leftText+ '" onfocus="enableSave(\''+uniqueId+'_savebtn\');"></input></td></tr></table><input type="button" value="'+saveTxt+'" disabled="disabled" id="'+uniqueId+'_savebtn" onclick="saveFieldValue(' + uniqueId+')"/><p></p> <input type="text" id="newitem'+uniqueId +'"></input><input type="button" name="submitbutton" value="'+addItemBtnTxt+'" onclick="addNewItem('+ uniqueId +');"><p></p>'+addedItemsTxt+':<p></p>' + fieldsInHTML;

	       			}
	       			 */

			   });


			   num++;
			    window.location = 'page2.php?id=' + formId;
	}


	// If the text field already existed, rebuilt it using data from the db.
	function recreateDropdown(uniqueId, leftText, requiredFieldValue){

		  var fieldsInHTML = 'No fields added..';


	       // Get the values for the dropdown menu
	        $.ajaxSetup({async:false});
	        $.post("ajax_functions.php", { id: uniqueId, type: 'getdropdownvalues'},

	   		function(data) {
	     		fieldsInHTML = data;

			var ni = document.getElementById('formdiv');
			var newdiv = document.createElement('div');
			//newdiv.style.backgroundColor = "gray";
			newdiv.style.borderWidth = '1px';
			newdiv.style.borderStyle = 'dotted';

			newdiv.style.width = 400;
			newdiv.style.height = 400;
	        newdiv.style.marginBottom = 5;
	        newdiv.style.marginLeft = 5;

			var divIdName = 'my'+num+'Div';
	        newdiv.setAttribute('id',num);
	        ni.appendChild(newdiv);

	   	   var selectedText = '';
	   	    if(requiredFieldValue == '1'){

	   	    	       	selectedText = 'selected="selected"';
	   	    }


	   	   if(numberOfFields == 1){
	   	    		//newdiv.innerHTML = '<b>'+dropdownTxt+':</b> <img src="../images/move_up_dis.gif" width="20" height="20" alt="move up" /> <img src="../images/move_down_dis.gif" width="20" height="20" alt="move down" />  <a href="page2.php?id=' + formId + '&del=' + uniqueId + '"><img src="../images/deleteIcon.png" width="20" height="20" alt="delete" /></a> <p></p><table><tr><td>'+leftTxt+':</td><td><input type="text" id = "'+uniqueId +'" size="30" value="' + leftText+ '" onfocus="enableSave(\''+uniqueId+'_savebtn\');"></input></td></tr></table><input type="button" value="'+saveTxt+'" disabled="disabled" id="'+uniqueId+'_savebtn" onclick="saveFieldValue(' + uniqueId+')"/>';
				     newdiv.innerHTML = '<b>'+dropdownTxt+':</b><img src="../images/move_up_dis.gif" width="20" height="20" alt="move up" /><a href="page2.php?id=' + formId + '&down=' + uniqueId + '"> '+
				     '<img src="../images/move_down.gif" width="20" height="20" alt="move down" /></a> <a href="page2.php?id=' + formId + '&t=drop&del=' + uniqueId + '"> '+
				     '<img src="../images/deleteIcon.png" width="20" height="20" alt="delete" /></a> '+
				     '<select id = "optional_'+uniqueId+'" onchange="saveOptionalStatus('+uniqueId+')"> <option value="0"> <?php echo get_string('optional_field', 'block_cmanager'); ?> </option>  <option '+selectedText+' value="1"> <?php echo get_string('required_field', 'block_cmanager'); ?></option>  </select><p></p><table><tr><td>'+leftTxt+':</td><td><input type="text" id = "'+uniqueId +'" size="30" value="' + leftText+ '" onfocus="enableSave(\''+uniqueId+'_savebtn\');"></input></td></tr></table><input type="button" value="'+saveTxt+'" disabled="disabled" id="'+uniqueId+'_savebtn" onclick="saveFieldValue(' + uniqueId+')"/><p></p> <input type="text" id="newitem'+uniqueId +'"></input><input type="button" name="submitbutton" value="'+addItemBtnTxt+'" onclick="addNewItem('+ uniqueId +');"><p></p>'+addedItemsTxt+':<p></p>' + fieldsInHTML;

	   	    } else {

					   	   	if(num == 1){
				       					newdiv.innerHTML = '<b>'+dropdownTxt+':</b><img src="../images/move_up_dis.gif" width="20" height="20" alt="move up" /><a href="page2.php?id=' + formId + '&down=' + uniqueId + '"> '+
				       					'<img src="../images/move_down.gif" width="20" height="20" alt="move down" /></a> <a href="page2.php?id=' + formId + '&t=drop&del=' + uniqueId + '"> ' +
				       					'<img src="../images/deleteIcon.png" width="20" height="20" alt="delete" /></a> '+
				       					'<select id = "optional_'+uniqueId+'" onchange="saveOptionalStatus('+uniqueId+')"> <option value="0"> <?php echo get_string('optional_field', 'block_cmanager'); ?> </option>  <option '+selectedText+' value="1"> <?php echo get_string('required_field', 'block_cmanager'); ?></option>  </select><p></p><table><tr><td>'+leftTxt+':</td><td><input type="text" id = "'+uniqueId +'" size="30" value="' + leftText+ '" onfocus="enableSave(\''+uniqueId+'_savebtn\');"></input></td></tr></table><input type="button" value="'+saveTxt+'" disabled="disabled" id="'+uniqueId+'_savebtn" onclick="saveFieldValue(' + uniqueId+')"/><p></p> <input type="text" id="newitem'+uniqueId +'"></input><input type="button" name="submitbutton" value="'+addItemBtnTxt+'" onclick="addNewItem('+ uniqueId +');"><p></p>'+addedItemsTxt+':<p></p>' + fieldsInHTML;
					       	}
					       	else if(movedownEnabled == 0){
					       		newdiv.innerHTML = '<b>'+dropdownTxt+':</b> <a href="page2.php?id=' + formId + '&up=' + uniqueId + '"> '+
					       		'<img src="../images/move_up.gif" width="20" height="20" alt="move up" /></a> '+
					       		'<img src="../images/move_down_dis.gif" width="20" height="20" alt="move down" /> <a href="page2.php?id=' + formId + '&t=drop&del=' + uniqueId + '"> '+
					       		'<img src="../images/deleteIcon.png" width="20" height="20" alt="delete" /></a>'+
					       		'<select id = "optional_'+uniqueId+'" onchange="saveOptionalStatus('+uniqueId+')"> <option value="0"> <?php echo get_string('optional_field', 'block_cmanager'); ?> </option>  <option '+selectedText+' value="1"> <?php echo get_string('required_field', 'block_cmanager'); ?></option>  </select> <p></p><table><tr><td>'+leftTxt+':</td><td><input type="text" id = "'+uniqueId +'" size="30" value="' + leftText+ '" onfocus="enableSave(\''+uniqueId+'_savebtn\');"></input></td></tr></table><input type="button" value="'+saveTxt+'" disabled="disabled" id="'+uniqueId+'_savebtn" onclick="saveFieldValue(' + uniqueId+')"/><p></p> <input type="text" id="newitem'+uniqueId +'"></input><input type="button" name="submitbutton" value="'+addItemBtnTxt+'" onclick="addNewItem('+ uniqueId +');"><p></p>'+addedItemsTxt+':<p></p>' + fieldsInHTML;

					       	} else {

					       	 		newdiv.innerHTML = '<b>'+dropdownTxt+':</b> <a href="page2.php?id=' + formId + '&up=' + uniqueId + '"> '+
					       	 		'<img src="../images/move_up.gif" width="20" height="20" alt="move up" /></a>  <a href="page2.php?id=' + formId + '&down=' + uniqueId + '"> '+
					       	 		'<img src="../images/move_down.gif" width="20" height="20" alt="move down" /></a>  <a href="page2.php?id=' + formId + '&t=drop&del=' + uniqueId + '"> '+
					       	 		'<img src="../images/deleteIcon.png" width="20" height="20" alt="delete" /></a> '+
					       	 		'<select id = "optional_'+uniqueId+'" onchange="saveOptionalStatus('+uniqueId+')"> <option value="0"> <?php echo get_string('optional_field', 'block_cmanager'); ?> </option>  <option '+selectedText+' value="1"> <?php echo get_string('required_field', 'block_cmanager'); ?></option>  </select><p></p><table><tr><td>'+leftTxt+':</td><td><input type="text" id = "'+uniqueId +'" size="30" value="' + leftText+ '" onfocus="enableSave(\''+uniqueId+'_savebtn\');"></input></td></tr></table><input type="button" value="'+saveTxt+'" disabled="disabled" id="'+uniqueId+'_savebtn" onclick="saveFieldValue(' + uniqueId+')"/><p></p> <input type="text" id="newitem'+uniqueId +'"></input><input type="button" name="submitbutton" value="'+addItemBtnTxt+'" onclick="addNewItem('+ uniqueId +');"><p></p>'+addedItemsTxt+':<p></p>' + fieldsInHTML;

					       			}
	       			}
		   });

	       			num++;
	}


	function createRadio(){

		  var fieldsInHTML = '';
		  var leftText = '';
			var ni = document.getElementById('formdiv');
			var newdiv = document.createElement('div');
			//newdiv.style.backgroundColor = "gray";
			newdiv.style.borderWidth = '1px';
			newdiv.style.borderStyle = 'dotted';

			newdiv.style.width = 450;
			newdiv.style.height = 400;
	        newdiv.style.marginBottom = 5;
	        newdiv.style.marginLeft = 5;

			var divIdName = 'my'+num+'Div';
	        newdiv.setAttribute('id',num);
	        ni.appendChild(newdiv);


	        var uniqueId;
	        // Add to database
	        $.ajaxSetup({async:false});
	         $.post("ajax_functions.php", { type: 'page2addfield', fieldtype: 'radio', formid: formId},
   				function(data) {
		     		uniqueId = data;
		     		/*
			          	if(num == 1){
		     		 newdiv.innerHTML = '<b>'+radioTxt+':</b>  <img src="../images/move_up_dis.gif" width="20" height="20" alt="move up" /> <a href="page2.php?id=' + formId + '&down=' + uniqueId + '"><img src="../images/move_down.gif" width="20" height="20" alt="move down" /></a>  <a href="page2.php?del=' + uniqueId + '"><img src="../images/deleteIcon.png" width="20" height="20" alt="delete" /></a><p></p><table><tr><td>'+leftTxt+':</td><td><input type="text" id = "'+ uniqueId+ '" value = "' + leftText+ '" size="30" onfocus="enableSave(\''+uniqueId+'_savebtn\');"></input></td></tr></table><input type="button" value="'+saveTxt+'" disabled="disabled" id="'+uniqueId+'_savebtn" onclick="saveFieldValue(' + uniqueId+')"/><input type="text" id="newitem"></input><p></p><input type="button" name="submitbutton" value="'+addItemBtnTxt+'" onclick="addNewItem('+ uniqueId +');">';
	       			}
	       			else if(movedownEnabled == 0){
	       				newdiv.innerHTML = '<b>'+radioTxt+':</b> <a href="page2.php?id=' + formId + '&up=' + uniqueId + '"><img src="../images/move_up.gif" width="20" height="20" alt="move up" /></a> <img src="../images/move_down_dis.gif" width="20" height="20" alt="move down" /> <a href="page2.php?id=' + formId + '&t=drop&del=' + uniqueId + '"><img src="../images/deleteIcon.png" width="20" height="20" alt="delete" /></a> <p></p><table><tr><td>'+leftTxt+':</td><td><input type="text" id = "'+uniqueId +'" size="30" value="' + leftText+ '" onfocus="enableSave(\''+uniqueId+'_savebtn\');"></input></td></tr></table><input type="button" value="'+saveTxt+'" disabled="disabled" id="'+uniqueId+'_savebtn" onclick="saveFieldValue(' + uniqueId+')"/><p></p> <input type="text" id="newitem'+uniqueId +'"></input><input type="button" name="submitbutton" value="'+addItemBtnTxt+'" onclick="addNewItem('+ uniqueId +');"><p></p>'+addedItemsTxt+':<p></p>' + fieldsInHTML;

	       			}
	       			 else {
	       	 			newdiv.innerHTML = '<b>'+radioTxt+':</b> <a href="page2.php?id=' + formId + '&up=' + uniqueId + '"><img src="../images/move_up.gif" width="20" height="20" alt="move up" /></a>  <a href="page2.php?id=' + formId + '&down=' + uniqueId + '"><img src="../images/move_down.gif" width="20" height="20" alt="move down" /></a>  <a href="page2.php?id=' + formId + '&t=drop&del=' + uniqueId + '"><img src="../images/deleteIcon.png" width="20" height="20" alt="delete" /></a> <p></p><table><tr><td>'+leftTxt+':</td><td><input type="text" id = "'+uniqueId +'" size="30" value="' + leftText+ '" onfocus="enableSave(\''+uniqueId+'_savebtn\');"></input></td></tr></table><input type="button" value="'+saveTxt+'" disabled="disabled" id="'+uniqueId+'_savebtn" onclick="saveFieldValue(' + uniqueId+')"/><p></p> <input type="text" id="newitem'+uniqueId +'"></input><input type="button" name="submitbutton" value="'+addItemBtnTxt+'" onclick="addNewItem('+ uniqueId +');"><p></p>'+addedItemsTxt+':<p></p>' + fieldsInHTML;

	       			}
	       			 */

			   });


			   num++;
			    window.location = 'page2.php?id=' + formId;
	}


	// If the text field already existed, rebuilt it using data from the db.
	function recreateRadio(uniqueId, leftText, requiredFieldValue){

		  var fieldsInHTML = 'No fields added..';



	       // Get the values for the dropdown menu
	        $.ajaxSetup({async:false});
	        $.post("ajax_functions.php", { id: uniqueId, type: 'getdropdownvalues'},

	   		function(data) {

			fieldsInHTML = data;
			var ni = document.getElementById('formdiv');
			var newdiv = document.createElement('div');
			//newdiv.style.backgroundColor = "gray";
			newdiv.style.borderWidth = '1px';
			newdiv.style.borderStyle = 'dotted';

			newdiv.style.width = 400;
			newdiv.style.height = 400;
	        newdiv.style.marginBottom = 5;
	        newdiv.style.marginLeft = 5;
			newdiv.style.overflow = 'auto';
			var divIdName = 'my'+num+'Div';
	        newdiv.setAttribute('id',num);
	        ni.appendChild(newdiv);

	   	var selectedText = '';
	   	    if(requiredFieldValue == '1'){

	   	    	       	selectedText = 'selected="selected"';
	   	    }


 	   if(numberOfFields == 1){
 	      // newdiv.innerHTML = '<b>'+radioTxt+':</b> <img src="../images/move_up_dis.gif" width="20" height="20" alt="move up" /><img src="../images/move_down_dis.gif" width="20" height="20" alt="move down" /> <a href="page2.php?id=' + formId + '&del=' + uniqueId + '"><img src="../images/deleteIcon.png" width="20" height="20" alt="delete" /></a> <p></p><table><tr><td>'+leftTxt+':</td><td><input type="text" id = "'+uniqueId +'" size="30" value="' + leftText+ '" onfocus="enableSave(\''+uniqueId+'_savebtn\');"></input></td></tr></table><input type="button" value="'+saveTxt+'" disabled="disabled" id="'+uniqueId+'_savebtn" onclick="saveFieldValue(' + uniqueId+')"/>';
			newdiv.innerHTML = '<b>'+radioTxt+':</b>  <img src="../images/move_up_dis.gif" width="20" height="20" alt="move up" /> <a href="page2.php?id=' + formId + '&down=' + uniqueId + '"> '+
			'<img src="../images/move_down.gif" width="20" height="20" alt="move down" /></a>  <a href="page2.php?id=' + formId + '&del=' + uniqueId + '"> '+
			'<img src="../images/deleteIcon.png" width="20" height="20" alt="delete" /></a> '+
			'<select id = "optional_'+uniqueId+'" onchange="saveOptionalStatus('+uniqueId+')"> <option value="0"> <?php echo get_string('optional_field', 'block_cmanager'); ?> </option>  <option '+selectedText+' value="1"> <?php echo get_string('required_field', 'block_cmanager'); ?></option>  </select><p></p><table><tr><td>'+leftTxt+':</td><td><input type="text" id = "'+uniqueId +'" size="30" value="' + leftText+ '" onfocus="enableSave(\''+uniqueId+'_savebtn\');"></input></td></tr></table><input type="button" value="'+saveTxt+'" disabled="disabled" id="'+uniqueId+'_savebtn" onclick="saveFieldValue(' + uniqueId+')"/><p></p> <input type="text" id="newitem'+uniqueId +'"></input><input type="button" name="submitbutton" value="'+addItemBtnTxt+'" onclick="addNewItem('+ uniqueId +');"><p></p>'+addedItemsTxt+':<p></p>' + fieldsInHTML;

		} else {


	   	   	if(num == 1){
					newdiv.innerHTML = '<b>'+radioTxt+':</b>  <img src="../images/move_up_dis.gif" width="20" height="20" alt="move up" /> <a href="page2.php?id=' + formId + '&down=' + uniqueId + '"> '+
					'<img src="../images/move_down.gif" width="20" height="20" alt="move down" /></a>  <a href="page2.php?id=' + formId + '&del=' + uniqueId + '"> '+
					'<img src="../images/deleteIcon.png" width="20" height="20" alt="delete" /></a> '+
					'<select id = "optional_'+uniqueId+'" onchange="saveOptionalStatus('+uniqueId+')"> <option value="0"> <?php echo get_string('optional_field', 'block_cmanager'); ?> </option>  <option '+selectedText+' value="1"> <?php echo get_string('required_field', 'block_cmanager'); ?></option>  </select><p></p><table><tr><td>'+leftTxt+':</td><td><input type="text" id = "'+uniqueId +'" size="30" value="' + leftText+ '" onfocus="enableSave(\''+uniqueId+'_savebtn\');"></input></td></tr></table><input type="button" value="'+saveTxt+'" disabled="disabled" id="'+uniqueId+'_savebtn" onclick="saveFieldValue(' + uniqueId+')"/><p></p> <input type="text" id="newitem'+uniqueId +'"></input><input type="button" name="submitbutton" value="'+addItemBtnTxt+'" onclick="addNewItem('+ uniqueId +');"><p></p>'+addedItemsTxt+':<p></p>' + fieldsInHTML;


					}
	       	else if(movedownEnabled == 0){
	       				newdiv.innerHTML = '<b>'+radioTxt+':</b> <a href="page2.php?id=' + formId + '&up=' + uniqueId + '"> '+
	       				'<img src="../images/move_up.gif" width="20" height="20" alt="move up" /></a> <img src="../images/move_down_dis.gif" width="20" height="20" alt="move down" /> <a href="page2.php?id=' + formId + '&t=drop&del=' + uniqueId + '"> '+
	       				'<img src="../images/deleteIcon.png" width="20" height="20" alt="delete" /></a> '+
	       				'<select id = "optional_'+uniqueId+'" onchange="saveOptionalStatus('+uniqueId+')"> <option value="0"> <?php echo get_string('optional_field', 'block_cmanager'); ?> </option>  <option '+selectedText+' value="1"> <?php echo get_string('required_field', 'block_cmanager'); ?></option>  </select><p></p><table><tr><td>'+leftTxt+':</td><td><input type="text" id = "'+uniqueId +'" size="30" value="' + leftText+ '" onfocus="enableSave(\''+uniqueId+'_savebtn\');"></input></td></tr></table><input type="button" value="'+saveTxt+'" disabled="disabled" id="'+uniqueId+'_savebtn" onclick="saveFieldValue(' + uniqueId+')"/><p></p> <input type="text" id="newitem'+uniqueId +'"></input><input type="button" name="submitbutton" value="'+addItemBtnTxt+'" onclick="addNewItem('+ uniqueId +');"><p></p>'+addedItemsTxt+':<p></p>' + fieldsInHTML;

	       	} else {
	       	 			newdiv.innerHTML = '<b>'+radioTxt+':</b> <a href="page2.php?id=' + formId + '&up=' + uniqueId + '"> '+
	       	 			'<img src="../images/move_up.gif" width="20" height="20" alt="move up" /></a>  <a href="page2.php?id=' + formId + '&down=' + uniqueId + '"> '+
	       	 			'<img src="../images/move_down.gif" width="20" height="20" alt="move down" /></a>  <a href="page2.php?id=' + formId + '&t=drop&del=' + uniqueId + '"> '+
	       	 			'<img src="../images/deleteIcon.png" width="20" height="20" alt="delete" /></a> '+
	       	 			'<select id = "optional_'+uniqueId+'" onchange="saveOptionalStatus('+uniqueId+')"> <option value="0"> <?php echo get_string('optional_field', 'block_cmanager'); ?> </option>  <option '+selectedText+' value="1"> <?php echo get_string('required_field', 'block_cmanager'); ?></option>  </select><p></p><table><tr><td>'+leftTxt+':</td><td><input type="text" id = "'+uniqueId +'" size="30" value="' + leftText+ '" onfocus="enableSave(\''+uniqueId+'_savebtn\');"></input></td></tr></table><input type="button" value="'+saveTxt+'" disabled="disabled" id="'+uniqueId+'_savebtn" onclick="saveFieldValue(' + uniqueId+')"/><p></p> <input type="text" id="newitem'+uniqueId +'"></input><input type="button" name="submitbutton" value="'+addItemBtnTxt+'" onclick="addNewItem('+ uniqueId +');"><p></p>'+addedItemsTxt+':<p></p>' + fieldsInHTML;

	       			}
	       			}
		   });

	       			num++;
	}


	// Saves the text field data to the database
	// by passing the field id.
	function saveFieldValue(id){

		var value = document.getElementById(id).value;
        var currentId = id;
       $.ajaxSetup({async:false});
        $.post("ajax_functions.php", { type: 'updatefield', id: currentId, value: value},
   				function(data) {


			   });



			   window.location = 'page2.php?id=' + formId;


	}




</script>
<?php

class courserequest_form extends moodleform {

    function definition() {

		global $CFG;
        global $USER;
        $mform =& $this->_form; // Don't forget the underscore!
		global $formId;
	   	global $formName;
  		$mform->addElement('header', 'mainheader', '<span style="font-size:18px">'.get_string('formBuilder_p2_header','block_cmanager').'</span>');

		$htmlOutput = '<br>
		<a href="form_builder.php">< '.get_string('back','block_cmanager').'</a><br>
			<br><b>'.get_string('formBuilder_editingForm','block_cmanager').':</b> ' .$formName.'<br><br>
			'.get_string('formBuilder_p2_instructions','block_cmanager').'
			<hr><p></p><br>
	 		'.get_string('formBuilder_p2_addNewField','block_cmanager').':
			<select onchange="addNewField(this);">
			   <option>'.get_string('formBuilder_p2_dropdown1','block_cmanager').'</option>
			   <option value="tf">'.get_string('formBuilder_p2_dropdown2','block_cmanager').'</option>
			   <option value="ta">'.get_string('formBuilder_p2_dropdown3','block_cmanager').'</option>
			   <option value="radio">'.get_string('formBuilder_p2_dropdown4','block_cmanager').'</option>
			   <option value="dropdown">'.get_string('formBuilder_p2_dropdown5','block_cmanager').'</option>
			</select>

			<p></p>
			<br>
			<hr>
			<div style="width: 100%; filter:alpha(Opacity=50); overflow:auto;">
			<div style="background: #9c9c9c;">
			<br>
			<center><b>' .$formName.' '.get_string('formBuilder_shownbelow','block_cmanager').'</b></center><br>
			</div><p></p><br>


	<script type="text/javascript">
				setLangStrings("'.get_string('formBuilder_dropdownTxt','block_cmanager').'","'.get_string('formBuilder_radioTxt','block_cmanager').'","'.get_string('formBuilder_textAreaTxt','block_cmanager').'","'.get_string('formBuilder_textFieldTxt','block_cmanager').'","'.get_string('formBuilder_leftTxt','block_cmanager').'","'.get_string('formBuilder_saveTxt','block_cmanager').'","'.get_string('formBuilder_addedItemsTxt','block_cmanager').'","'.get_string('formBuilder_addItemBtnTxt','block_cmanager').'")
			</script>




			<div id="formdiv" style="width:400px">


			</div>

			<a href="preview.php?id=' . $formId . '">'.get_string('formBuilder_previewForm','block_cmanager').'</a>
			<center><a href="../cmanager_admin.php"><input type="button" value="'.get_string('formBuilder_returntoCM','block_cmanager').'"/></a></center>
		';




	 	$mform->addElement('html', $htmlOutput);

	}
}

$mform = new courserequest_form();//name of the form you defined in file above.

if ($mform->is_cancelled()){


} else if ($fromform=$mform->get_data()){



} else {


}

		$mform->focus();
		$mform->display();
		echo $OUTPUT->footer();


		// If any fields currently exist, add them to the page for editing
		$selectQuery = "";

		// Count the total number of records
		$numberOfFields = $DB->count_records('block_cmanager_formfields', array('formid'=>$formId));
		echo '<script>numberOfFields = '.$numberOfFields.';</script>';

	   //$formFields = $DB->get_records('block_cmanager_formfields', 'formid', $formId, $sort='position ASC', $fields='*', $limitfrom='', $limitnum='');
		$formFields = $DB->get_records('block_cmanager_formfields', array('formid'=>$formId), 'position ASC');


	    $recCounter = 1;
		foreach($formFields as $field){

			   // If we are on the last record, disable the move down option.
			   if($numberOfFields == $recCounter || $numberOfFields == 1){

				    echo '<script>movedownEnabled = 0;</script>';

			   }


			   if($field->type == 'textfield'){

				echo "<script>
				       recreateTextField('". $field->id ."', '". $field->lefttext ."', '". $field->reqfield ."');
			      </script>
			      ";
			   }
			   else if($field->type == 'textarea'){
			   	echo "<script>
				       recreateTextArea('". $field->id ."', '". $field->lefttext ."', '". $field->reqfield ."');
			      </script>
			      ";
			   }
			   else if($field->type == 'dropdown'){
			   	echo "<script>
				       recreateDropdown('". $field->id ."', '". $field->lefttext ."', '". $field->reqfield ."');
			      </script>
			      ";
			   }

			   else if($field->type == 'radio'){
			   	echo "<script>
				       recreateRadio('". $field->id ."', '". $field->lefttext ."', '". $field->reqfield ."');
			      </script>
			      ";
			   }

			   $recCounter++;
		}


