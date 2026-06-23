<?php

// DataTables PHP library
include( "../lib/DataTables.php" );

// Alias Editor classes so they are easy to use
use
	DataTables\Editor,
	DataTables\Editor\Field,
	DataTables\Editor\Format,
	DataTables\Editor\Mjoin,
	DataTables\Editor\Options,
	DataTables\Editor\Upload,
	DataTables\Editor\Validate,
	DataTables\Editor\ValidateOptions;


/*
 * Example PHP implementation used for the join.html example
 */
Editor::inst( $db, 'permission' )
	->field( 
		Field::inst( 'id' )->set(false),
		Field::inst( 'name' )
	)
	->process($_POST)
	->json();
