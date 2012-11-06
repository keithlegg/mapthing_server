<?php
/*
  *********************************
    XML Parser  
	Created Aug 31, 2011 Keith Legg
    Modified June 1, 2012	
  *********************************
*/

 

class get_drill_down {

    var $ddlayers   = Array();
    var $ddfields   = Array();
    var $ftsfields  = Array();
	
	function get_layers(){
	 return $this->ddlayers;
	}
	/****/
	function get_layr_fields(){
	 return $this->ddfields;
	}
	
	function get_fts_fields(){
 
	 return $this->ftsfields;
	}
	
    /*****************/
	function read($fpath) {
	    $dom = new DomDocument;
        $dom->load($fpath);
        //$ddtables = Array();

		foreach ($dom->documentElement->childNodes as $domch){
		   if ( $domch->nodeName =='drilldown'){
			   foreach ($domch->childNodes as $ddnod){
			     if ( $ddnod->nodeName =='dd_geom'){
				     foreach ($ddnod->childNodes as $ddg){	
					   
					    if ( $ddg->nodeName =='tablename'){
								//print $ddg->nodeValue;
                                array_push($this->ddlayers,$ddg->nodeValue);
	 				    }//tablename nodes
					
						#debug june 1 , 2012 
					    if ( $ddg->nodeName =='fts_attr'){
                                array_push($this->ftsfields,array($ddg->nodeValue,$ddg->nodeValue )  );
								//print  $ddnod->nodeValue;
						}//scanfields nodes				  
					    
				     }//drilldown nodes
			     }//ddgeom
		       }
		   }//drilldown
		}
     }//end read 

    /*****************/
	function read_fields($fpath) {
	    $dom = new DomDocument;
        $dom->load($fpath);
		foreach ($dom->documentElement->childNodes as $domch){
		   if ( $domch->nodeName =='drilldown'){
			   foreach ($domch->childNodes as $ddnod){
			     if ( $ddnod->nodeName =='dd_geom'){
				     foreach ($ddnod->childNodes as $ddg){	
				    						
					    if ( $ddg->nodeName =='scanfields'){
                                array_push($this->ddfields,$ddg->nodeValue);
								
						}//scanfields nodes
	
				     }//drilldown nodes
			     }//ddgeom
		       }
		   }//drilldown
	   
		}
     }//end read 

}//end class 


/******************/
//klmt.parse.xmlnodes
class parsexmlnodes {

  //var $name = "Jimbo";
  var $NODES =  Array();


   function walk_DOM($fpath){
    $dom = new DomDocument;
    $dom->load($fpath);

	foreach ($dom->documentElement->childNodes as $nodez){
	   print ('<table>');
	   print $nodez->nodeName;
	   print ('</table>');	   
	   
    }	
   }

   //***************************// 
   function read($fpath) {
    $dom = new DomDocument;
    $dom->load($fpath);
    $out = array();
	
	foreach ($dom->documentElement->childNodes as $nodez){

	   if ( $nodez->nodeName=='map-source'){
		  $chodes= ( $nodez->childNodes );
		  foreach ($chodes as $chobj){
		     //print $chobj->nodeName;
			 if ($chobj->nodeType==3){
			    //print $chobj->textContent;
			     //print $chobj.getAttribute("name");
			 }
			 if ($chobj->nodeType==3){
			    //print $chobj->textContent;
			 }
		   }
	   };//map-source 
    }
    return $out;
  }
   var $CLASS_NAME = 'phpcore.parsexmlnodes';
   
}

/******************/

//klmt.parse.sampledata
class sampleData {

   var $name = "Jimbo";
    
   function sample_layers() {	
	
   $out =  Array();
	   array_push($out,'<map-OL_Layer name="bluebuffer" type="ol_vector" projection="EPSG:XXXX">   ');
	   array_push($out,'	<url>http:///cgi-bin/mapserv?map=foocitylimit.map</url>                '               );
	   array_push($out,'	<layer name="citylimits"/>                                            ');
	   array_push($out,'    <opacity value="1" />          '                                    );
	   array_push($out,'    <isvisible value=\'True\' />      '                                   );
	   array_push($out,'</map-OL_Layer>                                                       ' );
   return  $out;
   }

   
   function screen_sample() {
		 $ARR = $this->sample_layers();
 	     $size = count($ARR);
         print $this->name;
		 print '<table border="1">';
		 for ($a=0;$a<=$size;$a++)
		 {
		   print '<tr><td>'.$ARR[$a].'</td><td>Row 1 Cell 2</td></tr>';
		 }
		 print '</table>';
   }   
   
}
?>


