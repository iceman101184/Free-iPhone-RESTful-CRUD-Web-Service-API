Notes on CRUD Webservice use:

Purpose: this is a free RESTful API to do basic crud operations on various database objects. 

This is an open source product. You can use it to create a webservice for any need you may have. The variables at the top
of index.php expose all the necessary calls (i.e. "actions") you may need (e.g. insertEvent, updateUser, etc).

This API also allows for required fields, which is an added perk. This product is provided "as is." If you 
need any assistance getting it to work for your needs, you may email arts@loudcanvas.com. We offer paid consulting
and assistance for larger web development efforts. 

NOTES ON USE:

	EVENTS:
	$http.get('/events')  Gets all the events
	$http.get('/events/10')  Get the event with id = 10
	$http.post('/insertEvent', event);  Insert a new event
	$http.delete('/deleteEvent/10');  Delete the event with id=10
	$http.post('/updateEvent/10', event);  Updates the event with id = 10

	USERS:
	$http.get('/users')  Gets all the users
	$http.get('/users/24')  Get the user with id = 24
	$http.post('/insertUser', user);  Insert a new user
	$http.delete('/deleteUser/24');  Delete the user with id = 24
	$http.post('/updateUser/24', user);  Updates the user with id = 24

	EVENT ATTENDEES:
	$http.get('/attendees')  Gets all the attendees
	$http.get('/attendees/10')  Get the attendee with EVENT id = 10
	$http.post('/insertAttendee', attendee);  Insert a new attendee
	$http.delete('/deleteAttendee/100');  Delete the attendee with id = 100

	INVITES (TO EVENTS):
	$http.post('/insertInvite', invite);  Insert an event invite, invite obj = {fkEventID: eventID, fldEmail: email}


PERK (/upload/index.php):
Image upload web service. As an added perk, there is an "upload image" web service to aid in iPhone or other app development.

NB:

1) URL STRUCTURE / "GET" REQUEST ACCESS:
   The .htaccess file directs web/GET request traffic via the URL synax specified above. If you POST to the web service, be 
   sure to include an "action" variable for the action (e.g. "deleteUser"), an "id" field (when applicable) for the id, and
   a "dataObject" variable for the object (when applicable)

2) API NOT SECURE AS IS:
   This API currently grants UNSECURED database access. When operationalizing this for PROD use, be sure to include 
   a security module. At a minimum, proper API security should require an APIKey and APIPassword be passed along with 
   all web service requests and the credentials be validated before execution. 