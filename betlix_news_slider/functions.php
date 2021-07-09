<?php
function beton_news_slider_rating()
{
	global $post; 
	$rating = unserialize(get_post_meta($post->ID, 'news_ist_rating', true)); ?>
	<div class="clear"></div>
	<div class="b_n_rating" data-id="<?php echo $post->ID; ?>">
		<div class="b_n_r_blocks"><span style="text-align: center; width: 100%">Загружаем оценки...</span></div>
	</div>
<?php }

function beton_news_partners()
{
	global $options_class;
	$options_option = get_option($options_class->option_name); ?>
	<?php if($options_option['lentainform_news_checkbox']) { ?>
	<section id="news-similar">
        <p class="section-title">Новости партнеров</p>
		<div class="l_i_block" id="M616783ScriptRootC971048"></div>
	</section>
    <?php } ?>
<?php }

function beton_loop_content_news_end()
{
	if (!is_page()) { 
		$cat_id = get_the_category();
		if(is_array($cat_id))
			$cat_id = array_shift($cat_id);
		if(isset($cat_id))
			$cat_id = $cat_id->cat_ID; ?>
		<section id="post-meta" data-section="<?=$cat_id?>">
		<?php
			beton_post_news_block(); 
		?>
		</section>		
		<?php
		beton_news_partners();
		get_sidebar("post");
	}
}

function beton_loop_content_begin_news()
{
	while ( have_posts() ) : the_post();
		beton_loop_news(true);
	endwhile;
}

function beton_loop_news($speechki=false) { ?>
	<section data-time="<?=get_post_time( 'U', true )?>" data-link="<?=the_permalink()?>">
	<?php 
		beton_single_title(); 
		beton_post_author_news();  
	?>             
	</section>
	<section class="news">
	<?php
	beton_single_thumb();
	if($speechki)
		posts_ozvuchka_speechki(get_the_ID());
	beton_single_text_news();   
	if( has_action('сall_user_social') ){
		do_action('сall_user_social');	
	}
	if ( is_plugin_active( 'good_news_rating/good_news_rating.php' )) {
		beton_news_slider_rating();
	} ?>
	</section>
<?php }
function news_stylesheet(){
	wp_enqueue_style("style-news", plugins_url('css/style-slider-news.css', __FILE__));
	wp_register_script('slider-news-script',  plugins_url( '/js/news_slider.js', __FILE__));
	wp_enqueue_script('slider-news-script');
}
function news_slider_body_class($classes) {
	$classes[] = 'news_slider';
	return $classes;
}