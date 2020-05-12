<?php
/**
 * Created by PhpStorm.
 * User: leonardo
 * Date: 12/09/2016
 * Time: 10:19
 */
?>

<footer class="footer-main">

	<div class="footer-primary">
		<div class="row">

					<ul class="footer-primary__columns small-block-grid-1 medium-block-grid-3 large-block-grid-6">

                    <?php
                        $numberofcolumns = 5;
                        for ($i = 1; $i <= $numberofcolumns; $i++) {
                            if ($PAGE->theme->settings->{'column' . $i . 'visible'}) {
                                echo '<li class="footer-primary__columns--item">
						 		        <ul class="footer-primary__list">
									        <li class="footer-primary__title">'. $PAGE->theme->settings->{'column' . $i . 'title'} .'</li>';

                                $links = $PAGE->theme->settings->{'column' . $i . 'links'};
                                $links_arr = explode("\n",$links);

                                foreach ($links_arr as $link){
                                    $link_arr = explode("||", $link);
                                    echo '		<li class="footer-primary__list__item"><a class="footer-primary__list__item--link" href="' . $link_arr[1] . '" title="' . $link_arr[0] . '">' . $link_arr[2] . '</a></li>';
                                }

								echo '	</ul>
						              </li>';
                            }
                        }
                    ?>

						<li class="footer-primary__columns--item">
							<img class="footer-primary__list__item--brand-qisat" src="<?= $OUTPUT->pix_url('brand/brand_small-light', 'theme') ?>" alt="Brand QiSat Light">
						</li> <!-- //  -->

		</ul></div> <!-- ///row -->
	</div> <!-- // footer-primary -->

	<div class="footer-secondary">
			<div class="row">
					<ul class="footer-secundary__columns small-block-grid-1   large-block-grid-3">
						<li>
								<ul class="footer-secondary__list">
									<li class="footer-secondary__list__item"><a href="<?= $PAGE->theme->settings->termouso?>" class="footer-secondary__list__item--link">Termos de uso</a></li>
									<li class="footer-secondary__list__item"><a href="<?= $PAGE->theme->settings->politica?>" class="footer-secondary__list__item--link">Política de privacidade</a></li>
								</ul>
					    </li>

					    <li>
								<ul class="footer-secondary__list footer-secondary__list--email ">
									<li class="footer-secondary__list__item"><a href="mailto:inscricoes@qisat.com.br" class="footer-secondary__list__item--link">QiSat | Cursos aplicados à engenharia e Arquitetura : <strong>inscricoes@qisat.com.br</strong></a></li>
								</ul>
					   	</li>

						<li>
							<ul class="footer-secondary__list">
									<li class="footer-secondary__list__item">
										<span class="footer-secondary__list__item--brand-text">Uma empresa</span>
										<a href="#" class="footer-secondary__list__item--brand">
											<img src="<?= $OUTPUT->pix_url('brand/brand_altoqi-small-light', 'theme') ?>" alt="AltoQi"></a>
									</li>
							 </ul>
						</li>
	 		</ul></div> <!-- ///row -->
	</div> <!-- footer-secondary -->
</footer>