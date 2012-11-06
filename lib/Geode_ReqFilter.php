<?php
  /*
   *********************************
       URL FILTER  
   *********************************
 */

  require_once 'Geode_Tabrow.php';

  class parse_subquery{
 
  	  /*********/	      /*********/
	  function str_is_clean($str){
	     $numpre  = strlen($str);
         $scrubz  = PREG_REPLACE("/[^0-9a-zA-Z]/i", '', $str); //could even omit the numbers ? debug ?
		 $numpost = strlen($scrubz);
         if ($numpre==$numpost){return 1; }
         return 0;		 
       }		  
	  /*********/	      /*********/
      function is_known_askfor($check){
        $known = array();
		$rating = 0;
	    array_push($known,'gwkt');
	    array_push($known,'gfid'); //bbox is in meta data 
	    array_push($known,'grcd');
	    array_push($known,'grpt'); //same as grcd but html 
		////debug?
	    array_push($known,'gjsn'); //same as grcd but json 		
		array_push($known,'gbfr');

        $num = count($known);
		for ($i=0;$i<$num;$i++){
          if ($check==$known[$i]){$rating=$rating+1;}
		  }
		  if ($rating!=0) {return 1;}
		return 0;
	  }
      /*********************/
      function is_known_searchmethd($check){
        $known = array();
		$rating = 0;
	    array_push($known,'blayr'  );	//debug - if narg[0] matches - get records from xml	
	    array_push($known,'isect'  );   //geometric intersection 
	    array_push($known,'match'  );	
	    array_push($known,'geom'   );   //just return geom - buffer only debug 
	    array_push($known,'tsrcrd' );	//tsearch - special case match
				 
        /****/		
        $num = count($known);
		for ($i=0;$i<$num;$i++){
          if ($check==$known[$i]){$rating=$rating+1;}
		  }
		  if ($rating!=0) {return 1;}
		return 0;
	  }
      /*********************/
      function is_known_searchtype($check){
        $known = array();
		$rating = 0;
 	    array_push($known,'fxy');
	    array_push($known,'ffid');
	    array_push($known,'frcd'); //for tserach
	    //array_push($known,'flyr');	 //for general database search	
	    array_push($known,'fwkt');	
	    array_push($known,'fobrec');	
		
	    //array_push($known,'fbox');	
        $num = count($known);
		for ($i=0;$i<$num;$i++){
          if ($check==$known[$i]){$rating=$rating+1;}
		  }
		  if ($rating!=0) {return 1;}
		return 0;
	  }
	  /*********/	      /*********/
      //returns a 0 or a valid query	  
      function validate_askfor($string,$char='_'){
	    $isvalid = 0;
		$safe_askfor   ='';
		$safe_srchtyp ='';
		$safe_fromdat ='';
        //	
        //$tmp =preg_split('@['.$char.']+@', $string ); 	
	    $tmp = explode($char, $string); 
	    if ( count($tmp)==3 ) {
		 for ($i=0;$i<3;$i++){
 		   $scrub = $tmp[$i];
             if ($this->str_is_clean($scrub) ){
				if ($i==0){ if ($this->is_known_askfor      ($scrub)){$safe_askfor  =$scrub;$isvalid=$isvalid+1;} }
				if ($i==1){ if ($this->is_known_searchmethd ($scrub)){$safe_srchtyp =$scrub;$isvalid=$isvalid+1;} }
				if ($i==2){ if ($this->is_known_searchtype  ($scrub)){$safe_fromdat =$scrub;$isvalid=$isvalid+1;} }
	  		  }
 	     }
		}//make sure ONLY three parts -> (NO SQL injection here!) 
		if ($isvalid==3) {
		  $out = array();
		  array_push($out,$safe_askfor) ; 
		  array_push($out,$safe_srchtyp) ; 
		  array_push($out,$safe_fromdat) ; 
          return $out;		  
		}//if all three pass
		return 0;
	  }//validate search 
	  /*********/	 	  /*********/	

  }

  class  geode_request{
	  /*********/	      /*********/	 
	  function safe_dirname($path) {
		 $dirname = dirname($path);
		 return $dirname == '/' ? '' : $dirname;
	   }	  
	  /*********/	      /*********/	  
	  function isValidFileName($file) {
         /* don't allow .. and allow any "word" character \ / */
         return preg_match('/^(((?:\.)(?!\.))|\w)+$/', $file);
       }
	 
     /*********/	      /*********/
	  //split by ';' and '&;
	  function dirtychop($url,$char){
	     //return split('[/.-]', $url );
	     return explode($char,$url);//split($char, $url );	
        //   $tmp =preg_split('@['.$char.']+@', $url ); 
		
      }
	  /*********/
	  function uldec($url){
	  	  return htmlspecialchars_decode($url);
	  }
	  /*********/		  
	  function ulenc($url){
	  	  return htmlspecialchars($url);
	  }
  
	  /*********/	  
	  //works with string or array 
	  function sanitize(){
		  $outar = Array();
  	      $arg_list = func_get_args();
		 foreach($arg_list[0] as $key => $value){
				 $data =  $value;
				 $data = PREG_REPLACE("/[^0-9a-zA-Z]/i", '', $data);
				 array_push($outar,$data);
		 }
		return  $outar;
	  }
	  /*********/
	  function str_is_clean($str){
	     $numpre  = strlen($str);
         $scrubz  = PREG_REPLACE("/[^0-9a-zA-Z]/i", '', $str);
		 $numpost = strlen($scrubz);
         if ($numpre==$numpost){return 1; }
         return 0;		 
       }
	  /*********/	  

   

}//request class 

/*************************************/

	function sanitizeOne($var, $type)
	{       
			switch ( $type ) {
                        case 'int': // integer
                        $var = (int) $var;
                        break;

                        case 'str': // trim string
                        $var = trim ( $var );
                        break;

                        case 'nohtml': // trim string, no HTML allowed
                        $var = htmlentities ( trim ( $var ), ENT_QUOTES );
                        break;

                        case 'plain': // trim string, no HTML allowed, plain text
                        $var =  htmlentities ( trim ( $var ) , ENT_NOQUOTES )  ;
                        break;

                        case 'upper_word': // trim string, upper case words
                        $var = ucwords ( strtolower ( trim ( $var ) ) );
                        break;

                        case 'ucfirst': // trim string, upper case first word
                        $var = ucfirst ( strtolower ( trim ( $var ) ) );
                        break;

                        case 'lower': // trim string, lower case words
                        $var = strtolower ( trim ( $var ) );
                        break;

                        case 'urle': // trim string, url encoded
                        $var = urlencode ( trim ( $var ) );
                        break;

                        case 'trim_urle': // trim string, url decoded
                        $var = urldecode ( trim ( $var ) );
                        break;
					}       
			return $var;
	}

	function sanitize( &$data, $whatToKeep )
	{
			$data = array_intersect_key( $data, $whatToKeep ); 
			foreach ($data as $key => $value)
			{
					$data[$key] = sanitizeOne( $data[$key] , $whatToKeep[$key] );
			}
	}

?>
