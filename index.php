<?php
/*
Simple RESTful API to check whether or not it is a banking holiday.
Supports different timezones with the `timezone` param
Supports checking a different date with the `date` param
Supports returning in JSON with the `json` param (Note: the JSON return is more verbose)
*/

// Simple class to connect to the sqlite3 db.
class MyDB extends SQLite3
{
    public function __construct()
    {
        try {
            $this->open('holidays.db', SQLITE3_OPEN_READONLY);
        } catch (Exception $e) {
            die($e->getMessage()."\n\nUnable to find the db");
        }
    }
}

// Support a user specificing a timezone short code to check in
if(isset($_REQUEST['timezone'])) {
    $tz = timezone_name_from_abbr($_REQUEST['timezone']);
}

// If we found a timezone use it, else use UTC.
date_default_timezone_set($tz ? $tz : 'UTC');
$today = date('Y-m-d');

// Create our database connection, format our query, then execute.
$db = new MyDB('holidays.db');
$statement = $db->prepare("SELECT name FROM holidays WHERE date = :date;");
$date = isset($_REQUEST['date']) ? $_REQUEST['date'] : $today;
$statement->bindvalue(':date',$date);
$result = $statement->execute();

// Get our resulting row (not looping becuase there's only ever one holiday at a time)
$row = $result->fetchArray(SQLITE3_ASSOC);

// If we have a row and a value, it's a holiday! If not, no holiday today.
if($row && isset($row['name'])){
    $response = "Yes";
} else {
    $response = "No";
}

// If the user requested JSON output that in a pretty format, else just output the response
if(isset($_REQUEST['json'])) {
    header('Content-type: Application/JSON');
    $array = array($response => ["name" => $row['name'], "date" => $date ]);
    echo json_encode($array, JSON_PRETTY_PRINT);
} else {
    echo $response;
}
