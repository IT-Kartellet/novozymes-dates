<?php
define('CLI_SCRIPT', true);


require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');
require_once($CFG->libdir . '/clilib.php');


global $DB;

// Delete all courses
try{
	$DB->delete_records('grow_legacy');
	echo "Old records deleted \n";
} catch (Exception $e) {
	echo "No records deleted \n";
}

// Open CSV file
if ( ($file = fopen("GROW.csv", 'r')) !== FALSE ) {
	
	$counter = 0;

	while ($line = fgetcsv($file, 0, ',','"')) {
		try {

			// Create object and insert it to database;
			$grow = new grow_course($line[0], $line[1], strtotime($line[2]));
			$DB->insert_record("grow_legacy", $grow);

			echo "Inserted $grow->username  -- $grow->coursename -- $grow->timestart \n";
		} catch (Exception $e){
			var_dump($e);
			die();
		}
	}
}

echo "\n Done. \n";

class grow_course {

	public $username;
	public $coursename;
	public $timestart;

	public function __construct($username, $coursename, $timestart) {
		$this->username = $username;
		$this->coursename = $coursename;
		$this->timestart = $timestart;
	}
}