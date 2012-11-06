<?php>
    require_once 'lib/Geode_Server.php';
	$geode_server = new GEODE();
	$query =($_SERVER['QUERY_STRING']);
	if ( $query  ==''){ 
	  print 'Query String is Empty ';
	  exit;
	}
	$geode_server->ask($query);
?>

