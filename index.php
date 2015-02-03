<?php
	include("preheader.php");
	if (!function_exists('http_response_code')){
		function http_response_code($code){
			header('X-PHP-Response-Code: $code', true, $code);
		}
	}

	$databaseEntities 				= array("events", "attendees", "users", "invites"); //the database objects this API will use
	$databaseTable 					= array(); //the database table for the object (e.g. tblEvent would be set for $databaseTable['events'])
	$databasePK						= array(); //the primary key for the table
	$readIdentifier 				= array(); //for reads (selects) - the key or foreign key used for individual selects
	$allowedActions 				= array(); //allowed actions for that object (e.g. insertEvent)
	$requiredFields 				= array(); //required fields set for the object (to enforce database integrity)

	//events
	$databaseTable['events'] 		= "tblEvent";
	$databasePK['events']			= "pkEventID";
	$readIdentifier['events'] 		= "pkEventID";
	$allowedActions['events'] 		= array("insertevent", "updateevent", "deleteevent");
	$requiredFields['events'] 		= array("fldTitle", "fkUserID");

	//attendees
	$databaseTable['attendees'] 	= "tblAttendee";
	$databasePK['attendees']		= "pkAttendeeID";
	$readIdentifier['attendees'] 	= "fkEventID";
	$allowedActions['attendees'] 	= array("insertattendee", "updateattendee", "deleteattendee");
	$requiredFields['attendees'] 	= array("fkEventID", "fkUserID");

	//users
	$databaseTable['users'] 		= "tblUser";
	$databasePK['users']			= "pkUserID";
	$readIdentifier['users'] 		= "pkUserID";
	$allowedActions['users'] 		= array("insertuser", "updateuser", "deleteuser");
	$requiredFields['users'] 		= array("fldFirstName", "fldLastName", "fldEmail");

	//invites
	$databaseTable['invites'] 		= "tblEventInvite";
	$databasePK['invites']			= "pkEventInviteID";
	$readIdentifier['invites'] 		= NULL;
	$allowedActions['invites'] 		= array("insertinvite");
	$requiredFields['invites'] 		= array("fkEventID", "fldEmail", "fldEmail");

/*
	Notes on Use:

	EVENTS:
	$http.get('/events')  Gets all the events
	$http.get('/events/10')  Get the event with id = 10
	$http.post('/insertEvent', event);  Insert a new event
	$http.delete('/deleteEvent/10');  Delete the event with id=10
	$http.post('/updateEvent/10', event);  Updates the event with id = 10 (NOT CODED YET)

	USERS:
	$http.get('/users')  Gets all the users
	$http.get('/users/24')  Get the user with id = 24
	$http.post('/insertUser', user);  Insert a new user
	$http.delete('/deleteUser/24');  Delete the user with id = 24
	$http.post('/updateUser/24', user);  Updates the user with id = 24 (NOT CODED YET)

	EVENT ATTENDEES:
	$http.get('/attendees')  Gets all the attendees
	$http.get('/attendees/10')  Get the attendee with EVENT id = 10
	$http.post('/insertAttendee', attendee);  Insert a new attendee
	$http.delete('/deleteAttendee/100');  Delete the attendee with id=100

	INVITES (TO EVENTS):
	$http.post('/insertInvite', invite);  Insert an event invite (fkEventID => eventID, fldEmailAddress => email)

*/

	$testing = false;
	if ($_REQUEST['testing'] == "true"){
		$testing = true;
	}

	//POSTS
	if (isset($_POST['action']) && $_POST['action'] != ""){
		$action 	= strtolower(trim($_POST['action']));
		$dataObject = $_POST['object'];
		$id 		= $_POST['id']; //for deletes & updates
	}
	else{
		//logic to parse URL
		//GETS
		if ($_REQUEST['x']){
			$command 	= trim( $_REQUEST['x']);
			$command 	= explode("/", $command);//splint into an array. [0] = entity; [1] = id (if applicable)
			$action 	= $command[0];
			$action 	= strtolower($action);
			if (strstr($action, "insert") === FALSE && strstr($action, "update") === FALSE){
				//just reads
				$id 	= $command[1];
				if (isset($id)){
					$dataObject = $command[2];//for updates the object is the third param
				}
			}
			else{
				$dataObject = $command[1];
				if (strstr($action, "update") != FALSE){
					$id 	= $command[1];
					if (isset($id)){
						$dataObject = $command[2];//for updates the object is the third param
					}
				}
			}

			if (isset($id) && $id != "" && $id != "/"){
				if (is_numeric($id)){}
				else{
					http_response_code(409);
					die(json_encode(array(	"error" => "id was not a valid integer")));
				}
			}

			if (!$action){
				http_response_code(409);
				die(json_encode(array(	"error" => "no valid action sent",
										"msg" => "x param did not contain a action command, i.e. /{action}/{object} or {id}")));
			}
		}//if x param sent
		else{
			http_response_code(409);
			die(json_encode(array(	"error" => "no valid command sent via x parameter.",
									"msg" => "no x param was provided.")));
		}
	}

	/***************************** SELECT (C*R*UD) **********************/

	//check/confirm it is not an insert or a delete; if so it is a get/fetch
	if (strstr($action, "insert") === FALSE && strstr($action, "update") === FALSE && strstr($action, "delete") === FALSE){

		$fetchWhat = $action; //table psudeoname (e.g. 'events', 'attendees', or 'users')
		$singularFetch = substr($fetchWhat, 0, -1); //removes the "s"

		if (isset($fetchWhat)){
			$matchFound = false;
			foreach ($databaseEntities as $databaseEntity){
				if ($fetchWhat == $databaseEntity){
					$tableQueried 		= $databaseTable[$databaseEntity];
					$readIdentifierUsed = $readIdentifier[$databaseEntity];

					if ($id){
						$getQuery = "SELECT * FROM $databaseTable[$databaseEntity] WHERE $readIdentifier[$databaseEntity] = $id";
					}
					else{
						$getQuery = "SELECT * FROM $databaseTable[$databaseEntity]";
					}
					$matchFound = true;
				}
			}

			if (!$matchFound){
				http_response_code(409);
				die(json_encode(array("error" => "not valid fetch request (no table exists for alias *$fetchWhat*)")));
			}
		}//if read GET was sent

		if ($getQuery){
			$databaseItems = q($getQuery);

			// array to hold the data
			$resultArray = array();
			$tempArray = array();

			if (count($databaseItems) > 0){
				// Loop through each row in the result set
				foreach($databaseItems as $item) {

					if ($fetchWhat == "events"){
						$eventID = $item['pkEventID'];

						//second query for getting people invited to event(s)
						$peopleInvited = q("SELECT fldEmail FROM tblEventInvite WHERE fkEventID = $eventID");
						$item['fldInvitedUsers'] = $peopleInvited;
					}

					// Add each row into our results array
					$tempArray = $item;
					$resultArray[] = $tempArray;
				}

				// Finally, encode the array to JSON and output the results
				if (!$testing){
					//echo json_encode($resultArray, JSON_FORCE_OBJECT);
					echo json_encode($resultArray);
				}
				else{
					print_r($resultArray);
				}
			}
			else{
				http_response_code(409);
				die(json_encode(array(	"error" => "no $what rows found with this id",
										"msg" => "table " . $tableQueried . " did not have any rows using the selector " . $readIdentifierUsed . " = $id" )));
			}
		}//if getQuery
	}//not an insert or delete
	else{
		//is an insert, update, or delete

		/***************************** DELETE (CRU*D*) **********************/

		if (strstr($action, "delete") !== FALSE){

			$deleteWhat = $action; //eg deleteEvents

			if (!isset($id)){
				http_response_code(409);
				die(json_encode(array(	"error" => "no id was passed for $deleteWhat.")));
			}

			if (isset($deleteWhat) && $id != ""){
				$deleteQuery = false;
				foreach ($databaseEntities as $databaseEntity){
					if ( array_search($deleteWhat, $allowedActions[$databaseEntity]) !== FALSE){
						$deleteQuery = "DELETE FROM $databaseTable[$databaseEntity] WHERE $databasePK[$databaseEntity] = $id";
					}
				}

				if ($deleteQuery){
					$success = qr($deleteQuery);

					// Finally, encode the array to JSON and output the results
					if($success){
						http_response_code(200);
						echo json_encode(array("success" => "$action id $id deleted"));
					}
					else{
						http_response_code(409);
						die(json_encode(array("error" => "$action id $id could not be deleted. Likely row with id $id does not exist")));
					}
				}
				else{
					http_response_code(409);
					die(json_encode(array("error" => "not valid delete request (no table exists for alias *$deleteWhat*)")));
				}

			}//if deleteWhat isset and id is set

		}//delete


		//logic used for both insert and update
		if (strstr($action, "insert") !== FALSE || strstr($action, "update") !== FALSE){
			if ($dataObject != ""){
				//get the json converted to an object
				$dataObject 	= str_replace('\"', '"', $dataObject); //strip the extra slashes (I had these in the database by default using add_slashes)
				$decodedJSON 	= json_decode($dataObject, true); // 1st param contains JSON contents. 2nd parameter (true) converts the string into php associative array

				if (!$decodedJSON){
					http_response_code(409);
					die(json_encode(array(	"error" => "JSON did not decode properly.",
											"msg" => "please check your JSON string: $dataObject")));
				}
				else{
					$jsonVars = print_r($decodedJSON, true);
				}
			}
		}

		/***************************** INSERT (*C*RUD) **********************/

		if (strstr($action, "insert") !== FALSE){

			foreach ($databaseEntities as $databaseEntity){
				if ( array_search($action, $allowedActions[$databaseEntity]) !== FALSE){
					$table = $databaseTable[$databaseEntity];
					$what = str_replace("insert", "", $action);//make it singular (lose the "insert")

					if ($action == "insertuser"){
						//object logic
						$decodedJSON['fldCreateDate'] = date("Y-m-d H:i:s");
					}

					//enforce required fields be set
					$errors = false;
					$missingFields = array();
					foreach ($requiredFields[$databaseEntity] as $requiredField){
						if (!isset($decodedJSON[$requiredField])){
							$errors = true;
							$missingFields[] = $requiredField;
						}
					}
					if ($errors){
						$missingFields = implode(", ", $missingFields);
						http_response_code(409);
						die(json_encode(array(	"error" => "Required field(s) are omitted.",
												"msg" => "the following field(s) are missing: $missingFields",
												"jsonVarsSent" => $jsonVars)));
					}

				}//if a valid insert request
			}

			if ($table){
				//$to_insert = (array)($row);
				$insertSQL = mysql_insert($table, $decodedJSON);
				$success = qr($insertSQL);

				// Finally, encode the array to JSON and output the results
				if($success){
					http_response_code(200);
					echo json_encode(array("success" => "$what successfully added."));
				}
				else{
					$dbError = mysql_error();
					http_response_code(409);
					die(json_encode(array("error" => "$what could not be inserted.", "msg" => "Error: $dbError", "query" => $insertSQL)));
				}
			}
			else{
				http_response_code(409);
				die(json_encode(array(	"error" => "not valid insert request.",
										"msg" => "no table exists for alias *$action*")));
			}
		}//is an insert


		/***************************** UPDATE (CR*U*D) **********************/

		if (strstr($action, "update") !== FALSE){
			if (!$id){
				http_response_code(409);
				die(json_encode(array("error" => "no id provided for $action update.")));
			}

			if ($id != ""){
				foreach ($databaseEntities as $databaseEntity){
					if ( array_search($action, $allowedActions[$databaseEntity]) !== FALSE){
						$what = str_replace("update", "", $action);//make it singular (lose the "update")
						$table = $databaseTable[$databaseEntity];
						$pk = $databasePK[$databaseEntity];

						//enforce required fields be set
						$errors = false;
						$missingFields = array();
						foreach ($requiredFields[$databaseEntity] as $requiredField){
							if (!isset($decodedJSON[$requiredField])){
								$errors = true;
								$missingFields[] = $requiredField;
							}
						}
						if ($errors){
							$missingFields = implode(", ", $missingFields);
							http_response_code(409);
							die(json_encode(array(	"error" => "Required field(s) are omitted.",
													"msg" => "the following field(s) are missing: $missingFields",
													"jsonVarsSent" => $jsonVars)));
						}

					}//if a valid insert request
				}//foreach

				if ($table){
					foreach($decodedJSON as $field => $val){ // Check the customer received. If key does not exist, insert blank into the array.
						$columns = $columns . $field . " = \"" . $val . "\", ";
					}

					$doesRowExist = q1("SELECT COUNT(*) FROM $table WHERE $pk = $id");
					if ($doesRowExist > 0){
						$updateSQL = "UPDATE $table SET ".trim($columns,', ')." WHERE $pk = $id";
						$success = qr($updateSQL);

						if($success){
							http_response_code(200);
							echo json_encode(array("success" => "$what $id successfully updated."));
						}
						else{
							http_response_code(200);
							die(json_encode(array(	"success" => "Database update successfully executed",
													"msg" => "No fields were changed; no $what data altered.")));
						}
					}
					else{
						http_response_code(409);
						die(json_encode(array(	"error" => "$what could not be updated; likely no row exists for id $id",
												"msg" => "Error: $dbError", "query" => $updateSQL)));
					}
				}
				else{
					http_response_code(409);
					die(json_encode(array(	"error" => "not valid update request.",
											"msg" => "no table exists for alias *$action*")));
				}
			}//if id provided
		}//if update
	}//is an insert, update, or delete

	function mysql_insert($table, $inserts) {
		$values = array_map('mysql_real_escape_string', array_values($inserts));
		$keys = array_keys($inserts);

		return "INSERT INTO `" . $table . "` (`" . implode('`,`', $keys) . "`) VALUES ('" . implode('\',\'', $values) . "')";
	}
?>