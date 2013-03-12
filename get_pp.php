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
add_filter('getpp_filterargs',			'getpp_filterargs_default',0); 
add_filter('getpp_filtertemplate',		'getpp_filtertemplate_default',0);
add_filter('getpp_filterposts',			'getpp_filterposts_default',0);

function getpp_shortcode($args){
	$args = array_merge(wp_parse_args($args[args]),$args);
	$args = getpp_applyargfilters(apply_filters('getpp_filterargs',$filters),$args);
	$posts = apply_filters('getpp_filterposts',$args);
	if (!$posts) return;
	$template = 'getpp_template_' . apply_filters('getpp_filtertemplate');
	$output = apply_filters($template,$posts,$args);
	return $output;
}
function getpp_filterargs_default($filters){
	add_filter('getpp_argfilter_pagerelation',	'getpp_argfilter_pagerelation_default',0); 
	add_filter('getpp_argfilter_post_type',		'getpp_argfilter_post_type_default',0); 
	add_filter('getpp_argfilter_catrelation',	'getpp_argfilter_catrelation_default',0); 
	return array(
		'parent'=>			'getpp_argfilter_pagerelation',
		'child_of'=>		'getpp_argfilter_pagerelation', 
		'post_type'=>		'getpp_argfilter_post_type',
		'category'=>		'getpp_argfilter_catrelation'
		);
}
function getpp_applyargfilters($filters, $args){
	foreach ($filters as $key => $value) {
		if (has_filter($value)) {
			if(!empty($args[$key]))
				$args[$key] = apply_filters($value,$args[$key]);
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

function getpp_filterposts_default($args){
	switch ($args[func]) {
		case 'get_posts':
			if (get_post_type(get_the_ID()) != 'post') {
				return false;
			} else {
				return get_posts($args);
			}
			break;
		case 'get_pages':
			if (get_post_type(get_the_ID()) != 'page') {
				return false;
			} else {
				return get_pages($args);
			}
			break;
		default:
			return "Shortcode is missing the 'func' parameter.";
			break;
	}
}

function getpp_filtertemplate_default($func){
	add_filter('getpp_template_default','getpp_template_default_default',0,2); 
	return $func .'default';	
}

function getpp_template_default_default($posts, $args){
	$format = '<ul style="padding:0" class="nav nav-list">%s</ul>';
	$string = getpp_template_default_default_items($posts);
	return sprintf($format,$string);
}
	function getpp_template_default_default_items($posts){
		$format = '<li id="%1$s" class="%5$s"><a style="border: 1px solid #e5e5e5; margin: auto auto -1px;" href="%2$s"><i class="icon-chevron-right pull-right"></i>%4$s%3$s</a></li>';
		$parents = array($posts[0]->post_parent);
	$depth = 0;
		foreach ($posts as $key => $value) {
			// $relation = $posts[$key+1]->post_parent;
			$parent = $value->post_parent;
			if ($parents[count($parents)-1] != $parent) {
				if (!in_array($parent, $parents)) {
					$depth++;
				}else
					$depth--;
				array_push($parents, $parent);
			}
			$args[id] = 		'post-'. $value->ID;
			$args[href] =		get_permalink($value->ID);
			$args[title] = 		$value->post_title;
			$args[indent] = 	str_repeat('&raquo; ', $depth);
			$args[css] = 		($value->ID == get_the_ID())? 'active' : '';
			$output .= vsprintf($format, $args);
		}
		return $output;
	}


function set_getpp_meta($links, $file) {
    $plugin = plugin_basename(__FILE__);
    if ($file == $plugin) {
        return array_merge(
            $links,
            array( sprintf( '<a href="http://google.com">%s</a>',  __('Documentation') ) )
        );
    }
    return $links;
}
 
add_filter( 'plugin_row_meta', 'set_getpp_meta', 10, 2 );
