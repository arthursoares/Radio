<?
global $wpdb;
define("radio_ftp_host", "artsoar.es");
define("radio_ftp_user", "radioclasta");
define("radio_ftp_pass", "g65ipMG6");
define("radio_ftp_dir", "radio.clasta.com.br/mp3/");
define("radio_url", "http://radio.clasta.com.br/mp3");

$tbl_programas = $wpdb->prefix . "radio_programas";
$tbl_playlist = $wpdb->prefix . "radio_playlist";
$tbl_musicas = $wpdb->prefix . "radio_musicas";
?>