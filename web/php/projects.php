<?php
	include_once "utilities.php";


	/*******************************************
	* Functions
	*******************************************/

	/*
		ListProjectsContents: Show the projects and their contents of user 
		with id 'userId'. The 'projects' array contains the project's records.
	*/
	function ListProjectsContents( $userId, $projects ){
		global $users_dir;
		echo '<div id="projectsAccordion">';

		// Iterate over the user's projects.
		while($project = mysql_fetch_array($projects)){
			// Show the project's name .
			$project['name'] = trim( $project['name'] );
		
			ListProjectContent( $project['name'], $project['progLanguageId'] );
		}
		echo '</div>';
	}


	/*
		ListProjectContent: Show contents of project 'projectName'. The
		'progLanguageId' parameter is needed by compilation function.
	*/
	function ListProjectContent( $projectName, $progLanguageId )
	{
		echo '<h3 id="' . $projectName . '@h3"><a href="#">';
		echo $projectName;
		echo '</a></h3><div id="' . $projectName . '@div">';

		// Create a "Create File" button for this project.
		$function = "OpenNewFileDialog('" . $projectName . "' )";
		CreateButton( 'Crear fichero', $function );

		// Create a "Compile" button for this project.
		$function = "OpenCompilationDialog('" . $projectName . "','" . $progLanguageId . "')";
		CreateButton( 'Compilar proyecto', $function );

		// Create a "Delete Project" button for this project.
		$function = "DeleteProject('" . $projectName . "' )";
		CreateButton( 'Borrar proyecto', $function );

		// Iterate over and list the files in the project's dir.
		$dir = opendir( $_SESSION['dir'] . '/' . $projectName );
		if( $dir  ){
			echo '<p>Lista de ficheros: </p><ul id="' . $projectName . '@filesList">';
			$entry = readdir( $dir );
			while( $entry !== false ){
				if( ($entry !== "bin" ) && ($entry !== ".." ) && ($entry !== "." ) ){
					// For each file ('li' element), show a link for oppening it and
					// another for deleting it.
					$fileName = $projectName . '/' . $entry;
					echo '<li id="' . $fileName . '" >';

					$function = "OpenFile('" . $fileName . "' )";
					CreateJSLink( $fileName . ' ', $function );

					$function = "DeleteFile('" . $fileName . "' )";
					CreateJSLink( '[Eliminar]', $function );

					echo "</li>";
				}
				$entry = readdir( $dir );
			}
			echo '</ul>';
		}							
		echo '</div>';
	}


	/*
		CreateNewProject: Tries to create a new project associated with the
		current user. 
		On success, this function creates the project and returns 1. Otherwise, 
		it returns 0.
	*/
	function CreateNewProject( $name, $progLanguageId )
	{
		global $users_dir;
		$db_connection = ConnectToDB();

		// Prevent SQL injections
		$name = mysql_real_escape_string($name);
		$name = trim($name);
		
		// Is there an project with the same name already associated with the user?
		$sql = mysql_query("SELECT * FROM projects WHERE ownerId = '".$_SESSION['id']."' AND name = '".	$name."' LIMIT 1") or die("Error en busqueda: " . mysql_error());

		// If is there not a project with the same name, try to create it.
		if( mysql_num_rows( $sql ) == 0 ){
			$ownerId = $_SESSION['id'];
			$sql = mysql_query("INSERT INTO projects (ownerId, name, progLanguageId) VALUES (' $ownerId','$name','$progLanguageId')") or die (mysql_error());
			mysql_close( $db_connection );
			
			// Create the project's dir.
			$res = mkdir(  $_SESSION['dir'] . '/' . $name, 0777, true );

			if( !$res ){
				$error = error_get_last();
    			die( $error['message'] );
			}

			return 1;
		}else{
			// A user with same name already exists.
			die( mysql_error() );
			mysql_close( $db_connection );
			return 0;
		}
	}

	/*
		DeleteProject: Delete project 'projectName'.
	*/
	function DeleteProject( $projectName )
	{
		global $users_dir;
		$db_connection = ConnectToDB();

		// Prevent SQL injections
		$projectName = mysql_real_escape_string( $projectName );
		$projectName = trim( $projectName );
		
		// Delete project from database.
		$sql = mysql_query("DELETE FROM projects WHERE ownerId={$_SESSION['id']} AND name='$projectName'") or die(mysql_error());

		// Get project's open files and return them in a array
		// "file,file,file", so javascript in client can remove their tabs.
		$sql = mysql_query("SELECT * FROM openFiles WHERE ownerId={$_SESSION['id']} AND name LIKE '$projectName%'") or die(mysql_error());
		while( $file = mysql_fetch_array( $sql ) ){
			echo "{$file['name']},";
		}

		// Delete project's open files
		$sql = mysql_query("DELETE FROM openFiles WHERE ownerId={$_SESSION['id']} AND name LIKE '$projectName%'") or die(mysql_error());

		//Remove project's dir.
		DeleteDirectory( $users_dir . '/' . $_SESSION['name'] . '/' . $projectName );

		mysql_close( $db_connection );
	}
	
	/*
		CompileProject: Try to compile the project 'projecName' located in
		'user_dir' with compiler 'compiler'. Name the resulting executable as
		'execName'.
	*/
	function CompileProject( $user_dir, $projectName, $compiler, $execName ){
		$files = "";

		// Iterate over project's files and add its names to array 'files'.
		$dir = opendir( $user_dir . '/' . $projectName );

		// For futher compilation: Shell doesn't like spaces.
		$projectName = str_replace(" ", "\ ", $projectName );

		// We only allow an exec, clear the user's './bin' directory.
		$dirPath = $user_dir . '/.bin';
		$dir = opendir( $dirPath );

		if( $dir ){
			$entry = readdir( $dir );
			while( $entry !== false ){
				if( ($entry !== ".." ) && ($entry !== "." ) ){
					unlink( $dirPath . '/' . $entry );
				}
				$entry = readdir( $dir );
			}
		}

		closedir( $dir );
		
		$compiler = str_replace( '@', '+', $compiler ); 
		
		// Find the main file, that which has "main" function.
		$mainFile = null;
		$dir = opendir( $user_dir . '/' . $projectName );

		if( $dir ){
			while( ($entry = readdir( $dir )) && !$mainFile ){
				if( ($entry !== ".." ) && ($entry !== "." ) ){
					echo '<!--';
					if( system( 'cat ' . $user_dir . $projectName . '/' . $entry . ' | grep main' ) ){
						$mainFile = $entry;
					}
					echo '-->';
				}
			}
		}

		if( !$mainFile ){
			die( 'ERROR: No hay ningun fichero con la funcion "main"' );
		}

		// Change current dir to source dir, so compiler can follow relative
		// paths.
		$lastDir = getcwd();
		chdir( $user_dir . $projectName );

		if( !chdir( $user_dir . $projectName ) ){ 
			die( 'Error cambiando a directorio [' . $user_dir . $projectName . ']' );
		}
		
		// Try to compile the project. The '2>&1' redirects errors to standard 
		// output, so we can take it in $output.
		$output = system( "sh -c '" . $compiler . " -o " . $user_dir . "/.bin/" . $execName . " " . $mainFile . " 2>&1'", $returnValue );

		// Change to previous dir.
		chdir( $lastDir );

		if( $returnValue == 0 ){
			// Compilation successful, create a button for downloading exec.
			$execFile = $user_dir . "/.bin/" . $execName;
			$execFile = str_replace( "\ ", " ", $execFile );

			echo '<form action="php/controller.php" method="POST" >';
			echo '<input type="hidden" name="fileName" value="' . $execFile . '" />';
			echo '<input type="hidden" name="fileAction" value="downloadFile" />';
			echo '<input type="submit" name="submit" value="Descargar ejecutable (' . $execName . ')" />';
			echo '</form>';
		}
		
		// Show the compilation's result.
		echo "Consola: $output";
	}

	
	/*
		Find the last (and unique, we only allow one) executable generated by 
		user. If found, return a form to download it.
	*/
	function GetLastExecutable( $user_dir )
	{
		// Search for the last exec generated by the user.
		$dirPath = $user_dir . '/.bin';
		$dir = opendir( $dirPath );

		if( $dir ){
			$entry = readdir( $dir );
			while( ($entry !== false) && ( ($entry == ".." ) || ($entry == "." ) ) ){
				$entry = readdir( $dir );
			}
		
			if( $entry ){
				// We found the exec, set its download form.
				$execFile = $user_dir . "/.bin/" . $entry;
				$execFile = str_replace( "\ ", " ", $execFile );

				echo '<form action="php/controller.php" method="POST" >';
				echo '<input type="hidden" name="fileName" value="' . $execFile . '" />';
				echo '<input type="hidden" name="fileAction" value="downloadFile" />';
				echo '<input type="submit" name="submit" value="Descargar ejecutable (' . $entry . ')" />';
				echo '</form>';
			}	
		}
	}

	/*
		GetCompilers: Return a 'select' element with all the compilation's 
		options for programing language with id 'progLanguageId'.
	*/
	function GetCompilers( $progLanguageId )
	{
		$db_connection = ConnectToDB();
		$res = mysql_query("SELECT * FROM compilers WHERE progLanguageId='$progLanguageId'") or die (mysql_error());
		mysql_close( $db_connection );

		echo '<select id="compiler" name="compiler">';
		while( $compiler = mysql_fetch_array( $res ) ){
			echo '<option value="' . $compiler['compiler'] . '">';
			echo $compiler['operativeSystem'] . ' - ' . $compiler['architecture'];
			echo '</option>';
		}
		echo '</select>';
	}


	/*
		DisplayProgLanguagesList: Return a 'select' element with all the
		availables programming languages.
	*/
	function DisplayProgLanguagesList()
	{
		$db_connection = ConnectToDB();
		
		$res = mysql_query("SELECT * FROM progLanguages") or die (mysql_error());

		echo '<select id="progLanguageId" name="progLanguageId">';
		while( $progLanguage = mysql_fetch_array( $res ) ){
			echo '<option value="' . $progLanguage['id'] . '">';
			echo $progLanguage['name'];
			echo '</option>';
		}
		echo '</select>';
		
		mysql_close( $db_connection );
	}

?>
