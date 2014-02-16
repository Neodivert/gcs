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

/*
	Set JQuery-UI objects:
		- Open Files' Tabs.
		- Project list' accordion.
		- New Project's dialog.
*/
function InitProjectsJQuery()
{  
   $(function(){
		// Set the "fileTabs" div as a "tabs" jquery-ui object. 
		$('#fileTabs').tabs();

		// Set the list of projects as a accordion.
   		$("#projectsAccordion").accordion({ header: "h3" });

	   	// Set the div "newProjectDialog" div as a dialog
		$('#newProjectDialog').dialog({
		   autoOpen: false,
		   width: 600,
		   buttons: {
			   "Crear Proyecto": function() {
					var projectName = $('#projectName').val();
					if( projectName.length <= 0 ){
						alert( 'ERROR: El nombre del proyecto no puede estar vacio' );
					}else if( projectName.indexOf(' ') != -1 ){
						alert( 'ERROR: El nombre del proyecto no puede contener espacios' );
					}else if( document.getElementById(projectName + '@h3' ) ) {
						alert( 'ERROR: Ya existe otro proyecto con el mismo nombre' );
					}else{
						CreateProject( projectName, $('#progLanguageId').val() );
						$(this).dialog( 'close' );
					}
				},
				"Cerrar": function() {
					$(this).dialog( 'close' );
				}
			}
	   });
		
		// When the user click on "newProjectButton", open the dialog.
		$('#newProjectButton').click(function(){
			// Clear projectName text input.
			$('#projectName').val('');
		   	$('#newProjectDialog').dialog('open');
            return false;
	      });
	   });

		// Create compilation dialog
		$(function () {
			$('#compilationDialog').dialog({
				modal: true,
				autoOpen: false,
				title: '???',
				buttons: [
					{text: "Compilar", click: function() {
						var projectName = document.getElementById('compilation_projectName').getAttribute('value');
						CompileProject( projectName, $('#compiler').val(), $('#execName').val() ); 
						RemoveElement('compiler');
						$(this).dialog('close');
					}},

					{text: "Cancel", click: function() {
						RemoveElement('compiler');
						$(this).dialog('close')}
					}
				]
			});
			return false;
		});
}


/*
	OpenCompilationDialog - Open compilation dialog for project 'projectName',
	whose programming language id is 'progLanguageId'.
*/
function OpenCompilationDialog( projectName, progLanguageId )
{
	// Get a list of available compilers for project's language.
	GetCompilers( progLanguageId, $( '#compilationDestination' ) );

	// $( '#compilation-projectName' ) is a hidden input of compilation dialog.
	// We use it to pass project name to dialog.
	$( '#compilation_projectName' ).val( projectName );

	// Compilation dialog is user by all projects, change its title
	// to show project's name.
	$( '#compilationDialog' ).dialog( 'option', 'title', projectName + ' - Compilar' );

	// Clear the exec name text input.
	$('#execName').val('');

	// Open dialog.
	$( '#compilationDialog' ).dialog( 'open' );
}


/*
	CompileProject - Send AJAX request for compiling project 'projectName'
	with compiler 'compiler'. When compiled, name the generated exec as 
	'execName'.
*/
function CompileProject( projectName, compiler, execName )
{
	var parameters = "projectName="+projectName;
	parameters = parameters +'&projectAction=compileProject';
	
	compiler = compiler.replace( '++', '@@' );

	parameters = parameters +'&compiler='+compiler;
	parameters = parameters +'&execName='+execName;

	var responseFunction = function(){
  		if (xmlhttp.readyState==4 && xmlhttp.status==200){
			// Show compilation's result in 'lastCompilationResult' element.
			document.getElementById("lastCompilationResult").innerHTML=xmlhttp.responseText;
		}
	}

	SendPOSTRequest( request_url, parameters, responseFunction );
}


/*
	CreateProject - Send AJAX request for creating a project 'projectName' 
	whose programming language id is 'progLanguage'.
*/
function CreateProject( projectName, progLanguageId )
{
	var parameters = "projectName="+projectName+"&projectAction=createProject&progLanguageId="+progLanguageId;

	var responseFunction = function(){
  		if (xmlhttp.readyState==4 && xmlhttp.status==200){
			var str = xmlhttp.responseText;

			$('#projectsAccordion').append(str).accordion('destroy').accordion();
		}
	}

	SendPOSTRequest( request_url, parameters, responseFunction );
}


/*
	DeleteProject - Send AJAX request for deleting a project 'projectName'.
*/
function DeleteProject( projectName )
{
	// Ask for confirmation.
	var r = confirm( '¿Esta seguro de querer eliminar el proyecto [' + projectName + ']?' );
	if( r==false ){
		return;
	}

	// Prepare POST request.
	var parameters = 'projectName='+projectName+'&projectAction=deleteProject';

	var responseFunction = function(){
  		if (xmlhttp.readyState==4 && xmlhttp.status==200){
			// Destroy projects accordion object.
			$('#projectsAccordion').accordion('destroy');

			// Remove elements related to deleted project.
			RemoveElement( projectName + '@h3' );
			RemoveElement( projectName + '@div' );

			// Rebuild accordion.
			$('#projectsAccordion').accordion();

			// Time to delete open files tabs. Server response has form
			// 'file,file,file'...
			files = xmlhttp.responseText;
			files = files.split(',');
			var i=0;
			while( i<(files.length-1) ){
				$('#fileTabs').tabs('remove','fileTabs-' + hex_md5( files[i] ) );
				i++;
			}
		}
	}

	SendPOSTRequest( request_url, parameters, responseFunction );
}


/*
	GetCompilers - Search in the db for compilers with programming language id
	'progLanguageId' and append result to element 'compilersList'.
*/
function GetCompilers( progLanguageId, compilersList )
{
	var parameters = "progLanguageId="+progLanguageId;

	var responseFunction = function(){
  		if (xmlhttp.readyState==4 && xmlhttp.status==200){
			compilersList.append( xmlhttp.responseText );
		}
	}

	SendPOSTRequest( request_url, parameters, responseFunction, false );
}

