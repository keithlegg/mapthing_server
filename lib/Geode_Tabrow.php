<?php

/*
   
    -creates/edits nodes and trees
    -imports/exports xml
    
	used to send queries to server (in a daisy chain (X3) ) 
   
    Keith Legg - Sep 2-6,2011
*/


 ob_start();
 require_once 'Geode_Geometry.php';
 require_once 'Geode_Formats.php';
 require_once 'Geode_Parser.php';
 
/******************************/
class query_tree{
	  var $NODES    =  array();
	  
	  /*********************************/
	  //run the damn thing 
	  //SPECIALIZED FOR A STACK OF SERVER COMMANDS 
	  
	  function execute($startnode,$NARGS){
  
		  $parsedd = new get_drill_down();
		  $parsedd->read('../scene/db_config.xml');
		  $layers_dd =  $parsedd->get_layers();
  
		  /////
		  $fields_fts = $parsedd->get_fts_fields() ;
		  #print $fields_fts[1][0];		  
		  /////
	  
		  $COMMAND_OBJECT = $this->find_name($startnode);
		  $COM_ARGS       =  $COMMAND_OBJECT->qget();
		  
		  // STRUCT // COM_ARGS & NARGS
		  $SAFEQUERY = array();
		  array_push ($SAFEQUERY,$COM_ARGS[0]) ;			   
		  array_push ($SAFEQUERY,$COM_ARGS[1]) ;
		  array_push ($SAFEQUERY,$COM_ARGS[2]) ;
							
		  //... THIS CALLS GEOMETRY ,			  
		  // BUT GEOMETRY CAN CALL THIS , TOO  ...
		  $mappy = new map_api();
		  $mappy-> set_environment($layers_dd);
		  $mappy-> set_env_drilldown($fields_fts);
	  
		  $mappy-> drilldown($SAFEQUERY,$NARGS);//FILTER NARGS! DEBUG DANGER
	  }
  
	  /*****/
	  function qset($nodename,$one,$two,$three){
		$nod=$this->find_name($nodename);
		$nod->qset($one,$two,$three);
	 }
	  /*****/
	  function qget($nodename){
		$nodeobj=$this->find_name($nodename);
		if ($nodeobj){
		 return $nodeobj->qget();
		}
	 }
	  /*****/
	  function render($mode){
		 $testt = new render_text();
		 if ($mode=='docx'){
		   print 'docx is WIP - debug ';
		 }	 
		 if ($mode=='csv'){
		   $testt->run_render_csv($this,$this->firstnode() ) ;
		 }
		 if ($mode=='xml'){
		 	 //DEBUG // DECLARE ENCODING 
	        //print('<?xml version="1.0" encoding="ISO 8859-1">');
		   $testt->run_renderxml($this,$this->firstnode() ) ;
		 }
		 if ($mode=='html'){
		   $testt->render_html($this);
		 }
		 if ($mode=='json'){
		   $testt->run_render_json($this,$this->firstnode());
		 }		 
		 
	  }
	  /*****/
	  function firstnode(){
		 return $this->NODES[0];
	  }
	  
	  function numnodes(){
		 return count($this->NODES);
	  }
	  
	  /*****/ //  $nodnam,$atrname,$atrval
	  function add_attr($nodnam,$atrname,$atrval){
		 $nodeobj = $this->find_name($nodnam);
		 $nodeobj->add_attr($atrname,$atrval);
	  }  
  
	  /*****/	
	  
	  function find_name($nameget){
		$num = count($this->NODES);
		for ($n=0;$n<$num;$n++){
		  print $this->NODES[$n]->nam;//debug 
		  if ($this->NODES[$n]->name==$nameget){
			//print $this->NODES[$n]->name;
			return $this->NODES[$n];
		  }
		} 
		return 0;
	  }
	  /*****/
	  function find_obj($nameget){
		$num = count($this->NODES);
		for ($n=0;$n<$num;$n++){ 
		  if ($this->NODES[$n]===$nameget){
			return $this->NODES[$n];
		  }
		} 
		return 0;
	  }
	  
	  /*****/

	  function parent_name($cnode,$pnode){
		 $pobj = $this->find_name($pnode);
		 $cobj = $this->find_name($cnode);
		 //print ('parenting '.$cobj->name.' to '.$pobj->name);
		 if ($cobj&&$pobj){
		   //print 'parent '.$cobj->name.' to '.$pobj->name;
		   $pobj->add_child(  $cobj);
		   $pobj->has_children=1;
		   $cobj->add_parent( $pobj);
		   $cobj->has_parents =1;	   
		 }
	  }
	  /*****/		  
	  function parent_obj($cobj,$pobj){
		 if ($cobj&&$pobj){
		   //print 'parent '.$cobj->name.' to '.$pobj->name;
		   $pobj->add_child(  $cobj);
		   $pobj->has_children=1;
		   $cobj->add_parent( $pobj);
		   $cobj->has_parents =1;	   
		 }
		 
	  }		  
	  /*****/		  
	  function parent_obj_name($cobj,$pnode){
		 $pobj = $this->find_name($pnode);		  
		 if ($cobj&&$pobj){
		   //print 'parent '.$cobj->name.' to '.$pobj->name;
		   $pobj->add_child(  $cobj);
		   $pobj->has_children=1;
		   $cobj->add_parent( $pobj);
		   $cobj->has_parents =1;	   
		 }
		 
	  }	
	  //function add($nodeobj ) {
	  function link($inp_nam,$outp_nam){
		 $tmp_inp = $this->find_obj($inp_nam);
		 $tmp_out = $this->$outp_nam($outp_nam);
		 //$tmp_out.set_input($tmp_inp->name);
	  }
	  /*****/
	  function node_exists($name){
		$tmp = $this->find_name($name);
		if ($tmp) {return 1;}
		if (!$tmp){return 0;}	
	  }
	  /*****/
	  function newnode($nodename ) {
		 if (!$this->node_exists($nodename)){
		  $nodeobj = new q_node_base();
		  $nodeobj->__init__($nodename); 
		  array_push( $this->NODES ,$nodeobj );
		  return $nodeobj;
		 }
		 if ($this->node_exists($nodename)){
		  return $this->find_name($nodename); //if exists just return it 
		 }
		 return 0; //if error 
	  }//add	
	  /*****/
	  //debug wip - not tested
	  function newnode_child($nodename,$parentname ) {
		  $objfound= $this->find_name($parentname);
		  if ($this->find_name($parentname)==1){
			$newnobj = $this-> newnode($nodename);
			$this->parent_obj_name($newnobj,$parentname);//debug thi smay not work
			
		  }
		 return 0;//DEBUG - not tested
	  }//add
	  
	  /*****/
	  function add($nodeobj ) {
		 array_push( $this->NODES ,$nodeobj );
	  }//add
	  /*****/
	  function link_add($nodename,$inputnam ) {
		 if (!$this->node_exists($inputnam)){print 'link_add : '.$inputnam.' !EXISTS';}	 
		 if ($this->node_exists($inputnam)){
			 $inobj = $this->find_obj($inputnam); 
			 $nodeobj = new q_node_base();
			 $nodeobj->__init__($nodename);
			 $nodeobj->set_input($inobj);
			 //DEBUG 
			 $input = $this->find_name($inputnam);
			 $input->set_output($nodeobj->name);
			 //$nodeobj->set_input($inputnam);		 
			 array_push( $this->NODES ,$nodeobj );
		 };
	  }//add
	  /*****/
	  function addtext($nodename,$textline){ 
		 if (!$this->node_exists($nodename))
		   {print 'addtext :'.$nodename.' !EXISTS';}	 
		 if ($this->node_exists($nodename)){
			 $inobj = $this->find_obj($inputnam);
			 $inobj->add_text_line($textline);
		 }//node exists 
	  }//end addtext 

	  /*****/		  
	  function show(){
		$num = count($this->NODES);
		for ($n=0;$n<$num;$n++){
		  $tmpnod = $this->NODES[$n];
		  print '|name:     '.$tmpnod->name;
		  //print 'lnk:      '.$tmpnod->hyperlinks[0];
		  //print 'has text: '.$tmpnod->has_nodetext =1;
		  if ($tmpnod->has_nodetext){
		   print '_text '.$tmpnod->DOMtext[0];
		  }
			 
		  }
	  }//show

	  /*****/
	  function qreport_all(){
		 $num = count($this->NODES);
		for ($n=0;$n<$num;$n++){
		  print '| |'; 
		  print $this->NODES[$n]->report();
		} 
	  }
	  /*****/
	  function walk_dg_name($name){

		$foundobj = $this->find_name($name);
		$this->walk_dg($foundobj);

	  }

	  /*******************/
	  //OBJ,not NAME is trigger run walk_dg_name
	  function exestck($name,$NARGS){

		$foundobj = $this->find_name($name);
		$this->runstack($foundobj);

	  }		  
	  
	  function runstack($wnode){
		   
		   $found = $this->find_obj($wnode);
		   //if (!$found){print '|"'.$wnode->name.'" not found' ;}   
		   if ($found){
			  print '|exe|'.$wnode->name; //DEBUG  
			  /****/
			  //print 'parent is '.$wnode->    get_parents();
			  //print 'children are '.$wnode-> get_children();
			  /***/
			  $children = $found->get_children();
			  $numch = count($children);
			  for ($chi=0;$chi<$numch;$chi++){
				  $this-> runstack($children[$chi]);
			  }//recurse
		   }
	  }//end walk_dg
	  /*****/
	  function walk_links($wnode){
	  
		   $found = $this->find_obj($wnode);
		   //if (!$found){print 'walk_links: '.$wnode->name.' !exists' ;}   
		   if ($found){
			  $child = $found->get_output();
			  $chobj=$this->find_name($child);
			  $this-> walk_links($chobj);

		   }
	  
	  }
}//end query tree class
/*****************************/
	  class q_node_base{
	 
	   var $name          = '';
	   var $type          = '';
	   
	   var $points        = array(); 
	   var $line          = array(); 
	   var $poly          = array(); 
	   var $record        = array(); 
	   var $meta          = array();
	   var $DOMtext       = array();
	   
	   /*                    */
	   var $attrs_nams    = array();
	   var $attrs_vals    = array();
	   var $hyperlinks    = array();
	   var $button_img    = array();
	   /*                    */
	   var $has_attrs     = 0;   
	   var $has_parents   = 0 ;
	   var $has_children  = 0 ;
	   var $has_inputs    = 0 ;
	   var $has_outputs   = 0 ;
	   var $has_xform     = 0 ;
	   var $has_nodetext  = 0 ;//DOM kind of thing 
	   /*                    */	   
	   var $input_node    = ''; //single input/output
	   var $output_node   = ''; //single input/output
	   var $input_type    = '';
	   var $output_type   = '';
	   /*                    */	  
	   var $parent_nodes  = array();
	   var $child_nodes   = array();

	   /*************/
	   
	   /*************/ 
	   function __init__($name){
		$this->name=$name;
		$qinput   = new qi_base();
		$qrytype  = new qt_base();
		$qoutput  = new qo_base();    
	   }
	   /*************/
	   function get_attr_names(){
		 return $this-> attrs_nams ;
	   }
	   function get_attr_values(){
		 return $this-> attrs_vals;
	   }   
	   /*************/
	   function num_attrs(){
	     return count($this->attrs_nams);
	   }
	   /*************/	   
       function attr_exists($name){
	      for ($a=0;$a<$this->num_attrs();$a++){
		     if ($this->attrs_nams[$a]==$name){return 1;}
		  }
		  return 0;
	   }
	   
	   /*************/
       //added oct 5 2012	   
	   function set_type($val){
		  $this->type =$val; 
	   }
 
	   function get_type(){
		  return $this->type;  
	   }
	   
	   /*************/	   
	   function add_attr($name,$val){
		  $this->has_attrs =1; 
		
		  array_push( $this->attrs_nams,$name  );
		  array_push( $this->attrs_vals,$val   );	  
	   }
	   /*************/ 	   
	   function get_attr($name){
	      for ($a=0;$a<$this->num_attrs();$a++){
		     if ($this->attrs_nams[$a]==$name){
			    return $this-> attrs_vals[$a];
			 }//attr exists
		  }
		  return 0;	   
	   }
	   
	   /*************/ 
	   //USE NODES! NOT NAMES 
	   function add_text_line($linetext){
		  $this->has_nodetext=1;
		  array_push( $this->DOMtext,$linetext  );
	   }  
	  
       /*************/ 
	   function add_child($node){
		  $this->has_children=1;
		  array_push( $this->child_nodes,$node  );
	  
	   }  
	   
	   //USE NODES! NOT NAMES 
	   function add_parent($node){
		  $this->has_parents=1;
		  array_push( $this->parent_nodes,$node  );
	  
	   }  
	  /*************/    
	  //var $nod_attrs     = array();
	  function get_parents(){
		  if ($this->has_parents){
			return $this->parent_nodes ;
		  }
		  return 0;
	  }
	  /*************/ 
	  function get_children(){
		  if ($this->has_children){
			return $this->child_nodes ;
		  }
		  return 0;
	  }
	  //var $button_img    = array();
	  /*************/
	  function get_output(){
		return ( $this->output_node ); //NAME
	  }
	  /*************/  
	  function set_input($nodeobj,$ntype = null){
		$this->input_node = $nodeobj;
		$this->input_type = $ntype;
		
	  }
	  /*************/
	  function set_output($nodeobj,$ntype = null){
		$this->output_node = $nodeobj;
		$this->output_type = $ntype;
	  } 
	  /*************/
	  
	  function build(){
	    $querystr = ''; 
		$querystr=$querystr.( $this->qinput  ->qtype);
		$querystr=$querystr.( $this->qrytype ->qtype);
		$querystr=$querystr.( $this->qoutput ->qtype);
		
        return $querystr;	  
	  }
	  /**************/
	  function qget(){
	   $out = array();
	   
	   array_push( $out, $this->qinput->  qtype ); 
	   array_push( $out, $this->qrytype-> qtype ); 
	   array_push( $out, $this->qoutput-> qtype ); 
	   return $out;
	 
	  }	
	  function qset($one,$two,$three){
	   $this->qinput-> qtype  =$one;
	   $this->qrytype->qtype  =$two;
	   $this->qoutput->qtype  =$three;
	   
	  }
	  /*************/  
	  //report a transaction report for client side purposes
	  function report(){
		print $this->qinput->     qtype;
		print $this->qrytype->    qtype;
		print $this->qoutput->    qtype;
		
	  }
	 
	  /***/
	  function show(){
		print 'NODE NAME IS :';
		print $this->name;
		print 'NODE INPUT IS :';
		print $this->input_node; 
		print $this->input_type; 	
		print 'NODE OUTPUT IS :';
		print $this->output_node; 
		print $this->output_type; 	
		
	  }
	 
		function add_hyperlink($url){
		  array_push( $this->hyperlinks, $url);
		  
	   }
	 
	 
	    var $CLASS_NAME     = 'q_node_base';
		   
	 }//end query node class
/*****************************/
	  class qi_base{
	   var $qtype ='';
			  //is this how it will work? debug 
           function build(){
			  if ($this->$qtype=='gfid'){
				  $db_query = 'GO GET A FID';
			  }
		  }
	   //function set($type){
	   //  $this->qtype = $type;
	   //}
	   var $CLASS_NAME     = 'qi_base';
			
	 }
/*****************************/
	  class qt_base{
		var $qtype ='';
		
    	var $CLASS_NAME     = 'qt_base';
		  
	 }
/*****************************/
	  class qo_base{
	   var $qtype =''; 
	   
	   var $CLASS_NAME     = 'qo_base';
	 }

ob_end_clean(); 
  
	  
?>
