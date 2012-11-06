<?php
/*
  *******************************
  * "GEODE", A GEM OF A SERVER! *
  **  (It RUNS SERVER-CIDE)    **
  ***  (DB/GEOMETRY SERVER)   ***
  ***  Created Aug 31, 2011   *** 
  *****    KEITH LEGG       *****
  *******************************	
  
*/

error_reporting(0); //debug july 10 , 2012

define("GEODE_VERSION_NUMBER", "00.01.02a");

//TODO - ACCESS LOGS ,ETC?

ob_start();
require_once 'lib/.htwtlFilter.php';
require_once 'lib/.htrcfg.php'     ;
require_once 'Geode_ReqFilter.php' ;  

ob_end_clean();
$VALID_STATE = 0;

if( ($_SERVER['QUERY_STRING']) ==''){ $VALID_STATE=0; }


//better to keep quiet and thought a fool 
function saynothing(){
      print '<?xml>';
      print '<None>No Results Found.</None>';	 

}
	

 class GEODE{

    var $filter_whitelist  = 0; //1 is ON 0 is off 

    function ar_print(){
		 $arg = func_get_args();
		   foreach ($arg[0] as $value) {
		 	print ($value);
		   }
	}

	//----------------------------------------------//
    //ASKFOR - TYPESEARCH - ARGS | ARGS - ARGS -.-. 
	//----------------------------------------------//
  	//  ?[ gfid_isct_fxy ;triq;triq ]&[ 10.1;22 ;;;... ]	
	//  http://192.168.0.2/test/dlib/runserv.php?gwkt_isct_ffid&idx=foo;1;2;3
	  
	function ask(){
	  $isUnderstood = 0; 
  	  $this->req    = new geode_request();  
	  $this->valid  = new parse_subquery();
      //$SAFEQ = 0;

	  /***/
	  $arg_list = func_get_args(); //args dont make sense in php ! debug 
 	  //if (!isset($arg_list[0]) )  die( saynothing() );			  
 	    if (isset ($arg_list[0]) ){
	       $qury_args  = $this->req->dirtychop ($arg_list[0],'&') ;
		   $triqry     =($this->valid->validate_askfor( $qury_args[0]) );
 		   //DEBUG NEED TO VALIDATE ARG DATA TYPES BEFORE QUERY IS RUN !!			
 		   $NARGS = $this->req->dirtychop ($qury_args[1],';');	   
		   
		   
	       //WHITELIST CHECK 
	       if (!whitelist()&&$this->filter_whitelist){
			     $VALID_STATE=0;
  		         print '<None>WHITELIST Fail!<None>';
				 exit;
		   }//whitelist check 
		   
		   $VALID_STATE=1;//DEBUG DONT LEAVE THIS !

		   //*****************************************/
			   
	       if (count($triqry)>2&& $VALID_STATE  =1){
			     $isUnderstood =1;

				 $tree = new query_tree();
				 $tree-> newnode('click');
				 $COMMAND = $tree->firstnode(); //hack , find wont work? //debug 
								 
				 //if ($triqry[0]!='gfo'){
				 $COMMAND->qset($triqry[0],$triqry[1],$triqry[2]);
                 $tree->execute('click',$NARGS);  //knock em down 


			   }
		   else{ 
			  saynothing();
 		    }//if not understood
        }//has arguments 
  	}//end ask
	/*********/	
	function geomquery($SAFE ){
      $tl = new TYPELESS();
      //$tl->add_dd_layer();
	  $tl->QG($SAFE);

	}
	/*********/	

    function creategeom(){
	  $this->typls = new TYPELESS();
 	}	
	/*********/
    function gemdump(){
	   $this->gformat = new geometry_out();
	   $ar= ($this->gformat->write_geom() );
    }
 }//end class 

?>


