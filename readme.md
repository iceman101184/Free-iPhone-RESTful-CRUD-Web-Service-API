Notes on CRUD Webservice use:

Purpose: this is a free RESTful API to do basic crud operations on various database objects. 

This is an open source product. You can use it to create a webservice for any need you may have. The variables at the top
of index.php expose all the necessary calls (i.e. "actions") you may need (e.g. insertEvent, updateUser, etc).

This API also allows for required fields, which is an added perk. This product is provided "as is." If you 
need any assistance getting it to work for your needs, you may email arts@loudcanvas.com. We offer paid consulting
and assistance with larger web development efforts. 

NOTES ON USE:

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
	$http.post('/insertInvite', invite);  Insert an event invite, invite obj = {fkEventID: eventID, fldEmail: email}


PERK:
Image upload web service. As an added perk, there is an "upload image" web service to aid in iPhone or other app development.