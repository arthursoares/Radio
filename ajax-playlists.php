<?
include_once($_SERVER['DOCUMENT_ROOT'].'/wp-load.php' );
include_once("config.php");
global $wpdb;
$playlists = $wpdb->get_results("select id, titulo from $tbl_playlist where id_programa='".$_POST['id']."' order by titulo asc");
?>
<h2>Playlists</h2>
<ul class="playlists-ul">
<?
foreach ($playlists as $pl) {
	?><li class="lista-playlist" rel="<?=$pl->id?>"><?=$pl->titulo?></li><?
}
?>
</ul>