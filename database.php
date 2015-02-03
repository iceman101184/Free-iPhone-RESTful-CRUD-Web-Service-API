<?php

	$db_host = "localhost";
	$db_user = "username";
	$db_pass = "password";
	$db_name = "database_name";

	$db = mysql_connect($db_host,$db_user,$db_pass);
	mysql_select_db($db_name);


	function q($q, $debug = 0){
		$r = mysql_query($q);
		if(mysql_error()){
			echo mysql_error();
			echo "$q<br>";
		}

		if($debug == 1)
			echo "<br>$q<br>";

		if(stristr(substr($q,0,8),"delete") ||	stristr(substr($q,0,8),"insert") || stristr(substr($q,0,8),"update")){
			if(mysql_affected_rows() > 0)
				return true;
			else
				return false;
		}
		if(mysql_num_rows($r) > 1){
			while($row = mysql_fetch_array($r, MYSQL_ASSOC)){
				$results[] = $row;
			}
		}
		else if(mysql_num_rows($r) == 1){
			$results = array();
			$results[] = mysql_fetch_array($r, MYSQL_ASSOC);
		}

		else
			$results = array();

		return $results;
	}

	function q1($q, $debug = 0){
		$r = mysql_query($q);
		if(mysql_error()){
			echo mysql_error();
			echo "<br>$q<br>";
		}

		if($debug == 1)
			echo "<br>$q<br>";
		$row = @mysql_fetch_array($r);

		if(count($row) == 2)
			return $row[0];
		else
			return $row;
	}

	function qr($q, $debug = 0){
		$r = mysql_query($q);
		if(mysql_error()){
			return false;
			//echo mysql_error();
			//echo "<br>$q<br>";
		}

		if($debug == 1)
			echo "<br>$q<br>";

		if(stristr(substr($q,0,8),"delete") ||	stristr(substr($q,0,8),"insert") ||  stristr(substr($q,0,8),"update")){
			if(mysql_affected_rows() > 0){
				return true;
			}
			else{
				if(stristr(substr($q,0,8),"update")){
					//return true; //return true even if the update did not update anything (per kayla on 2/1/15 @ 4:10pm)
				}
				return false;
			}
		}

		$results = array();
		$results[] = mysql_fetch_array($r, MYSQL_ASSOC);
		$results = $results[0];


		return $results;
	}
?>