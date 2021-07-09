<?php	
/*
Plugin Name: Betlix Help 
Description: Заменяет содержание стандартной Помощи в Админ-панели(кнопка справа вверху). Настройки > Помощь в админке. Заменяет везде кроме стр Настройки > Помощь в админке
Version: 1.0.1
Author: Dmitry Rozhkov 

Использование:
Требует ACF 
1) Активизировать плагин
2) Создать группу полей с элементом "Повторитель" и 2-мя полями:
		Закладка - help_menu_item  - Текст - ширина 20%
		Содержание help_content_item - Редактор WordPress
   Условия отображения: "Страница с опциями" = "Помощь в админке"
3) Заполнить Настройки > Помощь в админке.

В тексте можно использовать html. Если оказался незакрытый тег и в админке ничего не будет отображатся, кроме меню, то в этом случае можо отредакитровать текст в Настройки > Помощь в админке. На этой странице Help отключен.
*/
if( function_exists('acf_add_options_sub_page') ) {
	acf_add_options_page(array(
		'parent_slug'	=> 'options-general.php',
		'page_title' 	=> 'Помощь в админ-панели',
		'menu_title'	=> 'Помощь в админке',
		'menu_slug' 	=> 'betlix_help',
		'capability'	=> 'edit_posts',
		'redirect'		=> false
	));
}
add_action('acf/save_post', 'help_save_post');
function help_save_post($post_id) {
	$value = get_field( 'field_1', $post_id );
	if(strpos($value,  '#####') !== false) {
		$data = array_filter(explode("#####", $value), function($var) { return trim($var); });
		if(!empty($data)) {
			update_field('betlix_help', null, 'option');
			$keys = ['help_menu_item', 'help_content_item'];
			foreach($data as $row) {
				$row_data = array_filter(explode("-----", $row), function($var) { return trim($var); });
				if(count($row_data) == 2) {
					$add = array_combine($keys, $row_data);
					add_row('betlix_help', $add, 'option');
				}
			}
		} 
	}
	update_field('field_1', '', $post_id);
}
add_action( 'acf/init', 'acf_help_init');
function acf_help_init() {
	if( function_exists('acf_add_local_field_group') ):
		if( empty(acf_get_local_field("field_1")) ) {
			acf_add_local_field_group(array(
				'key' => 'group_1',
				'title' => 'Импорт/экпорт',
				'fields' => array (
					array (
						'key' => 'field_1',
						'label' => 'Введите данные для импорта',
						'name' => 'sub_title',
						'type' => 'textarea',
					)
				),
				'location' => array (
					array (
						array (
							'param' => 'options_page',
							'operator' => '==',
							'value' => 'betlix_help',
						),
					),
				),
				'position' => 'normal',
			));
		}
	endif;
}
add_action('acf/render_field/key=field_1', 'render_fields');
function render_fields(){
	if(is_admin() & strpos($_SERVER['REQUEST_URI'], 'betlix_help') > 1) {
		$value = get_field( 'betlix_help', 'option' );
		if(is_array($value)) {
			echo "<div class='acf-label'><label>Данные для импорта</label></div><div class='acf-output'>";
			foreach($value as $item) {
				echo "<br>#####<br>";
				echo "-----";
				echo $item['help_menu_item'];
				echo "-----<br>";
				echo htmlspecialchars($item['help_content_item']);
			}
			echo "</div>";
		}
	}
}
add_action( 'admin_head', 'betlix_help_remove_contextual_help' );
function betlix_help_remove_contextual_help() {
	if(is_admin() & strpos($_SERVER['REQUEST_URI'], 'betlix_help') === false) {
		$help_items = get_field('betlix_help', 'option');
		$help_num = get_field('betlix_help_num', 'option');
		if($help_num < 3) $help_num = 3;
		function cmp($a, $b)
		{
			return strcmp($a["help_menu_item"], $b["help_menu_item"]);
		}
		usort($help_items, "cmp");
		$help_items = array_map(function($item) { $item['help_content_item'] = str_replace("\n", "\n<br>", $item['help_content_item']); return $item; }, $help_items ); // Чтоб не сливалось в одну строку добавляем перевод строки <br>
		$last = count($help_items) - 1; // Номер посленего элемента в массиве для цикла
		$dispabled = []; // Временный массив для неактивных шорткодов
		$rules =[]; // для  правил
		/* Удалим из основного массива неактивные шорткоды и правила и перенесем в соответсвенные массивы */
		foreach($help_items as $key=>$help) {
			if(strpos($help['help_menu_item'], '?') === 0) {
				$dispabled['help_content_item'] .= "<h3>".$help_items[$key]['help_menu_item']."</h3><p>".$help_items[$key]['help_content_item']."<p>";
				unset($help_items[$key]);
			} else if ($help['help_menu_item'] == "Правила использования шорткодов") {
				$help['help_content_item'] = "Обратите внимание:<br>".$help['help_content_item'];
				$rules = $help;
				unset($help_items[$key]);
			}
		}
		$help_items = array_values($help_items); // Обновим нумерацию элементов элементов массива, чтоб корректно работал цикл ниже
		$last = count($help_items); // Обновим значение поледнего элемента. Должно быть больше на 1 из-за внутреннего цикла!
		/* Цикл объединяет шорткоды в группы по три. Данные вносятся в 1 4 7 10 и т.д. А остальные элементы удаляются */
		for($i=1; $i <= $last; $i += $help_num) {
			$content = '';
			$menu_item = [];
			for($j=-1; $j < ($help_num - 1); $j++) {
				if(!empty($help_items[$i + $j])) {
					$content .= "<h3>".$help_items[$i + $j]['help_menu_item']."</h3><p>".$help_items[$i + $j]['help_content_item']."</p>";
					array_push($menu_item, $help_items[$i + $j]['help_menu_item']);
					unset($help_items[$i + $j]);
				}
			}
			$help_items[$i]['help_menu_item'] = implode(", ", $menu_item);
			$help_items[$i]['help_content_item'] = $content;
		}
		/* Возвращаем в основной массив неактивные шорткорды и правила  */
		if(!empty($rules))
			array_unshift($help_items, $rules);
		if(!empty($dispabled)) {
			$dispabled['help_menu_item'] = "Неактивные шорткоды";
			array_push($help_items, $dispabled);
		}
		$args = array();
		//$i = 100;
		foreach($help_items as $help_item) {
			$args[] = array(
				'title' => $help_item['help_menu_item'],
				'id' => $i++,
				'content' => $help_item['help_content_item'],
				'callback' => false,
				'priority' => 10 );
		
		}
		$screen = get_current_screen();
		$screen->remove_help_tabs();
		$screen->set_help_sidebar('');
		foreach($args as $arg) {
			$screen->add_help_tab( $arg );
		}	
	}
}
function my_stylesheet(){
    wp_enqueue_style("style-admin", plugins_url('css/style-admin.css', __FILE__));
}
add_action('admin_head', 'my_stylesheet');