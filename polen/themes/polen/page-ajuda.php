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

  <?php wp_enqueue_script('polen-help'); ?>

	<main id="primary" class="site-main">
    <section id="faq" class="my-4">
      <div class="container">
        <div class="row">
          <div class="col-sm-12">
            <h2 class="text-center title">Perguntas Frequentes</h2>
          </div>
          <?php
            $faq = get_the_content();
            $faq = apply_filters( 'the_content', get_the_content() );
            $faq = str_replace('</p>', '', $faq);
            $faq = explode('<p>', $faq);
            //print_r($faq);
          ?>
          <div class="col-md-10 col-sm-12 my-3 mx-auto">
            <div class="panel-group" id="accordion" role="tablist" aria-multiselectable="false">
              <?php foreach ($faq as $key => $item) : ?>
                <?php
                  if ($key % 2 != 0) {
                ?>
                  <div class="panel panel-default">
                    <div class="panel-heading" role="tab" id="heading-<?php echo $key; ?>">
                      <h4 class="panel-title">
                        <a class="panel-button" role="button" data-toggle="collapse" data-parent="#accordion" href="#collapse-<?php echo $key; ?>" aria-expanded="false" aria-controls="collapse-<?php echo $key; ?>">
                          <?php echo $item; ?>
                        </a>
                      </h4>
                    </div>
                    <div id="collapse-<?php echo $key; ?>" class="panel-collapse collapse" role="tabpanel" aria-labelledby="heading-<?php echo $key; ?>">
                      <div class="panel-body">
                        <?php echo $faq[$key + 1]; ?>
                      </div>
                    </div>
                  </div>
                <?php
                  }
                ?>
              <?php endforeach; ?>
            </div>
          </div>
          <div class="col-sm-12 mt-5">
            <h2 class="text-center title">Ainda possui dúvidas?</h2>
          </div>
          <div class="col-sm-12 mt-1">
            <p class="text-center subtitle">Entre em contato conosco 😀</p>
          </div>
        </div>
      </div>
    </section>
    <section id="bus-form-wrapper" class="row mb-5 bus-form">
      <?php $inputs = new Material_Inputs(); ?>
      <div class="col-12 col-md-8 m-md-auto">
        <form id="help-form" v-on:submit.prevent="handleSubmit" method="POST">
          <?php
          $inputs->input_hidden("action", "submit_form");
          $inputs->input_hidden("form_id", "2");
          $inputs->input_hidden("terms", "1");

          $inputs->material_input(Material_Inputs::TYPE_TEXT, "name", "name", "Seu nome", true, "mb-3");
          $inputs->material_input(Material_Inputs::TYPE_EMAIL, "email", "email", "Seu melhor e-mail", true, "mb-3");
          $inputs->material_input(
            Material_Inputs::TYPE_PHONE,
            "phone",
            "phone",
            "Telefone de contato",
            false,
            "mb-3",
            array(
              "placeholder" => "(XX) XXXXX-XXXX",
              "v-model" => "phone",
              "v-on:keyup" => "handleChange",
              //"maxlength" => "15",
            )
          );
          $inputs->material_textarea("textarea1", "message", "Descreva o que você gostaria de saber", true); ?>
          <?php $inputs->material_button(Material_Inputs::TYPE_SUBMIT, "send_form", "Enviar", "mt-4"); ?>
        </form>
      </div>
    </section>
	</main><!-- #main -->

<?php
get_footer();
