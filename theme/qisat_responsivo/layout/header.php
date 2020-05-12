<?php
    global $CFG, $USER, $DB;

    $host = $_SERVER['HTTP_HOST'];
    $host_arr = explode(".",$host);

    foreach($host_arr as $h){
        if (strpos($h, 'crea-') !== false) {
            $crea = $h;
        }
    }

    if(isset($crea)){
        echo '<style>
                .header-brand-logo {
                    background-image: url("/theme/image.php?theme='.$CFG->theme.'&component=core&image=brand%2F'.$crea.'%2Fbrand_large") !important;
                    width:580px;
                }

                @media (max-width: 1060px) {
                    .header-brand-logo {
                        background-image: url("/theme/image.php?theme='.$CFG->theme.'&component=core&image=brand%2F'.$crea.'%2Fbrand_small") !important;
                    }
                }
              </style>';
    }

    $dominio_acesso_site = 'dominio_acesso_site';
    if ($DB->record_exists_select('config', 'name LIKE ?', array($dominio_acesso_site))){
        $dominio_acesso_site = $DB->get_record('config', array('name'=>$dominio_acesso_site), '*', MUST_EXIST)->value;
    } else {
        $dominio_acesso_site = 'site.qisat.com';
    }
?>
<header role="banner" class="navbar navbar-fixed-top moodle-has-zindex">
    <nav role="navigation" class="navbar-inner">
        <div class="container-fluid">
            <div class="header-brand">
                <a class="header-brand-logo" href="<?php echo $PAGE->theme->settings->url;?>"></a>
            </div>
<!--            <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">-->
<!--                <span class="icon-bar"></span>-->
<!--                <span class="icon-bar"></span>-->
<!--                <span class="icon-bar"></span>-->
<!--            </a>-->
            <div class="menu-item menu-mobile">
                <a data-toggle="collapse" data-target=".nav-collapse">
                    <div class="menu-mobile-icon"><i class="material-icons">menu</i></div><div class="menu-mobile-text">Menu</div>
                </a>
            </div>

            <div class="nav-collapse collapse">
                <ul class="nav pull-right">
                    <li><div class="menu-item-collapse-head">Menu</div></li>
                    <li><div class="menu-item-collapse"><a href="<?=$dominio_acesso_site?>">Home</a></div></li>
                    <li><div class="menu-item-collapse"><a href="<?=$dominio_acesso_site?>/cursos">Cursos</a></div></li>
                    <li><div class="menu-item-collapse"><a href="<?=$dominio_acesso_site?>/aluno">Área do Aluno</a></div></li>
                    <li><div class="menu-item-collapse"><a href="<?=$dominio_acesso_site?>/institucional">Institucional</a></div></li>
                    <li><div class="menu-item-collapse"><a href="<?=$dominio_acesso_site?>/carrinho">Carrinho</a></div></li>
                    <li><div class="menu-item-collapse-head"><?=fullname($USER)?></div></li>
                    <li><div class="menu-item-collapse"><a href="<?=$CFG->wwwroot . '/user/profile.php?id=' . $USER->id?>">Perfil</a></div></li>
                    <li><div class="menu-item-collapse"><a href="<?=$CFG->wwwroot . '/login/logout.php?sesskey=' . sesskey()?>">Sair</a></div></li>
                </ul>
            </div>

            <?php echo $OUTPUT->user_menu(); ?>
            <div class="menu-item"><a href="<?=$dominio_acesso_site?>/institucional">Institucional</a></div>
            <div class="menu-item"><a href="<?=$dominio_acesso_site?>/aluno">Área do Aluno</a></div>
            <div class="menu-item"><a href="<?=$dominio_acesso_site?>/cursos">Cursos</a></div>
            <div class="menu-item"><a href="<?=$dominio_acesso_site?>">Home</a></div>
        </div>
    </nav>

    <script>
        var idTimeout;
        function abrirPopup(link, nome, params){
            clearInterval(idTimeout);
            var popup = window.open(link, 'nome', params);
            popup.focus();
            idTimeout = setInterval(function(){
                if(popup.closed){
                    location.reload();
                    clearInterval(idTimeout);
                }
            }, 1000);
        }
    </script>

    <?php
        if(isset($sql))
            unset($sql);

        $dbman = $DB->get_manager();
        if ($dbman->table_exists('speedtest_users')) {
            if ($DB->record_exists_select('config', 'name LIKE ?', array('dominio_acesso_speedTest')))
                $speed_test = $DB->get_record('config', array('name'=>'dominio_acesso_speedTest'), '*', MUST_EXIST)->value;
            
            $sql = "SELECT token FROM {external_services_functions} esf 
                INNER JOIN {external_tokens} et ON et.externalserviceid = esf.externalserviceid 
                WHERE esf.functionname LIKE 'web_service_speedtest' ORDER BY et.id DESC";
            if ($DB->record_exists_sql($sql))
                $token = $DB->get_record_sql($sql)->token;
            
            $date = new DateTime();
            unset($sql);
            $sql = "SELECT 0 FROM {speedtest_users} WHERE mdl_user_id = ? GROUP BY timestamp HAVING MAX(timestamp) > ?";
        }
    ?>
    <?php if(!empty($speed_test) && isset($sql) && isset($token) && !$DB->record_exists_sql($sql, array($USER->id, $date->format('Y-m-d')))): ?>
        <script type="text/javascript" src="<?=$speed_test?>/dist/speedtest.js"></script>
        <script type="text/javascript">
            (function() {
                var SPEEDTEST_SERVERS=[
                    {	
                        name:"QiSat Speedtest Server", //user friendly name for the server
                        server:"<?=$speed_test?>", //URL to the server. // at the beginning will be replaced with http:// or https:// automatically
                        dlURL:"/backend/garbage.php",  //path to download test on this server (garbage.php or replacement)
                        ulURL:"/backend/empty.php",  //path to upload test on this server (empty.php or replacement)
                        pingURL:"/backend/empty.php",  //path to ping/jitter test on this server (empty.php or replacement)
                        getIpURL:"/backend/getIP.php"  //path to getIP on this server (getIP.php or replacement)
                    }
                ];
                var s=new Speedtest(); //INITIALIZE SPEEDTEST
                //CUSTOM SETTINGS HERE
                s.setParameter("user_id",<?= $USER->id ?>); //Id do usuario
                s.setParameter("telemetry_level","basic"); //enable telemetry
                s.setParameter("url_telemetry","<?=$CFG->wwwroot?>/webservice/rest/server.php?wstoken=<?=$token?>&wsfunction=web_service_speedtest"); 
                s.setSelectedServer(SPEEDTEST_SERVERS[0]);
                //END OF CUSTOM SETTINGS
                if(s.getState()==3){
                    s.abort(); //speedtest is running, abort
                }else{
                    s.start(); //test is not running, begin
                }
            })();
        </script>
    <?php endif; ?>

</header>