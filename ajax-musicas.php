<?
include_once('config.php');
$xml = simplexml_load_file($_SERVER['DOCUMENT_ROOT']."/wp-content/radio/playlist_".$_POST['id'].".xml");
?>
<h2><?=$xml['title']?></h2>
<img src="/wp-content/uploads/<?=$xml['encarte']?>">
<ol>
<?
foreach ($xml->music as $music) {
	?><li><a href="<?=radio_url?>/<?=$music->directory?>/<?=stripslashes($music->file)?>" rel="<?=$_POST['id']?>" id_music="<?=$music->id?>"><?=stripslashes($music->artist)?> - <?=stripslashes($music->title)?></a></li><?
}
?>
</ol>