<?
if ($_POST['id']) {
	if ($_POST['like']) {
		$sign = "+"; $return = "liked";
		if ($_COOKIE['radio_likes']) { $prefix = $_COOKIE['radio_likes']."|"; }
		setcookie("radio_likes", $prefix.$_POST['id'], time()+2592000, "/");
	}
	else {
		$exp = explode("|", $_COOKIE['radio_likes']);
		foreach ($exp as $value) {
			if ($value != $_POST['id']) {
				$musicas = $value."|";
			}
			setcookie("radio_likes", substr($musicas, 0, -1), time()+2592000, "/");
		}
		$sign = "-"; $return = "like";
	}
	include_once($_SERVER['DOCUMENT_ROOT'].'/wp-load.php' );
	include(WP_PLUGIN_DIR."/radio/config.php");
	global $wpdb;
	$wpdb->query("update $tbl_musicas set likes=likes".$sign."1 where id='".$_POST['id']."'");
	echo $return;
}
else { echo 'error'; }
?>