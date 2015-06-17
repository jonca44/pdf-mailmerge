<?php
	
$time = microtime();
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$start = $time;

//require ("../../php/sessionStartAndCheck.php"); 

# include parseCSV class.
require_once('../parsecsv.lib.php');

# create new parseCSV object.
$csv = new parseCSV();


# Example conditions:
// $csv->conditions = 'title contains paperback OR title contains hardcover';
// $csv->conditions = 'author does not contain dan brown';
// $csv->conditions = 'rating < 4 OR author is John Twelve Hawks';
// $csv->conditions = 'rating > 4 AND author is Dan Brown';

	$data = stripslashes($_POST['data']);
	$data = json_decode($data);
	
	$titles = stripslashes($_POST['titles']);
	$titles = json_decode($titles);
	
	echo "<pre>";

	//$array = array('firstname' => 'John', 'lastname' => 'Doe', 'email' => 'john@doe.com');
	
	//$csv->parse('_books.csv', 0 , 1000); // At max 1000 lines.
	print_r($data);
	
	//$csv->output (true, 'movies.csv', $array);
	//$array);

	//$csv->output (true, 'data.csv', $data);
	
	//$outdata = $csv->unparse($data, $titles, true, null, ',');
	$csv->save('../out/test.csv', $data, false, array())
	//print_r($outdata);
	
		
?>

<?php 
	$time = microtime();
	$time = explode(' ', $time);
	$time = $time[1] + $time[0];
	$finish = $time;
	$total_time = round(($finish - $start), 4);
	echo 'Page generated in '.$total_time.' seconds.';
?>