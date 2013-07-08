<?php
/*
Plugin Name: Get_Posts/Pages
Description: Creats a shortcode based on get_pages and get_posts
Version: 0.1
Author: Jonathan Stanley
Author URI: http://bristleconeweb.com/
License: GPL2
Text Domain: get_pp
*/

// devs should add their filters with filter priority > 0
// see http://codex.wordpress.org/Function_Reference/add_filter#Parameters
add_shortcode( 'getpp', 				'getpp_shortcode' ); 
add_filter('getpp_filter_args',			'getpp_filter_args_default',10); 
add_filter('getpp_filtertemplate',		'getpp_filtertemplate_default',10);
add_filter('getpp_getposts',			'getpp_getposts_default',10);

function getpp_shortcode($args){
	$template = $args[template]; unset($args[template]);  //pull out template
	$args = apply_filters('getpp_filter_args',$args); // convert variables  ex: this > 123.  apply_filters allows override
	$posts = apply_filters('getpp_getposts',$args);
	if (!$posts) return;
	$template = 'getpp_template_' . apply_filters('getpp_filtertemplate', $template);
	$output = apply_filters($template,$posts,$args);
	return $output;
}
function getpp_filter_args_default($args){
	add_filter('getpp_argfilter_pagerelation',	'getpp_argfilter_pagerelation_default',10); 
	add_filter('getpp_argfilter_post_type',		'getpp_argfilter_post_type_default',10); 
	add_filter('getpp_argfilter_catrelation',	'getpp_argfilter_catrelation_default',10); 
	$filters = array(
		'parent'=>			'getpp_argfilter_pagerelation',
		'child_of'=>		'getpp_argfilter_pagerelation', 
		'include'=>			'getpp_argfilter_pagerelation', 
		'exclude'=>			'getpp_argfilter_pagerelation', 
		'post_type'=>		'getpp_argfilter_post_type',
		'category'=>		'getpp_argfilter_catrelation'
		);
	foreach ($args as $key => $value){
		if (!empty($filters[$key])){
			$args[$key] = apply_filters($filters[$key],$args[$key]);
		}
	}
	return $args;
}

function getpp_argfilter_post_type_default($arg){
	if(empty($arg))
		return get_post_type( get_the_ID() );
	return $arg;
}
function getpp_argfilter_pagerelation_default($value){
	$value = getpp_pagerelation_this($value);
	$value = getpp_pagerelation_parent($value);
	$value = getpp_pagerelation_top($value);
	return $value;	
}
	function getpp_pagerelation_this($value){
		return str_replace('this', get_the_ID(), $value);
	} 
	function getpp_pagerelation_parent($value){
		if(strstr($value, 'parent')){
			$ancestors = get_post_ancestors(get_the_ID());
			$count = count(explode("_",$value));
	    	$id = $ancestors[$count-1];
	    	if(count($ancestors) < $count)
	    		$id = $ancestors[count($ancestors)-1];
	        $value = $id;
	    }
	    return $value;
	}
	function getpp_pagerelation_top($value){
		if(strstr($value, 'top')){
			$ancestors = array_reverse(get_post_ancestors(get_the_ID()));
			return $ancestors[0];
		}
		return $value;
	}

function getpp_argfilter_catrelation_default($value){
	$value = getpp_catrelation_this($value);
	return $value;	
}
	function getpp_catrelation_this($value){
		$cats = get_the_category(get_the_ID());
		$catids = array();
		foreach ($cats as $cat) {
			array_push($catids, $cat->cat_ID);
		}
		$return = str_replace('this', implode(',', $catids), $value);
		return $return;
	} 

function getpp_getposts_default($args){
	switch ($args[func]) {
		case 'get_posts':
			return get_posts($args);
			break;
		case 'get_pages':
			return get_pages($args);
			break;
		default:
			return "Shortcode is missing the 'func' parameter.";
			break;
	}
}

function getpp_filtertemplate_default($args){
	if (!empty($args[template])) {
		if (has_filter('getpp_template_'.$args[template])) {
			return $args[template];
		}
	}
	add_filter('getpp_template_default','getpp_template_default_default',10,2); 
	return 'default';	
}

/**
 * This function renders a simple list of posts in a Bootstrap styled format.  You can override this with your own filter.  See remove_filter and add_filter in the codex.
 * @param  [type] $posts the posts returned by WordPress
 * @param  [type] $args  the original arguments specified in the shortcode
 * @return [type] returns the html output
 */
function getpp_template_default_default($posts, $args){
	$format = '<ul style="padding:0" class="nav nav-list">%s</ul>';
	$string = getpp_template_default_default_items($posts, $args);
	return sprintf($format,$string);
}
	function getpp_template_default_default_items($posts, $sargs){
		$format = '<li id="%1$s" class="%5$s"><a style="border: 1px solid #e5e5e5; margin: auto auto -1px;" href="%2$s"><i class="icon-chevron-right pull-right"></i>%4$s%3$s</a></li>';
		foreach ($posts as $key => $value) {
			$depth = getpp_depth($sargs[child_of],$value);
			if((($sargs[depth] >= 0) && ($depth <= $sargs[depth])) || (!isset($sargs[depth]))){
				$args[id] = 		'post-'. $value->ID;
				$args[href] =		get_permalink($value->ID);
				$args[title] = 		$value->post_title;
				$args[indent] = 	str_repeat('&raquo; ', $depth);
				$args[css] = 		($value->ID == get_the_ID())? 'active' : '';
				$output .= vsprintf($format, $args);
			}
		} 
		return $output;
	}


function set_getpp_meta($links, $file) {
    $plugin = plugin_basename(__FILE__);
    if ($file == $plugin) {
        return array_merge(
            $links,
            array( sprintf( '<a target="_blank" href="https://github.com/bristweb/get_pp">%s</a>',  __('Documentation') ) )
        );
    }
    return $links;
}
 
add_filter( 'plugin_row_meta', 'set_getpp_meta', 10, 2 );





/**
 * Use this function as a sample of how to create your own templates
 * @param  [type] $posts the posts returned by WordPress
 * @param  [type] $sargs the original arguments specified in the shortcode
 * @return [type] returns the html output
 */
function getpp_template_summary_default($posts, $sargs){
	$format = '<div class="media">%1$s<div class="media-body"><a href="%2$s"><h4 class="media-heading">%3$s</h4></a>%4$s</div></div>';
	global $post;
	foreach( $posts as $post ) : setup_postdata($post); 
		$depth = getpp_depth($sargs[child_of],$value);
		if((($sargs[depth] >= 0) && ($depth <= $sargs[depth])) || (!isset($sargs[depth]))){
			$args[img] = get_the_post_thumbnail($post->ID, 'thumbnail', array('class'=>'media-object pull-left'));
			$args[href] = get_permalink($post->ID);
			$args[title] = $post->post_title;
			$args[excerpt] = get_the_excerpt();
			$output .= vsprintf($format,$args);
		}
	endforeach; wp_reset_postdata();
	return $output;
}
add_filter('getpp_template_summary','getpp_template_summary_default',10,2); 

/**
 * Use this function as a sample of how to create your own templates
 * @param  [type] $posts the posts returned by WordPress
 * @param  [type] $sargs the original arguments specified in the shortcode
 * @return [type] returns the html output
 */
function getpp_template_thumbnails_default($posts, $sargs){
	$format = '<li class="span3"><a class="thumbnail" href="%2$s">%3$s<div class="text-center">%1$s</div></a></li>';
	global $post;
	foreach( $posts as $post ) : setup_postdata($post); 
		$depth = getpp_depth($sargs[child_of],$value);
		if((($sargs[depth] >= 0) && ($depth <= $sargs[depth])) || (!isset($sargs[depth]))){
			$excerpt = get_the_excerpt();
			$args[title] = $post->post_title;
			$args[href] = get_permalink($post->ID);
			$args[img] = get_the_post_thumbnail($post->ID, 'thumbnail', array('alt'	=> $excerpt,'title'=> $args[title]));
			$output .= vsprintf($format,$args);
		}
	endforeach; wp_reset_postdata();
	return '<div class="row-fluid"><ul class="thumbnails">' . $output . '</ul></div>';
}
add_filter('getpp_template_thumbnails','getpp_template_thumbnails_default',10,2); 

/**
 * This function gets the depth of the post/page
 * @param  [type] $start The top level post.  End should be a decendant of $start
 * @param  [type] $end   The post we are determining the depth of.
 * @return [type]        Returns the integer depth of the post.  If no beginning is specified, it will be the depth from the root
 */
function getpp_depth($start, $end){
	$ancestors = $end->ancestors;
	if(isset($ancestors))
		$depth = array_search($start,$ancestors);
	if (!isset($depth)) 
		return count($ancestors);
	else 
		return $depth;
}
