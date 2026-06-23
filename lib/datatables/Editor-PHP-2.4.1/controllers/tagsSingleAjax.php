<?php

// DataTables PHP library
include("../lib/DataTables.php");

// Alias Editor classes so they are easy to use
use
DataTables\Editor,
DataTables\Editor\Field,
DataTables\Editor\Options,
DataTables\Editor\Validate;

Editor::inst($db, 'team')
	->field(
		Field::inst('team.name'),
		Field::inst('team.continent')
			->get(false),
		Field::inst('continent.name'),
		Field::inst('team.country')
			->validator(Validate::required())
			->options(
				Options::inst()
					->table('country')
					->value('id')
					->label('name')
					->searchOnly(true)
					->limit(5)
			),
		Field::inst('country.name')
	)
	->leftJoin('continent', 'continent.id', '=', 'team.continent')
	->leftJoin('country', 'country.id', '=', 'team.country')
	->on('preCreate', function ($editor, $values) {
		$continent = getContinent($editor->db(), $values['team']['country']);
		$editor->field('team.continent')->setValue($continent);
	})
	->on('preEdit', function ($editor, $id, $values) {
		$continent = getContinent($editor->db(), $values['team']['country']);
		$editor->field('team.continent')->setValue($continent);
	})
	->process($_POST)
	->json();

function getContinent($db, $countryId)
{
	// The client-side only submits the country but this database scheme wants both
	// the country and the continent. Its redundant, but used for other examples!
	// So to retain database integrity, we need to look up the id for the continent
	// from the country id submitted
	$idRes = $db
		->select('country', 'continent', ['id' => $countryId])
		->fetch();

	return $idRes
		? $idRes['continent']
		: null;
}
