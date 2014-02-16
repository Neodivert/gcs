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

	include_once "php/users.php";
	include_once "php/projects.php";
	include_once "php/files.php";

	// Dont allow users who didnt login.
	if( !isset( $_SESSION['name'] ) ){
		die( 'ERROR: Debe logearse/registrarse para entrar en esta secci&oacute;n' );
	}

	/*******************************************
	* Respond to POST Requests
	*******************************************/
	// User requests
	if( isset( $_POST['userAction'] ) ){
		switch( $_POST['userAction'] ){
			case 'deleteUser':
				// Delete user from db and delete its dir.
				DeleteUser( $_SESSION['name'] );
			case 'logout':
				// Unset session variables and go to '../index.php'.
				unset( $_SESSION['name'] );
				unset( $_SESSION['id'] );
				unset( $_SESSION['dir'] );
				header('Location: index.php');
			break;
			default:
				die( 'ERROR: Requested a invalid user action' );
			break;
		}
	// File requests
	}else if( isset( $_POST['fileName'] ) ){
		switch( $_POST['fileAction'] ){
			case 'downloadFile':
				DownloadFile( $_POST['fileName'] );
			break;
			case 'saveFile':
				SaveFile( $_SESSION['dir'], $_POST['fileName'], $_POST['fileContent'] ); 
			break;
			case 'createFile':
				CreateFile( $_SESSION['dir'], $_POST['fileName'] );
			break;
			case 'openFile':
				$fileId = OpenFile( $_SESSION['id'], $_POST['fileName'] );
				GetFile( $fileId['id'], $_POST['fileName'] );
			break;
			case 'closeFile':
				CloseFile( $_SESSION['id'], $_POST['fileName'] );
			break;
			case 'deleteFile':
				CloseFile( $_SESSION['id'], $_POST['fileName'] );
				DeleteFile( $_SESSION['dir'], $_POST['fileName'] );
			break;
			default:
				die( 'ERROR: Requested a invalid file action' );
			break;
		}
	}else if( isset( $_POST['projectName'] ) ){
		// Requests related to projects.
		switch( $_POST['projectAction'] ){
			case 'compileProject':
				CompileProject( $_SESSION['dir'], $_POST['projectName'], $_POST['compiler'], $_POST['execName'] );
			break;
			case 'createProject':
				CreateNewProject( $_POST['projectName'], $_POST['progLanguageId'] );
				ListProjectContent( $_POST['projectName'], $_POST['progLanguageId'] );
			break;
			case 'deleteProject':
				DeleteProject( $_POST['projectName'] );
			break;
			default:
				die( 'ERROR: Requested a invalid project action' );
			break;
		}
	}else if( isset( $_POST['progLanguageId'] ) ){
		// The client only sends a 'progLanguageId' when he/she wants a
		// list of available compilers.
		GetCompilers( $_POST['progLanguageId'] );
	}
?>
