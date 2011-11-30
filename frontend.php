<div id="radio">
  <div id="radio-player">
    <div id="radio-controls">
       <div id="radio-prev" class="btn">prev</div><div id="radio-play" class="radio-play">play</div><div id="radio-next" class="btn">next</div><div id="radio-like" class="btn like">like</div>
       <div id="radio-volume">
          <div id="radio-volume-current"></div>
          <div id="radio-volume-total"></div>
       </div>
    </div>
    <div id="radio-display">carregando...</div>
    <div id="radio-mm" class="max">+</div>
    <div id="radio-timer">
    <div id="radio-progresso">
      <div id="radio-progresso-playing"></div>
      <div id="radio-progresso-loading"></div>
    </div>
    <div id="radio-time">00:00</div>
    <div id="peak">
      <div id="peak-box">
        <span class="l"></span>
        <span class="r"></span>
      </div>
    </div>
    </div>
  </div>
  <div id="radio-more">
  	<hr>
  	<div id="radio-programas">
      <h2>Programas</h2>
      <ul>
      <?
      global $wpdb, $tbl_programas, $tbl_playlist;
      $programas = $wpdb->get_results("select id, titulo from $tbl_programas order by titulo asc");
      foreach ($programas as $pl) {
      ?>
         <li class="lista-playlist" rel="<?=$pl->id?>"><?=$pl->titulo?></li>
      <?
      } ?>
      </ul>
    </div>
    <div id="radio-playlist" class="radio-playlist">
      <h2>Playlists</h2>
      <ul>
      <?
      $playlists = $wpdb->get_results("select id, titulo from $tbl_playlist order by titulo asc");
      foreach ($playlists as $pl) {
      ?>
         <li class="lista-playlist" rel="<?=$pl->id?>"><?=$pl->titulo?></li>
      <?
      } ?>
      </ul>
    </div>
    <div id="radio-playlist-preview" class="radio-playlist"></div>
    <div id="radio-musicas-preview" class="radio-musicas"></div>
    <div id="radio-musicas" class="radio-musicas"></div>
  </div>
  <div id="sm2-container">
  <!-- SM2 flash goes here -->
  </div>
</div>