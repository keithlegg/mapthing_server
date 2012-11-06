<?php
 function whitelist(){
	$valid_ips = array();
	/****/
	//add IP addresses to a list of known goods	
	//array_push($valid_ips,'192.168.etc etc');
	//array_push($valid_ips,'192.168.etc etc');
	
	/****/
	$remote = ($_SERVER['REMOTE_ADDR'] );
	for ($ai=0;$ai<count($valid_ips);$ai++)
	{
	  if ($remote== $valid_ips[$ai] ) {return 1;}
	 // if ('a'=='a') {}
	}
	return 0;
 }
?>
