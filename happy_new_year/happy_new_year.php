<?php	
/*
Plugin Name: Новогодняя шапка
Description: Заменяет содержание стандартной шапки на украшенную к новогодним праздникам
Version: 1.0.1
Author: Red.Nekus 

Использование:
1) Активизировать плагин
*/
function new_year_stylesheet(){
	wp_enqueue_style( "style-christmas", plugins_url('css/style-christmas.css', __FILE__) );
	wp_enqueue_script( 'christmas-script', plugins_url( '/js/snow.js', __FILE__));
	wp_enqueue_style( "style-snow", plugins_url('css/snow.css', __FILE__) );
}
add_action('wp_enqueue_scripts', 'new_year_stylesheet');

function insert_inline_styles_new_year()
{
	global $inline_styles;
	if(!$inline_styles)
		return;
	$inline_styles->add_style('benon-first-style-happy-new-year', WP_CONTENT_DIR . '/plugins/happy_new_year/css/style-first.css', 60, 'Стили для новогодней шапки');
}
add_action('wp', 'insert_inline_styles_new_year', 100);