<?php
use Polen\Includes\Polen_Talent;

function pol_get_menu()
{
  global $Polen_Plugin_Settings;
?>
  <div class="col-4 col-sm-6 d-flex justify-content-end align-items-center">
    <?php $Polen_Plugin_Settings['search_bar'] && polen_get_search_form(); ?>
    <a href="https://polen.me/" class="bus-menu-item">Para empresas</a>
    <div class="ml-3">
      <div class="dropdown">
        <?php
        if (is_user_logged_in()) {
          $user_name = wp_get_current_user();
        ?>
          <a class="dropbtn">
            <div class="d-none d-md-flex menu-user-data">
              <div class="user-avatar d-flex flex-wrap align-items-center justify-content-center">
                <?php echo polen_get_avatar(get_current_user_id(), "polen-square-crop-lg"); ?>
              </div>
              <span class="text"><?php Icon_Class::polen_icon_chevron_down(); ?></span>
            </div>
            <div class="d-block d-md-none">
              <?php pol_menu_icon(); ?>
            </div>
          </a>
          <div id="menu-bg" class="menu-bg"></div>
          <div class="dropdown-content background text">
            <div class="row mb-2 d-md-none">
              <div class="col-12 mx-2">
                <div class="user-avatar d-flex flex-wrap align-items-center justify-content-center mb-2">
                  <?php echo polen_get_avatar(get_current_user_id(), "polen-square-crop-lg"); ?>
                </div>
                <p class="user-name"><?php echo $user_name->display_name; ?></p>
              </div>
              <a class="menu-close"><?php Icon_Class::polen_icon_close(); ?></a>
            </div>
            <div class="row">
              <div class="col-12 mx-2">
                <?php if (Polen_Talent::static_is_user_talent(wp_get_current_user())) : ?>
                  <a href="<?php echo get_permalink(get_option('woocommerce_myaccount_page_id')); ?>">Dashboard</a>
                <?php endif; ?>
                <a href="<?php echo esc_url(wc_get_account_endpoint_url('orders')); ?>">Meus pedidos</a>
                <?php /* <a href="<?php echo esc_url(wc_get_account_endpoint_url('payment-options')); ?>">Pagamentos</a> */ ?>
                <?php if (!Polen_Talent::static_is_user_talent(wp_get_current_user())) : ?>
                  <a href="<?php echo esc_url(wc_customer_edit_account_url()); ?>">Meus dados</a>
                <?php endif; ?>
                <a href="<?php echo esc_url(wp_logout_url()); ?>">Sair</a>
              </div>
            </div>
            <?php pol_menu_extras(true); ?>
          </div>
        <?php
        } else { ?>
          <div class="d-none d-md-block">
            <a class="btn btn-outline-light" href="<?php echo polen_get_login_url(); ?>">
              Entrar
            </a>
          </div>
          <div class="d-md-none">
            <a class="dropbtn">
              <?php pol_menu_icon(); ?>
            </a>
            <div id="menu-bg" class="menu-bg"></div>
            <div class="dropdown-content background text">
              <?php pol_menu_extras(); ?>
              <a href="<?php echo polen_get_login_url(); ?>" class="btn btn-outline-dark btn-lg btn-block mt-4">Entrar</a>
              <a class="menu-close"><?php Icon_Class::polen_icon_close(); ?></a>
            </div>
          </div>
        <?php
        }
        ?>
      </div>
    </div>
  </div>
<?php
}

function pol_menu_icon()
{
?>
  <svg id="menu-icon" class="menu-icon" width="56" height="48" viewBox="0 0 56 48" fill="none" xmlns="http://www.w3.org/2000/svg">
    <path d="M16 16H40" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
    <path d="M16 24H40" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
    <path d="M16 32H40" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
  </svg>

<?php
}

function pol_menu_extras($logged = false)
{
?>
  <div class="row d-block d-md-none">
    <?php if ($logged) : ?>
      <div class="col-12">
        <hr class="divisor" />
      </div>
    <?php endif; ?>
    <div class="col-12 mx-2">
      <a href="https://polen.me/">Para empresas</a>
    </div>
  </div>
<?php
}
