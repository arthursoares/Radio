<div class="wrap">
<h2>Rádio Clasta</h2>
<h3>Criar Playlist</h3>
<form name="radioclasta" method="post" enctype="multpart/form-data">
<?
set_time_limit(0);
ini_set('memory_limit', '64M');
require_once("config.php");
global $wpdb, $tbl_playlist, $tbl_musicas, $tbl_programas;

$ftp = ftp_connect(radio_ftp_host); 
// login with username and password
$login_result = ftp_login($ftp, radio_ftp_user, radio_ftp_pass);

if ((!$ftp) || (!$login_result)) { 
    echo "FTP connection has failed!<br>";
    echo "Attempted to connect to ".radio_ftp_host." for user ".radio_ftp_user; 
    exit; 
} else {
	if (!$_POST && !$_GET['id']) {
		$contents = ftp_nlist($ftp, "radio.clasta.com.br/mp3");
	    natcasesort($contents);
?>
<input type="hidden" name="action" value="create">
<fieldset>
<label>Escolha o diretório:</label><br>

    
    <select name="diretorio" required>
    <option value=""></option>
    <?
    foreach($contents as $dir) {
    	?>
    	<option value="<?=$dir?>"><?=$dir?></option>
    	<?
    }
    ?>
    </select>
</fieldset>
<? }
	else if ($_POST['action'] == "create") {
		?>
		<input type="hidden" name="action" value="id3">
		<input type="hidden" name="diretorio" value="<?=$_POST['diretorio']?>">
		<input type="text" name="titulo" placeholder="Nome da playlist"><br>
		Programa: <select name="programa">
		<?
		$programas = $wpdb->get_results("select id, titulo from $tbl_programas order by titulo asc");
	      foreach ($programas as $pl) {
	      ?>
	         <option value="<?=$pl->id?>"><?=$pl->titulo?></li>
	      <?
	      }		?>
		</select>
		<?
		$contents = ftp_nlist($ftp, $_POST['diretorio']);
    	natcasesort($contents);
    	$i = 0;
    	require_once('id3.php');
    	$oReader = new ID3TagsReader();
    	foreach ($contents as $file) {
       		if (substr($file, -4) == ".mp3") {
       			$filename = tempnam('../wp-content/tmp','getid3');
    			$mp3 = substr($file, strlen($_POST['diretorio'])+1);
    			?>
    			<p><?=$mp3?><br>
    			<input type="hidden" name="file[<?=$i?>]" value="<?=$mp3?>"><?
    			$file_mp3 = "http://".str_replace("%2F", "/", rawurlencode($file));
    			if (file_put_contents($filename, file_get_contents($file_mp3, false, null, 0, 10000))) {
    			   $tags = $oReader->getTagsInfo($filename);
				   unlink($filename);
				}
    			?>
    			<input type="text" name="artist[<?=$i?>]" value="<?=preg_replace('/[^a-zA-Z0-9\-\.\&\s\(\)\,\'\"]/','', $tags['Author'])?>" placeholder="Artista">
    			<input type="text" name="title[<?=$i?>]" value="<?=preg_replace('/[^a-zA-Z0-9\-\.\&\s\(\)\,\'\"]/','', $tags['Title'])?>" placeholder="Titulo"></p>
    			<?
    			$i++;
    		}
    		flush();
    	}
    	?>
    	<input type="file" name="encarte"><?
	}
	else if ($_POST['action'] == "id3") {
		if ($_FILES['encarte']['type'] == "image/jpeg") {
			$upload = wp_upload_bits("radio_".md5($_POST['id']).".jpg", null, file_get_contents($_FILES['encarte']['tmp_name']));
			$upload_resize_big = image_resize($upload['file'], 770, 770, true);
			$upload_resize = image_resize($upload['file'], 200, 200, true);
			if (!is_wp_error($upload_resize_big)) {
				$encarte_tmp_big = $upload_resize_big;
			}
			else {
				$encarte_tmp_big = $upload['file'];
			}
			$exp = explode("/", $encarte_tmp_big);
			foreach($exp as $key => $value) {
				if ($key >= (count($exp)-3)) {
					$encarte_big .= "/".$value;
				}
			}
			if (!is_wp_error($upload_resize)) {
				$encarte_tmp = $upload_resize;
			}
			else {
				$encarte_tmp = $upload['file'];
			}
			$exp = explode("/", $encarte_tmp);
			foreach($exp as $key => $value) {
				if ($key >= (count($exp)-3)) {
					$encarte .= "/".$value;
				}
			}
			$sql_encarte = $encarte_big.",".$encarte;
		}
		$ids = "";
		$xml = new SimpleXMLElement("<playlist></playlist>");
		$xml->addAttribute('title', $_POST['titulo']);
		$xml->addAttribute('id_prog', $_POST['programa']);
		if ($sql_encarte) {
			$xml->addAttribute('encarte_big', $encarte_big);
			$xml->addAttribute('encarte', $encarte);
		}
		for ($i = 0; $_POST['file'][$i]; $i++) {
			$wpdb->query("insert into $tbl_musicas (diretorio, arquivo, artista, titulo) values ('".substr($_POST['diretorio'], strlen(radio_ftp_dir))."', '".$_POST['file'][$i]."', '".$_POST['artist'][$i]."', '".$_POST['title'][$i]."')");
			$last = $wpdb->get_row("select id from $tbl_musicas order by id desc limit 1");
			$ids .= $last->id.",";
			$musica = $xml->addChild('music');
			$musica->addChild('id', $last->id);
			$musica->addChild('artist', htmlentities($_POST['artist'][$i]));
			$musica->addChild('title', htmlentities($_POST['title'][$i]));
			$musica->addChild('directory', substr($_POST['diretorio'], strlen(radio_ftp_dir)));
			$musica->addChild('file', $_POST['file'][$i]);
		}
		$wpdb->query("insert into $tbl_playlist (titulo, musicas, diretorio, encarte_big, encarte, id_programa) values ('".$_POST['titulo']."', '".substr($ids,0,-1)."', '".substr($_POST['diretorio'], strlen(radio_ftp_dir))."', '".$encarte_big."', '".$encarte."', '".$_POST['programa']."')");
		$last = $wpdb->get_row("select id from $tbl_playlist order by id desc limit 1");
		$xml->asXML("../wp-content/radio/playlist_".$last->id.".xml");
		?><script>location.href='/wp-admin/admin.php?page=radio/index.php';</script><?
	}
	else if ($_GET['id']) {
		if ($_POST['action'] == "update") {
			if ($_FILES['encarte']['type'] == "image/jpeg") {
				$upload = wp_upload_bits("radio_".md5($_POST['id']).".jpg", null, file_get_contents($_FILES['encarte']['tmp_name']));
				$upload_resize_big = image_resize($upload['file'], 770, 770, true);
				$upload_resize = image_resize($upload['file'], 200, 200, true);
				if (!is_wp_error($upload_resize_big)) {
					$encarte_tmp_big = $upload_resize_big;
				}
				else {
					$encarte_tmp_big = $upload['file'];
				}
				$exp = explode("/", $encarte_tmp_big);
				foreach($exp as $key => $value) {
					if ($key >= (count($exp)-3)) {
						$encarte_big .= "/".$value;
					}
				}
				if (!is_wp_error($upload_resize)) {
					$encarte_tmp = $upload_resize;
				}
				else {
					$encarte_tmp = $upload['file'];
				}
				$exp = explode("/", $encarte_tmp);
				foreach($exp as $key => $value) {
					if ($key >= (count($exp)-3)) {
						$encarte .= "/".$value;
					}
				}
				$sql_encarte = ", encarte_big='".$encarte_big."', encarte='".$encarte."'";
			}
			$ids = "";
			$xml = new SimpleXMLElement("<playlist></playlist>");
			$xml->addAttribute('title', $_POST['titulo']);
			$xml->addAttribute('id_prog', $_POST['programa']);
			if ($sql_encarte) {
				$xml->addAttribute('encarte_big', $encarte_big);
				$xml->addAttribute('encarte', $encarte);
			}
			else {
				$xml->addAttribute('encarte_big', $_POST['encarte_big']);
				$xml->addAttribute('encarte', $_POST['encarte']);
			}
			for ($i = 0; $_POST['file'][$i]; $i++) {
				$wpdb->query("update $tbl_musicas set diretorio='".$_POST['diretorio']."', arquivo='".$_POST['file'][$i]."', artista='".$_POST['artist'][$i]."', titulo='".$_POST['title'][$i]."' where id='".$_POST['mp3_id'][$i]."'");
				$ids .= $_POST['mp3_id'][$i].",";
				$musica = $xml->addChild('music');
				$musica->addChild('id', $_POST['mp3_id'][$i]);
				$musica->addChild('artist', htmlentities($_POST['artist'][$i]));
				$musica->addChild('title', htmlentities($_POST['title'][$i]));
				$musica->addChild('directory', $_POST['diretorio']);
				$musica->addChild('file', $_POST['file'][$i]);
			}
			$wpdb->query("update $tbl_playlist set titulo='".$_POST['titulo']."', musicas='".substr($ids,0,-1)."', diretorio='".$_POST['diretorio']."', id_programa='".$_POST['programa']."'".$sql_encarte." where id='".$_POST['id']."'");
			$xml->asXML("../wp-content/radio/playlist_".$_POST['id'].".xml");
			?><script>location.href='/wp-admin/admin.php?page=radio/index.php';</script><?
		}
		else {
			$playlist = $wpdb->get_row("select id, diretorio, titulo, musicas, encarte_big, encarte, id_programa from $tbl_playlist where id='".$_GET['id']."'");
			?>
			<input type="hidden" name="action" value="update">
			<input type="hidden" name="id" value="<?=$playlist->id?>">
			<input type="hidden" name="diretorio" value="<?=$playlist->diretorio?>">
			<input type="hidden" name="encarte_big" value="<?=$playlist->encarte_big?>">
			<input type="hidden" name="encarte" value="<?=$playlist->encarte?>">
			<input type="text" name="titulo" placeholder="Nome da playlist" value="<?=$playlist->titulo?>"><br>
			Programa: <select name="programa">
				<?
				$programas = $wpdb->get_results("select id, titulo from $tbl_programas order by titulo asc");
			      foreach ($programas as $pl) {
			      ?>
			         <option value="<?=$pl->id?>"<? if ($pl->id == $playlist->id_programa) { ?> selected<? } ?>><?=$pl->titulo?></li>
			      <?
			      }		?>
				</select>
			
			<?
			$musicas = $wpdb->get_results("select id, artista, titulo, arquivo from $tbl_musicas where id in (".$playlist->musicas.") order by id asc");
			$i = 0;
			foreach ($musicas as $m) {
				?>
				<p><?=$m->arquivo?><br>
				<input type="hidden" name="file[<?=$i?>]" value="<?=$m->arquivo?>">
				<input type="hidden" name="mp3_id[<?=$i?>]" value="<?=$m->id?>">
				<input type="text" name="artist[<?=$i?>]" value="<?=$m->artista?>" placeholder="Artista">
				<input type="text" name="title[<?=$i?>]" value="<?=$m->titulo?>" placeholder="Titulo"></p>
				<?
				$i++;
			}
			?>
			<input type="file" name="encarte"><?
		}
	}
}
?>
<p class="submit"><input type="submit" name="Próximo" class="button-primary"></p>
</form>
</div>