<?php 
///FIELD DEFINITIONS

//Variables
$prefix = 'calsteams_';// 

//fields. Prefixed by "field_"
$field_office_location = array(
  'name'=>'Offie Location',
  'desc'=>'This is where the office is.',
  'id'=>$prefix . 'office_location', //corresponds to input field name & id
  'type'=>'text',
  'std'=>'office location',//default value
  );

//field2
$field_office_location2 = array(
  'name'=>'Offie Location2',
  'desc'=>'This is where the office is.',
  'id'=>$prefix . 'office_location_2', //corresponds to input field name & id
  'type'=>'text',
  'std'=>'office location_2',//default value
  );


//metabox args
$mbox = array(
  'id'=>'cals_teams_mbox_0',//HTML ID
  'title'=>'Team Member Data',//MetaBox Title
  'screen'=>'cals_team_members',//custom post type slug
  'context'=>'normal',//display location
  'priority'=>'default',
  'fields'=>array($field_office_location,$field_office_location2),
  );
 ?>