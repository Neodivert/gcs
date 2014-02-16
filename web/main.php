<!DOCTYPE html> 
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
?>

<html>
	<!--                                     HEAD                           -->
	<head>
		<title>GCS - Compilador Online</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<link rel="stylesheet" type="text/css" href="css/login.css" />
		<link rel="stylesheet" type="text/css" href="css/general.css" />
		<link rel="stylesheet" type="text/css" href="css/jquery-ui-1.8.20.custom.css" />
		<script type="text/javascript" src="js/jquery-1.7.2.min.js"> </script>
		<script type="text/javascript" src="js/jquery-ui-1.8.20.custom.min.js"> </script>
		<script type="text/javascript" src="js/utilities.js"> </script>
		<script type="text/javascript" src="js/users.js"> </script>
		<script type="text/javascript" src="js/projects.js"> </script>
		<script type="text/javascript" src="js/files.js"> </script>
		<script type="text/javascript" src="js/md5.js"> </script>
		<script type="text/javascript">
      		InitProjectsJQuery();
				InitFilesJQuery();
		</script>
	</head>
	

	<!--                                     BODY                           -->
	<body id="body">
		<div id="background">
			<!-- Left menu: user's info, projects and files -->
			<div id="menu">
				<h1 class="centerText">GCS</h1>
				<!-- Show the user's name and id -->
				<p>Bienvenido <?php echo $_SESSION['name'] . " (id: " . $_SESSION['id'] . ")"; ?> </p>
				
				<!-- Logout form -->
				<form id="logoutForm" action="controller.php" method="post">
					<input type="submit" name="userAction" value="logout" />
				</form>

				<!-- Delete user form -->
				<form id="deleteUserForm" action="controller.php" method="post">
					<input type="hidden" name="userAction" value="deleteUser" />
					<?php
					echo '<input type="button" name="userAction" value="Borrar usuario"';
					echo "onClick=\"DeleteUser('{$_SESSION['name']}')\" />";
					?>
				</form>

				<!-- Show user's projects -->
				<p>Proyectos</p>
				<p>----------------------</p>
				
				<?php
					$projects = GetUserProjects( $_SESSION['id'] );

					if( $projects ){
						ListProjectsContents( $_SESSION['id'], $projects );
					}else{
						die( '[' . mysql_error() . ']' );
					}
				?>
				<p>----------------------</p>

				<!-- New Project Button -->
				<input type="button" id="newProjectButton" value="Nuevo proyecto" />
			</div>

			<!-- Main Panel: open files tabs and compilation's info -->
			<div id="mainPanel">
				<h2 class="centerText">Ficheros abiertos</h2>
				<!-- Tabs with open files -->
				<div id="fileTabs">
					<?php
						ListOpenFiles ( $_SESSION['id'] );
					?>
				</div>

				<!-- Last compilation result -->
				<div id="lastCompilation">
					<h3 class="centerText">Ultima compilaci&oacute;n</h3>
					<div id="lastCompilationResult"><?php GetLastExecutable( $_SESSION['dir'] ) ?></div>
				</div>
			</div>
		</div>

		<!-- Dialog for project creation -->
		<div id="newProjectDialog" title="Crear Nuevo Proyecto">
			<p>Nombre del proyecto: <input type="text" id="projectName" name="projectName" /></p>
			<p>
				Lenguaje de programaci&oacute;n:
				<?php DisplayProgLanguagesList(); ?>
			</p>
		</div>

		<!-- Dialog for file creation -->
		<div id="newFileDialog">
			<p>
				Nombre del fichero: 
				<input type="text" id="newFileInput" />
			</p>
		</div>

		<!-- Dialog for compilation -->
		<div id="compilationDialog">
			<!-- The next div will be completed when opening dialog -->
			<div id="compilationDestination">Destino: </div>
			<p>
				Nombre del ejecutable: 
				<input type="hidden" id="compilation_projectName" value="generic" />
				<input type="text" id="execName" name="execName" />
			</p>
		</div>

	</body>
</html>

