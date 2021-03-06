<?php
/*
   Plugin Name: cals_teams
   Plugin URI: http://wordpress.org/extend/plugins/cals_teams/
   Version: 0.1
   Author: Daniel Dropik & Al Nemec
   Description: Define Custom templates in theme by creating folder cals_teams_templates/single.php 
   Text Domain: cals_teams
   License: GPLv3
  */
//version from cals.main

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

include( plugin_dir_path(__FILE__) .'includes/data/cals_teams_fields.php' );

$spath = plugin_dir_path(__FILE__) .'includes/data/cals_teams_fields.php';
logit($spath,'$spath: ');
//Define Constants

if(!defined('CT_PLUGIN_BASE_FILE')){
  define('CT_PLUGIN_BASE_FILE',__FILE__);
}

if(!defined('CT_PLUGIN_BASE_DIR')){
  define('CT_PLUGIN_BASE_DIR',dirname(CT_PLUGIN_BASE_FILE));
}

if(!defined('CT_PLUGIN_URL')){
  define('CT_PLUGIN_URL', plugin_dir_url(__FILE__));
}



function create_cals_teams_post_type() {

  register_post_type( 'cals_team_members',
    array(
      'labels' => array(
        'name' => __( 'Team Members','cals_teams' ),
        'singular_name' => __( 'Team Member','cals_teams' )
        ),
      'public' => true,
      'has_archive' => true,
      'taxonomies'=>array('cals_groups'),
      'supports' => array(
        'title',
        'editor',
        'excerpt',
        'revisions',
        'thumbnail',
        'author',
        'page-attributes',
        ),
      'add_meta_box_cb'=>'add_cals_teams_metaboxes'
      )
    );
}
add_action( 'init', 'create_cals_teams_post_type' );


function create_cals_teams_taxonomies(){

  register_taxonomy('cals_groups','cals_team_members',

    array(
      'labels'=>array(
        'name'=>__('Groups','cals_teams'),
        'singular-name'=>__('Group','cals_teams'),
        ),
      'public'=>'true',
      'heirarchical'=>'true',
      'show_ui'=>'true',
      'show_in_admin_bar'=>'true',
      'show_in_nav_menus'=>'true'
      )

    );

  register_taxonomy_for_object_type( 'cals_groups', 'cals_team_members' );

}
add_action( 'init', 'create_cals_teams_taxonomies');




//add meta boxes
function add_meta_boxes_cals_team_members($post){

  global $mbox;

  add_meta_box($mbox['id'],$mbox['title'], 'calsteams_buildform_cb',$mbox['screen'],$mbox['context']);
}
add_action( 'add_meta_boxes', 'add_meta_boxes_cals_team_members' );

//generates metabox markup on admin
function calsteams_buildform_cb($post){

  global $mbox, $post;//bring in these variables from global scope

  $mbox_data = get_post_custom($post->ID); //get array containing metabox custom fields

  logit($mbox_data,'$mbox_data: ');

  wp_nonce_field( 'calsteams_update_field', 'calsteams_nonce');

  echo '<table class="form-table">';

  foreach ($mbox['fields'] as $field) {

    $meta = get_post_meta($post->ID,$field['id'],true); //get meta-box data for current field

    echo '<tr>',
                '<th style="width:20%"><label for="', $field['id'], '">', $field['name'], '</label></th>',
                '<td>';

                switch ($field['type']) {
                  case 'text':
                    echo '<input type="text" name="', $field['id'], '" id="', $field['id'], '" value="', $meta ? $meta : $field['std'], '" size="30" style="width:97%" />', '<br />', $field['desc'];
                    break;

                  default:
                    echo 'uh oh, default case!';
                }

          echo '</td>',
            '</tr>';
  }
  echo '</table>';
}

//save metabox form data
function calsteams_mbox_save($post_id){
  // Checks save status
  global $mbox;

  $is_autosave = wp_is_post_autosave( $post_id );
  $is_revision = wp_is_post_revision( $post_id );
  $is_valid_nonce = ( isset( $_POST['calsteams_nonce'] ) ) && wp_verify_nonce($_POST['calsteams_nonce'],'calsteams_update_field') ? 'true' : 'false';

  // Exits script depending on save status
  if ( $is_autosave || $is_revision || !$is_valid_nonce ) {
    return;
  }

  //Real foreach, temporarily commented out
  foreach ($mbox['fields'] as $field) {

      $input_id = $field['id'];//get the current item's input id property

      // Checks for input and sanitizes/saves if needed
      if( isset( $_POST[ $input_id ] ) ) {

        update_post_meta( $post_id, $input_id, sanitize_text_field( $_POST[ $input_id ] ) );
      }
  }
}
add_action('save_post', 'calsteams_mbox_save');

function template_chooser($template){
  $post_id = get_the_ID();

  if(get_post_type($post_id) != 'cals_team_members'){

    return $template;
  }

  if(is_single()){
    return ct_get_template_hierarchy('single');
  }
}
add_filter('template_include','template_chooser');


/**
 * Get the custom template if is set
 *
 * @since 1.0
 */
 
function ct_get_template_hierarchy( $template ) {
 
    // Get the template slug
    $template_slug = rtrim( $template, '.php' );//single
    $template = $template_slug . '.php'; //single.php

    //logit($template,'$template: ');
    //logit($template_slug,'$template_slug: ');

    //$locate = locate_template( array( 'plugin_templates/single.php' ) );
    //$locateString = 'plugin_template/' . $template;
    //logit($locateString,'$locateString: ');
    //logit($locate,'$locate: ');
 
    // Check if a custom template exists in the theme folder, if not, load the plugin template file
    if ( $theme_file = locate_template( array( 'cals_teams_templates/' . $template ) ) ) {
        $file = $theme_file;
        logit($file,'$file: ');

    }
    else {
        $file = CT_PLUGIN_BASE_DIR . '/includes/templates/' . $template;
    }
 
    //return apply_filters( 'rc_repl_template_' . $template, $file );
    return $file;
}
add_filter( 'template_include', 'template_chooser' );
