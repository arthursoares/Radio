<?php
/*
Plugin Name: Radio Clasta
Plugin URI: http://clasta.com.br
Description: Plugin da radio Clasta
Version: 1.0
Author: Clasta
Author URI: http://clasta.com.br
*/
include(WP_PLUGIN_DIR."/radio/config.php");
function addMenu () {
	add_menu_page('Radio Clasta', 'Radio', 10, 'radio/index.php');
	add_submenu_page('radio/index.php', 'Criar Playlist', 'Criar Playlist', 10, 'radio/criar.php');
}
function radio_init() {
	if (!is_admin()) {
		wp_deregister_script('radiojs');
		wp_register_script('radiojs', plugins_url('/js/soundmanager2.js',__FILE__), false, '2.97a.20111030'); 
		wp_enqueue_script('radiojs');
		wp_deregister_script('radiojs2');
		wp_register_script('radiojs2', plugins_url('/js/radio.js',__FILE__), false, '0.1'); 
		wp_enqueue_script('radiojs2');
		wp_register_style('radio', plugins_url('/css/radio.css',__FILE__));
		wp_enqueue_style('radio');
	}
}
function add_radio(){
	include 'frontend.php';
}
function radio_embed_playlist($s){
	preg_match('/\[radio=(\d+)+\]/', $s, $matches);
	if ($matches) {
		include_once('config.php');
		$xml = simplexml_load_file($_SERVER['DOCUMENT_ROOT']."/wp-content/radio/playlist_".$matches[1].".xml");
		ob_start(); ?>
		<div id="radio_embed">
		<div class="artwork">
			<img src="/wp-content/uploads/<?=$xml['encarte_big']?>">
		</div>
		<div class="playlist">
		<h2><?=$xml['title']?></h2>
		<ol><?
		$i = 1;
		foreach ($xml->music as $music) {
			?><li class="musica-<?=$i?>"><a href="<?=radio_url?>/<?=$music->directory?>/<?=stripslashes($music->file)?>" rel="<?=$xml['id_prog']?>:<?=$matches[1]?>" class="radio-musica"><?=stripslashes($music->artist)?> - <?=stripslashes($music->title)?></a></li><?
			$i++;
		}
		?>
		</ol></div>
		</div><?
		$embed = ob_get_contents();
		ob_end_clean();
		return preg_replace('/\[radio=(\d+)+\]/', $embed, $s);
	}
	else {
		return $s;
	}
}
//Ação de criar menu
add_action('admin_menu', 'addMenu');
add_action('init', 'radio_init');

add_filter('the_content', 'radio_embed_playlist');

?>