<div class="wrap">
<h2>Rádio Clasta <a href="admin.php?page=radio/criar.php" class="add-new-h2">Criar nova playlist</a></h2>
<h3>Playlists</h3>
<?
require_once("config.php");
global $wpdb, $tbl_playlist, $tbl_musicas;
if ($_GET['act'] == "deletar") {
	$wpdb->query("delete from $tbl_playlist where id='".$_GET['id']."'");
}
$playlists = $wpdb->get_results("select id, titulo, musicas from $tbl_playlist order by id asc");
?>
<table class="widefat" cellspacing="0">
	<thead>
	<tr>
		<th scope='col' id='cb' class='manage-column column-cb check-column'  style=""><input type="checkbox" /></th><th scope='col' id='username' class='manage-column column-title sortable desc'  style="width:90%"><span>Nome da playlist</span></th><th scope='col' id='posts' class='manage-column column-posts num'  style="width:5%">Músicas</th></tr>
	</thead>

	<tfoot>
	<tr>
		<th scope='col' class='manage-column column-cb check-column'  style=""><input type="checkbox" /></th><th scope='col'  class='manage-column column-username sortable desc'  style="width:5%"><span>Nome da playlist</span></th><th scope='col' id='posts' class='manage-column column-posts num'  style="">Músicas</th></tr>
	</tfoot>

	<tbody id="the-list" class='list:user'>
	<?
foreach ($playlists as $pl) {
	$exp = explode(",", $pl->musicas);
	$count = count($exp);
	?>
	<tr id='user-1' class="alternate"><th scope='row' class='check-column'><input type='checkbox' name='users[]' id='user_1' class='administrator' value='1' /></th><td><strong><a href="admin.php?page=radio/criar.php&id=<?=$pl->id?>"><?=$pl->titulo?></a></strong><br /><div class="row-actions"><span class='edit'><a href="admin.php?page=radio/criar.php&id=<?=$pl->id?>">Editar</a> | </span><span class='delete'><a class='submitdelete' href='admin.php?page=radio/index.php&act=deletar&id=<?=$pl->id?>'>Excluir</a></span></div></td><td style="text-align:center"><a href='admin.php?page=radio/criar.php&id=<?=$pl->id?>' title='Ver posts desse autor'><?=$count?></a></td></tr>
	<?
}
?>
</table>
</div>