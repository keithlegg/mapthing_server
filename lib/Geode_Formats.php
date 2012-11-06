<?php

class render_text{
   var $NODES = array();
   /*******/
   function render_html($TREE){

      $NODES = $TREE->NODES;
      /****/	  
	  print '<table border="1">';
		
	  $num = count($NODES);
 
	  for ($n=0;$n<$num;$n++){
        if ($NODES[$n]->attr_exists('tabcolor') ){
			   print '<tr BGCOLOR="'.$NODES[$n]->get_attr('tabcolor').'">';
		}else{print '<tr>';}
 	      print '<td>';
			print $NODES[$n]->name;
		    if ($NODES[$n]->has_nodetext ==1 ){
			   $textarray = $NODES[$n]->DOMtext;
			   for ($tx=0;$tx<count($textarray);$tx++){
			      print '<td>';
			      print ($textarray[$tx]);
				  print '</td>';
               }			   
			}//has DOMtext
			
	      print '</td>';	
         print  '</tr>';			
	  } 
	  print '</table> ';
   }
   /*******/
   
   function xml2xhtml($xml) {
    return preg_replace_callback('#<(\w+)([^>]*)\s*/>#s', create_function('$m', '
        $xhtml_tags = array("br", "hr", "input", "frame", "img", "area", "link", "col", "base", "basefont", "param");
        return in_array($m[1], $xhtml_tags) ? "<$m[1]$m[2] />" : "<$m[1]$m[2]></$m[1]>";
    '), $xml);
   }
   /*******/

   
   /*******/
   function run_renderxml($tree,$wnode){
     $doc = new DOMDocument();
     $this->render_XML($doc,$tree,$wnode) ;
	 //echo $doc->saveXML();
 
   	 print $doc->saveXML($doc->documentElement);
	 //print ($this->xml2xhtml($doc->saveXML() ) );
   }
   /*******/	
 
   function render_XML($doc,$tree,$wnode){
	   $found = $tree->find_obj($wnode);
 	   if ($found){
	      $newnode = $doc->createElement( $found->name );
		  $gpar = $found->get_parents();
		  if ($gpar[0]){
		   $dom_parent = $doc->getElementsByTagName( $gpar[0]->name )->item(0);
		  }//KL node found 
	      $doc->appendChild( $newnode );
		  
		  //NODE TEXT
		  if ($found->has_nodetext ){
		    $debug = $found->DOMtext[0];//only firstline debug 
		    $newnode->appendChild(
              $doc->createTextNode( $debug )
	        );
		  }//has domtext
		  
		  //NODE ATTRS
          if ($found->has_attrs ){
		    $namattrs=$found->get_attr_names();
		    $valattrs=$found->get_attr_values();
			for ($ati=0;$ati<count($namattrs);$ati++){
		      $newnode->setAttribute($namattrs[$ati],$valattrs[$ati]);
			}
		  }//node attrs
 		  $children = $found->get_children();
		  for ($chi=0;$chi<count($children);$chi++){
		      $this-> render_XML($doc,$tree,$children[$chi]);
		      if ($dom_parent){ 
                $prslt= ( $dom_parent->appendChild( $newnode ) );
		      }//DOM node (type 0) found -AFTER recursive call  
		  }//recurse child nodes 
	   }//if node exists 
	   
    return $doc; 
  }//render_XML
   /*******/

   function run_render_json($tree,$wnode){
     print '[';
     $this->render_json($tree,$wnode,0) ;
     print ']';	 
   }
   
   /******************************/
   function render_json($tree,$wnode,$depth){ //,$count
	   $found = $tree->find_obj($wnode);
 	   if ($found){
		  if ($found->get_type()=='db_record'){
	          print ('[{"'.$found->get_type()).'":';
			  
			  if ($found->has_nodetext ){
				$debug = $found->DOMtext[0];
				print('"'.$found->name.'"}');
				print(',{"db_value":"'.$debug.'"}]' );
				
			  }else{print('"'.$found->name.'"}');}	
		   //if NOT a db_record attr 
		  }else{  
	          print ('{"'.$found->get_type()).'":';
			  if ($found->has_nodetext ){
				$debug = $found->DOMtext[0];
				print '"'.$debug.'"}';
			  }else{print('"'.$found->name.'"}');}				
		  }
          print',';
		  /***/
 		  $children = $found->get_children();
		  for ($chi=0;$chi<count($children);$chi++){
              $depth++;	
			  $count++;//unlike depth this does not de-increment 
  		      $this-> render_json($tree,$children[$chi],$depth);
			  $depth--;
		  }//recurse child nodes 
	   }//if node exists 
   }

}//render nodes as text class

/*********************************/

 class geometry_out{
 
    var $DOCUMENT  = Array();
    var $META      = Array();
    var $ELEMENT   = Array();
    var $GINDEX    = Array();
	
	/*****/ /*****/
	//load data to be written 
    function scribe($datastuffs){
      //$this->$DOCUMENT
	  //$this->$META 
	}
	/*****/ /*****/
	function asmbl_doc(){
	  $out     ='<geod_response>';
	  $out=$out.' <geotype>     ';
	  return $out;
	}
	/*****/	
	function asmbl_meta(){
	  $out= '<document>';
	  return $out;
	}
	/*****/	
	function asmbl_elem(){
	  $out= '<document>';
	  return $out;
	}	
	/*****/
    function asmbl_gidx(){
	  $out= '<document>';
	  return $out;
	}	
	///////////////////////////////////
	function close_doc(){
	  $out     =' </geotype>     ';
	  $out=$out.'</geod_response>';
	  return $out;
	}	
	/*****/ /*****/
    function write_geom(){
      $out= array();
	  $tmp = $this->asmbl_doc();
	  print $tmp;
	  array_push($out,$tmp);
	  $tmp = $this->close_doc();
      print $tmp;
 
	  return $out;

    }

 }

?>


