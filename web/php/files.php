<?php
	/***
	 * Copyright 2012, 2014
	 * Garoe Dorta Perez
	 * Moises J. Bonilla Caraballo (Neodivert)
	 *
	 * This file is part of GCS.
	 *
	 * GCS is free software: you can redistribute it and/or modify
	 * it under the terms of the GNU General Public License as published by
	 * the Free Software Foundation, either version 3 of the License, or
	 * any later version.
	 *
	 * GCS is distributed in the hope that it will be useful,
	 * but WITHOUT ANY WARRANTY; without even the implied warranty of
	 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	 * GNU General Public License for more details.
	 *
	 * You should have received a copy of the GNU General Public License
	 * along with GCS.  If not, see <http://www.gnu.org/licenses/>.
	***/

	include_once "utilities.php";


	/*******************************************
	* Functions
	*******************************************/

	/*
		CreateFile: Create file 'fileName' in user dir 'user_dir'.
	*/
	function CreateFile ( $user_dir, $fileName )
	{
		// Create the file.
		$res = fopen( $_SESSION['dir'] . '/' . $fileName, "x" );

		if( !$res ){
			$error = error_get_last();
			die( $error['message'] );
		}

		fclose( $res );
	}


	/*
		DeleteFile: Delete file 'fileName' in user dir 'user_dir'.
	*/
	function DeleteFile( $user_dir, $fileName )
	{
		$res = unlink( $user_dir . '/' . $fileName );

		if( !$res ){
			$error = error_get_last();
    		die( $error['message'] );
		}
	}


	/*
		ListOpenFiles: Return a 'ul' element with all the files open by user,
		followed by a list of 'div' elements with that files' content. This
		format is needed in order to append it to a JQuery-UI tabs element. 
	*/
	function ListOpenFiles ( $userId )
	{
		global $users_dir;

		// Get open files records from database.
		$db_connection = ConnectToDB();
		$sql = mysql_query("SELECT * FROM openFiles WHERE ownerId = '".$userId."'" ) or die("Error en busqueda: " . mysql_error());
		mysql_close( $db_connection );

		// Create list of open files names.
		$i = 0;
		$openFiles = array();
		echo '<ul>';
		while($file = mysql_fetch_array($sql)){
			$file['name'] = trim( $file['name'] );

			// We use md5 resume function in order to get a ASCII unique and 
			// without 'strange characters' id for open files tabs.
			echo '<li><a href="#file-' . md5( $file['name'] ) . '">';
			echo $file['name'];
			echo '</a></li>';		
			$i++;

			// Save file name in a array. 
			array_push( $openFiles, $file );
		}
		echo '</ul>';

		// Create a list of divs with open files' content.
		$n = count( $openFiles );
		$i = 0;
		while( $i < $n ){
			echo '<div id="file-' . md5( $openFiles[$i]['name'] ) . '" >';
			GetFileByRecord( $openFiles[$i] );
			echo '</div>';
			$i++;
		}
	}


	/*
		SaveFile: Save content 'content' in file 'fileName' for user whose
		personal dir is 'user_dir'. Also return the file's content to client.
		This is not needed (the content was sended by client), but we use it
		as a confirmation.
	*/
	function SaveFile ( $user_dir, $fileName, $content ) 
	{
		file_put_contents( $user_dir . '/' . $fileName, $content );
		echo $content;
	}


	/*
		OpenFile: Open file 'fileName' for user with id 'ownerId'.
	*/
	function OpenFile( $ownerId, $fileName )
	{
		// Insert (fileName, ownerId) in 'openFiles' table.
		$db_connection = ConnectToDB();
		$sql = mysql_query("INSERT INTO openFiles (ownerId, name) VALUES ('$ownerId','$fileName')") or die (mysql_error());
		
		// Search the recently inserted record and return it (It is used by
		// function 'GetFileByRecord' for reading file.
		$sql = mysql_query( "SELECT id FROM openFiles WHERE ownerId=$ownerId AND name='$fileName'") or die (mysql_error());

		mysql_close( $db_connection );

		return mysql_fetch_array( $sql );
	}


	/*
		GetFileByRecord: Return the content of file present in 'fileRecord',
		with buttons for saving a closing the file.
	*/
	function GetFileByRecord( $fileRecord )
	{
		GetFile( $fileRecord['id'], $fileRecord['name'] );
	}


	/*
		GetFile: Return the content of file 'fileName' with id 'fileId',
		with buttons for saving a closing the file.
	*/
	function GetFile( $fileId, $fileName )
	{
		// Get complete file path.
		$filePath = $_SESSION['dir'] . '/' . $fileName;

		// Open file for reading/writting.
		$file = fopen( $filePath, "r+" );
		if( !$file ){
			// Error opening file.
			$error = error_get_last(); 
			echo '<p>error: ' . $error['message'] . '</p>'; 
		}else{
			// File open, read its content and close it.
			$str = null;
			if( filesize( $filePath ) ){ 
				$str = fread( $file, filesize( $filePath ) );
			}
			fclose( $file );
	
			// Show text area with file content.
			echo '<textarea id="' . $fileId . '@textArea" class="fileEditor" cols="65">';
			echo $str;
			echo '</textarea>';
			echo '<input type="hidden" id="file" name="fileName" value="';
			echo $fileName;
			echo '" />';
			echo '<p>';
		
			// Create buttons for saving a closing file.
			$function = "SaveFile('" . $fileName . "', '" . $fileId . "@textArea' )";
			CreateButton( 'Salvar Fichero', $function );

			$function = "CloseFile('" . $fileName . "')";
			CreateButton( 'Cerrar Fichero', $function );
		}
	}


	/*
		CloseFile: Close open file 'fileName' for user with id 'ownerId'.
	*/
	function CloseFile( $ownerId, $fileName )
	{
		// Delete it from open files sql table.
		$db_connection = ConnectToDB();
		$sql = mysql_query("DELETE FROM openFiles WHERE ownerId=$ownerId AND name='$fileName'") or die (mysql_error());
		mysql_close( $db_connection );
	}


	/*
		DownloadFile: Read file 'fileName' and send it to user.
	*/
	function DownloadFile( $fileName )
	{
		// If file exists, send it to client, otherwise show the error message.
		if( file_exists($fileName) ){
			chmod($fileName,0755);
			header('Content-Description: File Transfer');
			header('Content-Type: application/octet-stream');
			header('Content-Disposition: attachment; filename=' . basename($fileName));
			header('Content-Transfer-Encoding: binary');
			header('Expires: 0');
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Pragma: public');

			readfile( $fileName );
		}else{
			die( 'ERROR: El fichero [' . $fileName . '] no existe' );
		}
	}

?>
