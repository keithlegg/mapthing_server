<?php
	
 function brug(){
	
  $myFile = "securityfile.txt"; //clean up these paths?

  $CFG_USER      = '';
  $CFG_PW        = '';
  $CFG_DBASE     = '';
  $CFG_SRID      = '';
  $CFG_DBHOST    = '';
  $CFG_GIDINDX   = '';
  $CFG_PORT      = '';
  $CFG_DB_FIELD  = '';
  $CFG_SCHEMA    = '';  
  $CFG_FPATH     = ''; 	

  $handle = @fopen($myFile, "r"); 
	
	if ($handle) {
	  while (!feof($handle)) // Loop til end of file. 
	 {
		$buffer = fgets($handle, 4096); // Read a line.
		if ($buffer <> "srid")        // Check for string.
		{
		   $subject = $buffer;
		   $pattern = '/dbtitle/';
		   preg_match($pattern, $subject, $matches, PREG_OFFSET_CAPTURE); 
		   $pieces = explode(":", $buffer);
		   if ($matches[0][0] =='dbtitle') { 
			 $CFG_DBASE= $pieces[1]; 
		   }
			 ////
		   $pattern = '/user/';
		   preg_match($pattern, $subject, $matches, PREG_OFFSET_CAPTURE); 
		   $pieces = explode(":", $buffer); 
		   if ($matches[0][0] =='user') { 
			 $CFG_USER= $pieces[1]; 
		   }
		   ////
		   $pattern = '/pwd/';
		   preg_match($pattern, $subject, $matches, PREG_OFFSET_CAPTURE); 
		   $pieces = explode(":", $buffer); 
		   if ($matches[0][0] =='pwd') { 
			 $CFG_PW = $pieces[1]; 
		   }
		   ////
		   $pattern = '/schema/';
		   preg_match($pattern, $subject, $matches, PREG_OFFSET_CAPTURE); 
		   $pieces = explode(":", $buffer); 
		   if ($matches[0][0] =='schema') { 
			 $CFG_SCHEMA  = $pieces[1]; 
		   }		   
		   ////
		   $pattern = '/srid/';
		   preg_match($pattern, $subject, $matches, PREG_OFFSET_CAPTURE); 
		   $pieces = explode(":", $buffer); 
		   if ($matches[0][0] =='srid') { 
		   $CFG_SRID= $pieces[1]; 
		   }
		   ////
		   $pattern = '/dbhost/';
		   preg_match($pattern, $subject, $matches, PREG_OFFSET_CAPTURE); 
		   $pieces = explode(":", $buffer); 
		   if ($matches[0][0] =='dbhost') { 
		   $CFG_DBHOST= $pieces[1]; 
		   }
		   ////
		   $pattern = '/port/';
		   preg_match($pattern, $subject, $matches, PREG_OFFSET_CAPTURE); 
		   $pieces = explode(":", $buffer); 
		   if ($matches[0][0] =='port') { 
		   $CFG_PORT= $pieces[1]; 
		   }
		   ////
		   $pattern = '/gidindex/';
		   preg_match($pattern, $subject, $matches, PREG_OFFSET_CAPTURE); 
		   $pieces = explode(":", $buffer); 
		   if ($matches[0][0] =='gidindex') { 
		   $CFG_GIDINDX= $pieces[1]; 
		   }
		   ////
		   $pattern = '/fpath/';
		   preg_match($pattern, $subject, $matches, PREG_OFFSET_CAPTURE); 
		   $pieces = explode(":", $buffer); 
		   if ($matches[0][0] =='fpath') { 
		   $CFG_FPATH= $pieces[1]; 
		   }
		   ///
		   $pattern = '/dbsearchfield/';
		   preg_match($pattern, $subject, $matches, PREG_OFFSET_CAPTURE); 
		   $pieces = explode(":", $buffer); 
		   if ($matches[0][0] =='dbsearchfield') { 
		   $CFG_DB_FIELD= $pieces[1]; 
		   }
		   //

		} 
	  }

	  fclose($handle); 
	  
	}
      /*******/	  
	  $out = array();
	  array_push( $out, $CFG_DBASE    );
	  array_push( $out, $CFG_USER     );
	  array_push( $out, $CFG_PW       );
	  array_push( $out, $CFG_SRID     );
	  array_push( $out, $CFG_DBHOST   );
	  array_push( $out, $CFG_PORT     );
	  array_push( $out, $CFG_GIDINDX  );
	  array_push( $out, $CFG_FPATH    );	
 	  array_push( $out, $CFG_DB_FIELD ); 
     return $out;
}
  
?>



