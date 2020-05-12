<?php
/**
 * Created by PhpStorm.
 * User: leonardo
 * Date: 12/09/2016
 * Time: 11:57
 */
?>

<!--<header role="banner" class="navbar navbar-fixed-top--><?php //echo $html->navbarclass ?><!-- moodle-has-zindex">-->
<!--    <nav role="navigation" class="navbar-inner">-->
<!--        <div class="container-fluid">-->
<!--            <a class="brand" href="--><?php //echo $CFG->wwwroot;?><!--">--><?php //echo
//format_string($SITE->shortname, true, array('context' => context_course::instance(SITEID)));
//                ?><!--</a>-->
<!--<a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">-->
<!--    <span class="icon-bar"></span>-->
<!--    <span class="icon-bar"></span>-->
<!--    <span class="icon-bar"></span>-->
<!--</a>-->
<?php //echo $OUTPUT->user_menu(); ?>
<!--<div class="nav-collapse collapse">-->
<!--    --><?php //echo $OUTPUT->custom_menu(); ?>
<!--    <ul class="nav pull-right">-->
<!--        <li>--><?php //echo $OUTPUT->page_heading_menu(); ?><!--</li>-->
<!--    </ul>-->
<!--</div>-->
<!--</div>-->
<!--</nav>-->
<!--</header>-->

<header class="header-main ng-scope" ng-controller="headerCtrl">
    <div class="header-main__brand"> <a href="/" class="header-main__logo"> QiSat </a> </div>
    <nav class="header-main__nav">
        <ul class="header-main__ul">
            <li class="show-for-large-up header-main__list" data-linka="home"> <a class="header-main__item" href="http://newsite.qisat.com/">Home</a></li>
            <li class="show-for-large-up header-main__list  header-main__list__has--submenu" data-linka="cursos">
                <a class="header-main__item header-main__list-item-current" href="http://newsite.qisat.com/cursos">Cursos</a>
                <ul class="header-main__list__submenu">
                    <li class="header-main__list__submenu--item"><a class="header-main__list__submenu--item-link" href="http://newsite.qisat.com/course-ead.html">Curso EAD</a></li>
                    <li class="header-main__list__submenu--item"><a class="header-main__list__submenu--item-link" href="http://newsite.qisat.com/course-presential.html">Curso Presencial</a></li>
                </ul>
            </li>
            <li class="show-for-large-up header-main__list header-main__list-current" data-linka="aluno"> <a class="header-main__item" href="http://newsite.qisat.com/aluno">Área do Aluno</a></li>
            <li class="show-for-large-up header-main__list" data-linka="institucional"> <a class="header-main__item" href="http://newsite.qisat.com/institucional">Institucional</a></li>
            <li class="hide-for-large-up header-main__list"> <a class="right-off-canvas-toggle header-main__item" href="#"> <i class="material-icons">menu</i> Menu</a></li>
            <li class="show-for-large-up header-main__list" data-linka="user">
                <?php echo $OUTPUT->user_menu(); ?>
                <div class="nav-collapse collapse">
                    <?php echo $OUTPUT->custom_menu(); ?>
                    <ul class="nav pull-right">
                        <li><?php echo $OUTPUT->page_heading_menu(); ?></li>
                    </ul>
                </div>
            </li>
            <li class="header-main__list header-main__list-cart"> <a class="header-main__item header-main__item-cart" href="http://newsite.qisat.com/carrinho"><i class="material-icons">shopping_cart</i></a></li>
        </ul>
    </nav>
</header>