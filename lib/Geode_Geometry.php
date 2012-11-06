<?php
/*
  *********************************
  **   Geometry<-> POSTGIS!      **
  **   Created Aug 31 , 2011     **
  **     Modified 7-10-12        **
  **                             **
  *********************************
	
	
  TODO |-  add in a schema property
       |-- scrub the NARGS for security   
	   
  --------------------------------------
  Changelog July 10 ,2012 Debug 
  Geomfromtext -> ST_GeomFromText
  ST_Box2d     -> box2d
	   
*/

require_once 'Geode_Parser.php'; 
require_once 'Geode_Tabrow.php'; 



//DEBUG - DECLARE ENCODING???
/*<?xml version="1.0" encoding="UTF-8"?>  */


/******************************/

class map_api{
  
   var $DEBUG_INPUTS      = 0;   // show a breakdown of the input queries for debugging
   var $RUN_REPONSE       = 1;   // MAIN OUTPUT XML OR HTML 
   var $RUN_META_QUERY    = 1;   // return meta data from server 
   var $RUN_BBOX_QUERY    = 1;   // return Bounding Box meta data 
   var $XML_HTML          = 1;   // ZERO=HTML , ONE=XML  //  THREE   // TWOTWOTWO=CSV
   /***************************/

   var $bufferdist       = 10.1; //geometry buffer distance
   
   /***************************/
   function drilldown($BLK_QUERY,$BLK_NARGS){
       //$this->DATA_GRAPH   = new query_tree();
	   $this->OUTPUT_GRAPH = new query_tree(); //this is OUTPUT	  
       $this->OUTPUT_GRAPH->newnode('geod_response');

	   /**************/		  
	   $numlayers = count($this->DDLYRS);
	   for ($li=0;$li<$numlayers;$li++){
		 $this->run_block($BLK_QUERY,$BLK_NARGS,$this->DDLYRS[$li],$this->FTSLAYERS[$li]); 
	   }
	   /**************/
       //$this->OUTPUT_GRAPH->show(); //raw data output 
	   if ($this->XML_HTML==0){     $this->OUTPUT_GRAPH->render('html');}
	   /**************/
	   //print '<xml>';//javascript DOMparse bitches without this
	   if ($this->XML_HTML==1){     $this->OUTPUT_GRAPH->render('xml') ;}
	   /**************/
	   //JSON  
	   if ($this->XML_HTML==3){     $this->OUTPUT_GRAPH->render('json') ;} //DEBUG  
	   
	   //KML 
	   //if ($this->XML_HTML==123){   $this->OUTPUT_GRAPH->render('kml') ;} //DEBUG  
	   
	   /**************/	   
	   //CSV 
	   if ($this->XML_HTML==222){   $this->OUTPUT_GRAPH->render('csv') ;} //DEBUG Dec 7 //CSV 

 	   /**************/	   
	   //RSS 

	   
   }
  
   /*************/	
   
   function set_environment($DDLAYRS){
	 $this->DDLYRS =$DDLAYRS; 
	 //UNITS
	 //PROJECTION

   }
    /*************/	
   //debug june 1 2012	
   //pass in each layer's FTS feild 
   function set_env_drilldown($FTSLAYERS){
	 $this->FTSLAYERS =$FTSLAYERS; 
   }

   
   /*************/   
   function run_block($BLK_QUERY,$BLK_NARGS,$ONLAYER,$FTS_FIELD){
  	 /********************************/
	   $key        = brug();
	   

	   //DO YOUR OWN SECURITY! 
       //you can pull the credentials from another script	   
    /*
	   $HOST       = $key[4];
	   $PORT       = $key[5];
	   $DBNAME     = $key[0];
	   $USER       = $key[1];
	   $PASSWORD   = $key[2];
	   $GIDX       = $key[6];
	   $tmpfield   = $key[8];
	   $QFIELD  =  substr($tmpfield,0,-1);//clip off last char 
     */

	   //or put them here 
	   
		$DBNAME    = 'databsename';
		$USER      = 'databaseuser';
		$PASSWORD  = 'password';
		$HOST      = '127.0.0.1';
		$PORT      = 5432;
		$GIDX      = 'gid';
		$SRID      = 4326;
		$QFIELD    = 'primaryqueryfield';
		$GEOMTAB   = 'geom';
		$INDEXTAB  = 'gid';
	    $scrubatr  = 'filterattribute';
		
  	   /********************************/	   
	   $RQ1   = '';$RQ2   = '';$RQ3   = '';$RQ4 = '';$RQ5 = '';	
	   $INTQ1 = '';$INTQ2 = '';$INTQ3 = '';
       $BB1   = '';$BB2   = '';
	   /********/ 

	   $META_NL_PNT      = 0;
	   $META_NL_LIN      = 0;
	   $META_NL_PLY      = 0;
	   $META_NREC        = 0;
       $NUMRTRNS_LAYERS  = 0;
	   $RECORDS_FOUND    = array();
	   /******************************/

	   $numnargs = count($BLK_NARGS);
	   if ($numnargs>1){
		   $extended_query = '';
		   for ($a=0;$a<$numnargs;$a++)
		   {
		     //if buffer do different
			 if ($BLK_QUERY[0]=='gbfr'){
				 if ($a>0&&$a!=($numnargs-1)){
					  $extended_query.=(' OR '.$GIDX.' = '.$BLK_NARGS[$a]);
				 }
				 if ($a==($numnargs-1)){
					  $bufferdist =$BLK_NARGS[$a];
				 }				 
				 
				 
			 }
			 //default case 
			 if ($BLK_QUERY[0]!='gbfr'){			 
				 if ($a>0){
					  $extended_query.=(' OR '.$GIDX.' = '.$BLK_NARGS[$a]);
				 }
			 }
		   }

       }//debug 
	   
	   /******************************/
	   
	   if ($BLK_QUERY[0] =='gfid' ){
	     $RQ1  ='SELECT '.$GIDX.' FROM '       .$ONLAYER.' '  ;
	     //$INTQ1='SELECT count('.$GIDX.') FROM '.$ONLAYER.' '  ;//do client side instead
		 $INTQ1='SELECT '.$GIDX.' FROM '.$ONLAYER.' '  ;
		 /**/
		 //$BB1  = 
 	   }
	   /******************************/	   
	   if ($BLK_QUERY[0] =='gwkt' ){
	     $RQ1  ='SELECT  ST_asText('.$GEOMTAB.') FROM '.$ONLAYER.' '  ;
		 $INTQ1='SELECT '.$GIDX.' FROM '.$ONLAYER.' '  ;
		 /**/
		 // SINGLE NARG | ST_Box2D('.$GEOMTAB.')
		 if ($numnargs==1){
		  $BB1='SELECT box2d('.$GEOMTAB.') FROM '.$ONLAYER.' '  ;
		 } 
		 // IS MULTI NARG, NO? | ST_Box2D(ST_Union('.$GEOMTAB.')) 
		 if ($numnargs>1){
		  $BB1='SELECT box2d(ST_Union('.$GEOMTAB.')) FROM '.$ONLAYER.' '  ;
		 } 
		 
 	   }

	   
	   
	   /******************************/
		
	   //IF RECORD THEN LOAD EACH LAYERS ATTR LIST FROM XML 
	   if ($BLK_QUERY[0] =='grcd' ){
		
		 //$this->XML_HTML =0; //0=html , 1= XML | DEBUG 
	   
	     //Normal mode 
	     if (($BLK_QUERY[1] !='blayr' )){
	       //check if .$ONLAYER.' exists 
	 	   $RQ1  ='SELECT '.$GIDX.' FROM '       .$ONLAYER.' '  ;
           //turn off other queries if tsearch 
	       $this->RUN_META_QUERY =0 ;//debug //BBOX WILL GO OFF TOO  ;
		   
		 }
	     if (($BLK_QUERY[1] =='blayr' )){
	 	   $RQ1  ='SELECT '  ;        //PASS THE QUERY ON TO THE NEXT STAGE 
	       $this->RUN_META_QUERY =0 ; //debug //turn off other queries 
		 }
		 
 	   }
	   /************************************/
	   /************************************/

	   //Should be same as above , but html 
	   if ($BLK_QUERY[0] =='grpt' ){
		
		 $this->XML_HTML =0; //0=html , 1= XML | DEBUG 
	   
	     //Normal mode 
	     if (($BLK_QUERY[1] !='blayr' )){
	       //check if .$ONLAYER.' exists 
	 	   $RQ1  ='SELECT '.$GIDX.' FROM '       .$ONLAYER.' '  ;
           //turn off other queries if tssearch 
	       $this->RUN_META_QUERY =0 ;//debug //BBOX WILL GO OFF TOO  ;
		   

		 }
	     if (($BLK_QUERY[1] =='blayr' )){
	 	   $RQ1  ='SELECT '  ;        //PASS THE QUERY ON TO THE NEXT STAGE 
	       $this->RUN_META_QUERY =0 ; //debug //turn off other queries 
		 }
		 
 	   }	   

	   //Should be same as above , but json 
	   if ($BLK_QUERY[0] =='gjsn' ){
		
		 $this->XML_HTML =3; //0=html , 1= XML | DEBUG 
	   
	     //Normal mode 
	     if (($BLK_QUERY[1] !='blayr' )){
	       //check if .$ONLAYER.' exists 
	 	   $RQ1  ='SELECT '.$GIDX.' FROM '       .$ONLAYER.' '  ;
           //turn off other queries if tssearch 
	       $this->RUN_META_QUERY =0 ;//debug //BBOX WILL GO OFF TOO  ;
		   
		 }
	     if (($BLK_QUERY[1] =='blayr' )){
	 	   $RQ1  ='SELECT '  ;        //PASS THE QUERY ON TO THE NEXT STAGE 
	       $this->RUN_META_QUERY =0 ; //debug //turn off other queries 
		 }
		 
 	   }		   
	   
	  ///////////////////////////
	   if ($BLK_QUERY[0] =='gbfr' ){	
   
		   $RQ1 = 'SELECT  ST_asText( ST_Buffer(ST_union('.$GEOMTAB.') ,'.$bufferdist.' ) )  FROM '.$ONLAYER.' ';
			   
   	   }

	   /******************************/
	   if ($BLK_QUERY[1] =='isect' ){
	     $RQ2= 'WHERE st_dwithin( ';
         $INTQ2='WHERE st_dwithin( ';
		 
	   }
	   //if match , arg4 is table to match ,arg5 is num args , arg6 up is N args
	   if ($BLK_QUERY[1] =='match' ){
	      $RQ2  ='WHERE '.$INDEXTAB.' = ' ; 
		  $INTQ2='WHERE '.$INDEXTAB.' = ' ;
	   }
	   ///ts search / record search 
	   if ($BLK_QUERY[1] =='tsrcrd' ){
	     if ($BLK_QUERY[0]=='gfrcd' ){
		 
		    $RQ2  ='XWHERE to_tsvector("'.$QFIELD.'")' ; 
		    $INTQ2='XNULL' ; 	  
		  }
	     if ($BLK_QUERY[0]!='gfrcd' ){
	        $RQ2  ='WHERE to_tsvector("'.$QFIELD.'")' ; 
		    $INTQ2='NULL' ; 		  
		  }		  

	   }

	   /******************************/	   
	   if ($BLK_QUERY[2] =='ffid' ){
	    //DEBUG NEED TO WORK FOR MULTIPLE 
	     if ($numnargs==1){
	       $RQ3  = $BLK_NARGS[0];
		   $INTQ3= $BLK_NARGS[0];
		 } 
		 // IS MULTI NARG, NO? | ST_Box2D(ST_Union('.$GEOMTAB.')) 
		 if ($numnargs>1){
	       $RQ3  = ($BLK_NARGS[0].$extended_query);
		   $INTQ3= ($BLK_NARGS[0].$extended_query);
		 } 
	   }
	   ///Oct 31 , 2011 debug 
	   if ($BLK_QUERY[2] =='frcd' ){
		   $query_str          = urldecode($BLK_NARGS[0]);
           //IF MULTI ARGUMENT SEARCH 		   
		   if ($BLK_NARGS[1]){
		     $EXTRA_TAG = urldecode($BLK_NARGS[1]);
 	         $optional_query_str = ('AND to_tsvector("'.$QFIELD.'") @@ to_tsquery(\''.$EXTRA_TAG.'\')' );
		   }
	   
		   if ($query_str){
             if ($BLK_QUERY[1]!='blayr'){
		      $RQ3  = (' \'null\''); 
		      $INTQ3= (';'); 
			 }
			 
		   }
		   if (!$query_str){
		     $RQ3  = (' \'null\''); 
		     $INTQ3= (';'); 
		   }
           /////////////////
	       if ($BLK_QUERY[1]=='tsrcrd'){
	       	 //single argument   	
 			 if (!$BLK_NARGS[1]){
			   if ($query_str!=$scrubatr){
		         $RQ3  = (' @@ to_tsquery(\''.$query_str.'\')'); 
			   }
			 }
	       	 //multi argument   	
 			 if ($BLK_NARGS[1]){
			   if ($query_str!=$scrubatr&&$optional_query_str!=$scrubatr){
		          $RQ3  = (' @@ to_tsquery(\''.$query_str.'\') '.$optional_query_str );
               }			   
			 }
 	         $INTQ3= (';'); 
		   }
   
		   //record query from layer name 
	       if ($BLK_QUERY[1]=='blayr'){
			 //if layer (first arg) matches - use rest of nargs as fields to search 
			 if ($BLK_NARGS[0]==$ONLAYER){
			   $num        = count($BLK_NARGS);
			   $narguments = '';
			   
			   for ($i=0;$i<$num;$i++){

			     //0-layername , 1- fid , 2+ (nargs) 
			     if ($i>1){
				     if ($i==2){$narguments = ($narguments.'"'    .$BLK_NARGS[$i].'"');array_push($RECORDS_FOUND,$BLK_NARGS[$i]); }//DEBUG-  ONLY CSV IF MULTI- DEBUG 
				     if ($i>2) {$narguments = ($narguments.','.'"'.$BLK_NARGS[$i].'"');array_push($RECORDS_FOUND,$BLK_NARGS[$i]);}//DEBUG-  ONLY CSV IF MULTI- DEBUG 					 
				 }
				 //DEBUG - if ($check==$known[$i]){$rating=$rating+1;}
			   }
			   //
  		       $RQ3   = ($RQ3.$narguments.' FROM '.$ONLAYER.' WHERE '.$INDEXTAB.' = '.$BLK_NARGS[1]); 
		       $INTQ3 = (';'); 		 
			 }
           }//BLAYER 
       }
	   ///
	   if ($BLK_QUERY[2] =='fxy' ){
	     $TMPWKT = 'POINT( '.$BLK_NARGS[0].' '.$BLK_NARGS[1].')';
	     $RQ3   = ('ST_GeomFromText( \''.$TMPWKT.'\','.$SRID.'),'.$ONLAYER.'.'.$GEOMTAB.',0)'); 
		 $INTQ3 = ('ST_GeomFromText( \''.$TMPWKT.'\','.$SRID.'),'.$ONLAYER.'.'.$GEOMTAB.',0)'); 
	   }
	   //
	   if ($BLK_QUERY[2] =='fwkt' ){
	     $RQ3   = ('ST_GeomFromText( \''.urldecode($BLK_NARGS[0]).'\','.$SRID.'),'.$ONLAYER.'.'.$GEOMTAB.',0)'); 
		 $INTQ3 = ('ST_GeomFromText( \''.urldecode($BLK_NARGS[0]).'\','.$SRID.'),'.$ONLAYER.'.'.$GEOMTAB.',0)'); 
	   }	   
      /***********************************/
      $ASSEMBLED = ($RQ1  .$RQ2.$RQ3.$RQ4.$RQ5);
	  $INTERNAL  = ($INTQ1.$RQ2.$RQ3.$RQ4.$RQ5); //count up the meta data 
	  $BBOXQURY  = ($BB1  .$RQ2.$RQ3.$RQ4.$RQ5);

      /***********************************/
	 if ($this->DEBUG_INPUTS){

        //this is *GREAT*, it lets you find a node from a previous cycle of the drill down  
        $response_top= $this->OUTPUT_GRAPH->find_name('geod_response');
		$response_top->add_attr("tabcolor","ffff00");
		$response_top->add_attr("invisible","1"); //hack - bypass drawing to screen
		
        $laynod      = $this->OUTPUT_GRAPH->newnode( ('layer_'.$ONLAYER) );	
        $laynod->add_attr("tabcolor","ff0099");
  
        $n1= $this->OUTPUT_GRAPH->newnode('input');
		$n1->add_attr("tabcolor","00ff00"); 
	    $n1->add_text_line($BLK_QUERY[0]);
	    $n1->add_text_line($BLK_QUERY[1]);
	    $n1->add_text_line($BLK_QUERY[2]);
        //
        $n2= $this->OUTPUT_GRAPH->newnode('breakdown');
		$n2->add_attr("tabcolor","6cc417"); 		
	    $n2->add_text_line($RQ1);
	    $n2->add_text_line($RQ2);
	    $n2->add_text_line($RQ3);
        //
        if ($this->RUN_META_QUERY){		
			$n4= $this->OUTPUT_GRAPH->newnode('metadata');
			$n4->add_attr("tabcolor","33cc99"); 		
			$n4->add_text_line($INTQ1);
			$n4->add_text_line($INTQ2);
			$n4->add_text_line($INTQ3);
		}
		//
        if ($this->RUN_BBOX_QUERY){		
			$n4= $this->OUTPUT_GRAPH->newnode('bboxdata');
			$n4->add_attr("tabcolor","66cccc"); 		
			$n4->add_text_line($BB1);
			$n4->add_text_line($BB2);
		}		
	
		//
        $n3= $this->OUTPUT_GRAPH->newnode('assembled');
		$n3->add_attr("tabcolor","00ffff"); 		
	    $n3->add_text_line($ASSEMBLED);
        if ($this->RUN_META_QUERY){			
			$n5= $this->OUTPUT_GRAPH->newnode('meta_assembled');
			$n5->add_attr("tabcolor","00ffff"); 		
			$n5->add_text_line($INTERNAL);
	    }
		
        if ($this->RUN_BBOX_QUERY){			
			$n5= $this->OUTPUT_GRAPH->newnode('bbox_assembled');
			$n5->add_attr("tabcolor","00ffff"); 		
			$n5->add_text_line($BBOXQURY);
	    }
		
		$this->OUTPUT_GRAPH->parent_name($n1->name ,$laynod);
		$this->OUTPUT_GRAPH->parent_name($n2->name ,$laynod);
		$this->OUTPUT_GRAPH->parent_name($n3->name ,$laynod);
	
        $this->OUTPUT_GRAPH->parent_name($laynod   ,$responsetop);
	 }//DEBUG MODE 
	 /*****************************/

	//build the top node 
    $kdocument = $this->OUTPUT_GRAPH->find_name('geod_response');
	$kdocument->add_attr("tabcolor","33ccff"); //debug not too pretty 

	$kdocument->set_type("response");  //added type oct 5 2012  
	
	
	
    if ($this->RUN_REPONSE){
	       $dbconn = pg_connect("host=$HOST dbname=$DBNAME user=$USER password=$PASSWORD")
	          or die( pg_last_error() );
 		   $search_db = @pg_query($dbconn, $ASSEMBLED) ;//or die();
		   $numrows   = @pg_num_rows($search_db);
		   
		   }//META DATA TOGGLE
				  
		   if ($numrows < 1) {
             //print 'none';
		   }else{
 			     //MAKE A LAYER NODE 
				 $test = $this->OUTPUT_GRAPH->find_name(('layer_'.$ONLAYER));
				 if (!$test){
				    $laynod =$this->OUTPUT_GRAPH->newnode(('layer_'.$ONLAYER));
					$laynod->set_type('layer');//debug oct 5 2012
				    $this->OUTPUT_GRAPH->parent_obj($laynod,$kdocument);
		         }//layer node 
			   //debug Oct 31 - Probably redundant 
			   if ($BLK_QUERY[1]!='blayr' ){
				   for ($x=0;$x<$numrows;$x++){
						 
						 //$val = pg_fetch_array($search_db);
						 $val = pg_fetch_result($search_db, $x, 0);
						 
						 $geodata =$this->OUTPUT_GRAPH->newnode(('wkt_'.$ONLAYER.'_'.$x));//('geodata_'.$x));
						 $geodata->add_text_line($val); //debug 
						 $this->OUTPUT_GRAPH->parent_obj($geodata,$laynod);
				   }//main loop 
               }
			   if ($BLK_QUERY[1]=='blayr' ){
		   		   $val = pg_fetch_array($search_db);
                   $counttest = count( $val);
				   
				   // DEBUG - WHY IS THIS /2 ???
				   for ($yyx=0;$yyx<($counttest/2);$yyx++){
						 $rcddata =$this->OUTPUT_GRAPH->newnode(($RECORDS_FOUND[$yyx] ));//rcd_ //. ('geodata_'.$x));
						 $rcddata->add_attr("tabcolor","b0c4de"); //debug not too pretty 
						 $rcddata->set_type('db_record');//debug Oct 5 2012 
						 $rcddata->add_text_line($val[$yyx]); //debug 
						 $this->OUTPUT_GRAPH->parent_obj($rcddata,$laynod);
				   }//main loop 
               }
			   
			   /******/	 
               if ($this->RUN_META_QUERY){
			      //DEBUG ADD "@" 
				  $meta_db     = @pg_query($dbconn, $INTERNAL) ;//or die();
				  $meta_rows   = @pg_num_rows($meta_db);
				  $countfids   = @pg_num_rows($meta_db);
				  
				  if ( $countfids!=0) {
			             for ($y=0;$y<$meta_rows;$y++){			  
				           $val = pg_fetch_result($meta_db, $y, 0);
						   $metadata=$this->OUTPUT_GRAPH->newnode(('meta_'.$ONLAYER.'_'.$y));
						   $metadata->add_text_line($val); //debug 
						   $metadata->add_attr('num_fids'   ,$countfids );
						   $metadata->add_attr('srid'       ,$SRID      );
					       $metadata->add_attr('layer_name' ,$ONLAYER   );
						   $this->OUTPUT_GRAPH->parent_obj($metadata,$laynod);
				         } 
			      }//IF METADATA FOUND
	              /************/
                  if ($this->RUN_BBOX_QUERY){
					   $bbox_db     = @pg_query($dbconn, $BBOXQURY) ;//or die();
					   $bbox_rows   = @pg_num_rows($bbox_db);
					   $countbbox   = @pg_num_rows($bbox_db);
					  
					   if ( $countbbox!=0) {
							for ($zz=0;$zz<$bbox_rows;$zz++){			  
							   $val = pg_fetch_result($bbox_db, $zz, 0);
							   $bbxdata=$this->OUTPUT_GRAPH->newnode(('bbox_'.$ONLAYER.'_'.$zz));
							   $bbxdata->add_text_line($val); //debug 
							   $this->OUTPUT_GRAPH->parent_obj($bbxdata,$metadata);
							   
							}               
					   }//				  
				  
			      }//bbox 		
				  
			   }//meta 
			   
			   //$BBOXQURY
			   
				 
    }//TEST NUMBER THREE 
   }//run block 
  
  

   /***********************************/  
 

  function split_string($stringsplit ,$char){
	 
     $tmp= split( $char ,$stringsplit );	
	 $out = array();
	 for($cl=0;$cl<count($tmp);$cl++)
	 {
	   array_push($out,$this->sanitize( $cl ) );
	 }
     return $out;
  }   
   /*************/
	
  //split,clean and decode
  function sanitize($str){
     $scrubz  = PREG_REPLACE("/[^0-9a-zA-Z]/i", '', $str);
	 return urldecode($scrubz);
  }
   /****************/ 
   function ar_decode($array){
     
  	 $out = array();
	 for($cl=0;$cl<count($array);$cl++)
	 {
	   array_push($out,urldecode($array[$cl] ) );
	 }
	 return $out;	 
   }
   /****************/  
   //THIS WILL RUIN WKT! (SPACES , PARENTHESIS ,ETC)
  function clean_array($array){
  	 $out = array();
	 for($cl=0;$cl<count($array);$cl++)
	 {
	   array_push($out,$this->sanitize( $array[$cl] ) );
	 }
	 return $out;
  }
   /*************/
   
   function ar_print(){
		 $arg = func_get_args();
		  print '<table border=2>';
		   foreach ($arg[0] as $value) {
		   print '<tr>';
		   print '<td>';
		 	print urldecode($value);
		   print '</td>';
		   print '</tr>';		   
		   }
		   print '</table>';
	}

  
    /***********/

  var $CLASS_NAME = 'geode_geom_geo_bot';
}

/************************************/
   
class geom_point{

 var $pt_x = 0.0;
 var $pt_y = 0.0;
 var $pt_z = 0.0;
 
}

/************************************/
   
class geom_line{

 var $verticies = array();
 var $encoding = 'wkt';
 
}
 
/************************************/
   
class geom_poly{

 var $verticies = array();
 var $encoding = 'wkt';
 
}
 
 



?>


