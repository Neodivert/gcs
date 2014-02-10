<?php
	session_start();

	/*
		Set users' dir.
	*/
	if( is_dir ( '/home/garoe/gcs_contents/' ) ){
		$users_dir = '/home/garoe/gcs_contents/';
	}else{
		$users_dir = '/home/moises/gcs_contents/';
	}
 	

	/*
		ConnectToDB: Try to connect to "gcs" db on localhost.
	  	Returns a MySQL link identifier on success, or kills the execution
	  	on failure.
	*/
	function ConnectToDB ()
	{
		$db_connection = mysql_connect("localhost", "root", "");
		if( !$db_connection ){ 
			die( 'ERROR when trying to connect to DB' );
		}


		if( !mysql_select_db("gcs") ){
			mysql_close( $db_connection );
			die( 'ERROR when trying to select DB' );
		}

		return $db_connection;
	}


	/*
		GetUserProjects: Return an array of projects records of user with
		id 'userId'.
	*/
	function GetUserProjects ( $userId )
	{
		$db_connection = ConnectToDB();

		$res = mysql_query("SELECT * FROM projects WHERE ownerId = '".$userId."'");

		mysql_close( $db_connection );

		return $res;
	}


	/*
		CreateButton: Create a button with text 'text'. When pressed, button
		will call javascript function 'function'.
	*/
	function CreateButton( $text, $function )
	{
		echo '<input type="button" value="' . $text . '" onClick="' . $function . ';return false;" />';
	}


	/*
		CreateJSLink: Create a link ('a' element) with text 'text'. When pressed, 
		javascript function 'function' will be called.
	*/
	function CreateJSLink( $text, $function )
	{
		echo '<a href="#" onclick="' . $function . ';return false;">';
		echo $text;
		echo '</a>';
	}


	/*
		DeleteDirectory: Recursively delete directory whose path is path 'path'.
		Code taken from 
		http://www.barattalo.it/2010/02/02/recursive-remove-directory-rmdir-in-php/
	*/	
	function DeleteDirectory( $path )
	{
		$path = rtrim( $path, '/' ).'/';
		$handle = opendir( $path );

		if( !$handle ) return false;

		while( $file = readdir($handle) ){
		    if( ($file != '.') && ($file != '..') ) {
		        $fullpath = $path.$file;
				
		        if( is_dir( $fullpath ) ){ 
					DeleteDirectory( $fullpath ); 
				}else{ 
					unlink( $fullpath );
				}
		    }
		}
		closedir( $handle );
		rmdir( $path );
	}

?>
