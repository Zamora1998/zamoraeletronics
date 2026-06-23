<?php

// DataTables PHP library
include('../lib/DataTables.php');

// Alias Editor classes so they are easy to use
use
DataTables\Editor,
DataTables\Editor\Field,
DataTables\Editor\Options;


Editor::inst($db, 'team')
	->field(
		Field::inst('team.name'),
		Field::inst('country.name')
			->options(
				Options::inst()
					->table('country')
					->value('name')
					->label('name')
					->searchOnly(true)
					->limit(10)
			)
	)
	->leftJoin('country', 'country.id', '=', 'team.country')
	->process($_POST)
	->json();
