<?php
/*
Plugin Name: 文章浏览
Plugin URI: https://blog.wpjam.com/project/wpjam-basic/
Description: 统计文章阅读数以及在RSS或者客户端中的阅读数，激活该扩展，请不要再激活 WP-Postviews 插件。
Version: 1.0
*/

//显示浏览次数
function the_views() {
	$post_views			= wpjam_get_post_views(get_the_ID());
	$post_feed_views	= wpjam_get_post_views(get_the_ID(), 'feed_views');

	if(is_single()){	//因为累加的过程在 footer，所以显示的时候先+1
		$post_views = $post_views+1;
	}

	if(current_user_can('manage_options')){
		echo '<span class="view">浏览：'.$post_views.' | '.$post_feed_views.'</span>'; 
	}else{
		$views = $post_views + $post_feed_views;
		echo '<span class="view">浏览：'.$views.'</span>'; 
	}
}

function wpjam_get_post_total_view($post_id){
	return wpjam_get_post_views($post_id) + wpjam_get_post_views($post_id, 'feed_views') + apply_filters('wpjam_post_views_addon', 0);
}

add_action('wp_footer',function(){
	if(is_single()){ //只统计日志的浏览次数
		wpjam_update_post_views(get_the_ID());
	}
});

add_filter('wpjam_rewrite_rules', function($wpjam_rewrite_rules){
	$wpjam_rewrite_rules['feedviews/([0-9]+)\.png$']	= 'index.php?module=postviews&action=feed&p=$matches[1]';
	$wpjam_rewrite_rules['postviews/([0-9]+)\.png$']	= 'index.php?module=postviews&action=post&p=$matches[1]';
	return $wpjam_rewrite_rules;
});

add_filter('wpjam_template', function($wpjam_template, $module, $action){
	if(($module == 'postviews') && (!is_file($wpjam_template))) {
		return WPJAM_BASIC_PLUGIN_DIR.'template/postviews.php';
	}
	return $wpjam_template;
}, 10, 3);


add_action('pre_get_posts', function($wp_query){
	$module = get_query_var('module');
	if($module == 'postviews'){	// 不指定 post_type ，默认查询 post，这样custom post type 的文章页面就会显示 404
		$wp_query->set('post_type', 'any');
	}
});

