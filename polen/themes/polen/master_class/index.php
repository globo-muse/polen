<?php
/**
 * The template for displaying all pages
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages
 * and that other 'pages' on your WordPress site may use a
 * different template.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Polen
 */

get_header();
?>
<main id="primary" class="site-main">
    <?php mc_get_top_banner(); ?>
	<div class="row">
		<div class="col-12 col-md-8 m-md-auto mc-content">
            <div class="row">
                <div class="col-12 text-center mt-3">
                    <h3 class="title mb-4">O que é a Masterclass?</h3>
                    <p>Na masterclass online Beabá do Vinho, Ronnie Von vai compartilhar seu conhecimento e paixão por vinhos diretamente de sua adega particular. Criando um ambiente intimista para os fãs, que vão sentir que estão compartilhando uma taça com o ídolo.</p>
                </div>
                <div class="col-12 text-center mt-3">
                    <h3 class="title mb-4">Para quem é esse curso?</h3>
                    <ul>
                        <li>Apreciadores que querem aprender como harmonizar diferentes vinhos com diferentes pratos</li>
                        <li>Entusiastas que gostam de explorar vinhos de diferentes uvas e regiões</li>
                        <li>Iniciados que buscam aprofundar seus conhecimentos sobre vinhos</li>
                    </ul>
                </div>
                <div class="col-12 text-center mt-3">
                    <div class="row">
                        <div class="col-12">
                            <h3 class="title mb-4">Com quem você vai aprender?</h3>
                        </div>
                        <div class="col-12">
                            <div class="box-round book-info-wrapp py-3 px-3">
                                <div class="row">
                                    <div class="col-12 mb-3">
                                        <img class="img-responsive" src="<?php echo TEMPLATE_URI . '/assets/img/masterclass/ronnie.png'; ?>" alt="Ronnie Von"></img>
                                    </div>
                                    <div class="col-12">
                                        <p>
                                        Ronnie Von tem uma extensa carreira de sucesso como cantor, compositor, ator e apresentador. Grande apreciador de vinhos desde jovem, hoje também é enólogo formado e compartilha dicas sobre vinho em suas entrevistas e redes sociais.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 text-center mt-4">
                    <a href="#" class="btn gradient btn-lg">Quero ganhar desconto</a>
                </div>
                <div class="col-12 text-center mt-3">
                    <div class="row">
                        <div class="col-12">
                            <h3 class="title mb-4">Realização</h3>
                        </div>
                        <div class="col-12 d-flex justify-content-around">
                            <img class="img-responsive" src="<?php echo TEMPLATE_URI . '/assets/img/masterclass/polen-masterclass.png'; ?>" alt="Polen Masterclass"></img>
                            <img class="img-responsive" src="<?php echo TEMPLATE_URI . '/assets/img/masterclass/todo-vino.png'; ?>" alt="Todo Vino"></img>
                        </div>
                    </div>
                </div>
            </div>
		</div>
	</div>

</main><!-- #main -->

<?php
get_footer();
