<?php	
/*
Plugin Name: Betlix News Slider
Description: Заменяет стили в админке
Version: 1.0.1
Author: Red.Nek
*/
add_action( 'wp_enqueue_scripts', 'news_stylesheet' );
add_action('base_classes_loaded', 'include_functions_betlix_news_slider');
function include_functions_betlix_news_slider()
{
	require_once( plugin_dir_path( __FILE__ ) . '/functions.php');
}
function n_slider() {
    if(is_single() &&  beton_format_sel() == 'news') {
        remove_action('beton_loop_content_begin', 'beton_loop_content_begin_default', 50);
        add_action('beton_loop_content_begin', 'beton_loop_content_begin_news', 50);
        add_action('loop_start', function(){
            remove_action('beton_loop_content_end', 'beton_loop_content_end_default', 50);
        });
        add_action('beton_loop_content_end', 'beton_loop_content_news_end', 50);
        add_filter('body_class', 'news_slider_body_class');
    }
}
add_action('wp', 'n_slider');
function news_slider_ajax_submit(){
    global $post;
    $cat = (isset($_POST['category_id'])) ? $_POST['category_id'] : 0;
    $dt = (int) $_POST['date'];
    $args = [
        'numberposts' => 1,
        'orderby'     => 'date',
        'order'       => 'ASC',
        'post_status' => 'publish',
        'category'    => $cat,
        'date_query'  => [ 'after' =>  date("Y-m-d H:i:s", $dt), 'column' => 'post_date_gmt' ]
    ];
    $get_posts = get_posts($args);
    if(is_array($get_posts)) {
        $post = array_shift($get_posts);
        if(!empty($post->ID)) {
            setup_postdata( $post );
            beton_loop_news();
        }
    }
    wp_die();
}
add_action( 'init', function() {
    if(function_exists('add_action')) {
        add_action( 'wp_ajax_nopriv_news-slider-ajax-submit', 'news_slider_ajax_submit' );
        add_action( 'wp_ajax_news-slider-ajax-submit', 'news_slider_ajax_submit' );
    }
});