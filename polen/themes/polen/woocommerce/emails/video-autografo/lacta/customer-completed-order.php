<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

//do_action( 'woocommerce_email_header', $email_heading, $email );
?>

<!DOCTYPE html>
<html lang="pt-BR">
  <head>
      <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
      <title>Polen.me</title>
  </head>
  <body leftmargin="0" marginwidth="0" topmargin="0" marginheight="0" offset="0"
    style="padding: 0; -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale;">
    <div id="wrapper" dir="ltr"
      style="background-color: #fff; margin: 0; padding: 70px 0; width: 100%; -webkit-text-size-adjust: none;">
      <table border="0" cellpadding="0" cellspacing="0" height="100%" width="100%">
        <tr>
          <td align="center" valign="top">
            <table border="0" cellpadding="0" cellspacing="0" width="600" id="template_container"
              style="background: #0033A0; overflow: hidden; box-shadow: 0 1px 4px rgba(0, 0, 0, 0.1); border-radius: 8px 8px 0px 0px;">
              <tr>
                <td align="center" valign="top">
                  <!-- Header -->
                  <table border="0" cellpadding="0" cellspacing="0" width="100%" id="template_header"
                    style='color: #ffffff; border-bottom: 0; font-weight: bold; line-height: 100%; vertical-align: middle; font-family: Poppins, "Helvetica Neue", Helvetica, Arial, sans-serif;'>
                    <tr>
                      <td align="center" id="header_wrapper"
                        style="display: flex; justify-content: center; flex-wrap: wrap; align-items: center; padding: 20px 0px;">
                        <img src="<?php echo get_template_directory_uri().'/assets/img/lacta/email/logo.png'?>" style="height: 62px; margin: 0 auto; display: block;"></img>
                      </td>
                      <td align="center" id="header_wrapper"
                        style="display: flex; justify-content: center;border-bottom: 1px solid rgba(255, 255, 255, 0.2); flex-wrap: wrap; align-items: center; padding: 20px 0px;">
                        <h2 style="width: 100%; text-align: center; color: #fff;margin:20px 0px; font-size: 32px;">Seu vídeo chegou!</h2>
                      </td>
                    </tr>
                  </table>
                  <!-- End Header -->
                </td>
              </tr>
              <tr>
                <td align="center" valign="top">
                  <!-- Body -->
                  <table border="0" cellpadding="0" cellspacing="0" width="600" id="template_body">
                    <tr>
                      <td valign="top" id="body_content" style="background-color: #0033A0;">
                        <!-- Content -->
                        <table border="0" cellpadding="20" cellspacing="0" width="100%">
                          <tr>
                            <td align="center" valign="top">
                              <div id="body_content_inner"
                                style='color: #ffffff; font-family: Roboto, "Helvetica Neue", Helvetica, Arial, sans-serif; font-size: 14px; line-height: 100%; text-align: left;'>
                                <p
                                  style="font-size: 16px; line-height: 2; font-weight: 400; color: #ffffff; margin:0px; text-align: center;">
                                  Seu vídeo já está disponível! Clique no botão abaixo para<br>visualizar o vídeo e se
                                  emocionar!
                                </p>
                              </div>
                            </td>
                          </tr>
                          <tr
                            style='color: #ffffff; font-family: Poppins, "Helvetica Neue", Helvetica, Arial, sans-serif; font-size: 14px; line-height: 100%; text-align: left;'>
                            <td align="center">
                              <a href="<?php echo polen_get_link_order_status( $order->get_id() ); ?>" target="_blank"
                                style="font-size: 16px;color: #fff; font-weight: 600; text-decoration: none; border: 1px solid #fff; padding: 15px 40px; border-radius: 10px;margin-bottom: 30px;">Visualizar
                                vídeo</a>
                            </td>
                          </tr>
                          <tr style="background-color: #262626;">
                            <td valign="top" style="padding: 0; border-radius: 6px;">
                              <table border="0" cellpadding="10" cellspacing="0" width="100%">
                                <tr>
                                  <td colspan="2" valign="middle" id="credit"
                                    style='border-radius: 6px; border: 0; color: #ffffff; font-family: Poppins, "Helvetica Neue", Helvetica, Arial, sans-serif; font-size: 12px; line-height: 150%; text-align: center; padding: 24px 0;'>
                                    <p
                                      style="font-size: 16px; line-height: 2; font-weight: 400; color: #ffffff; margin: 0 0 10px; text-align: center;">
                                      Em caso de dúvida, consulte nossa página de ajuda:<br><a
                                        href="https://polen.me/ajuda/" target="_blank" style="color: #fff">polen.me/ajuda</a>
                                    </p>
                                    <img src="<?php echo get_template_directory_uri().'/assets/img/logo.png'?>" style="height: 50px;"></img>
                                  </td>
                              </table>
                            </td>
                          </tr>
                        </table>
                        <!-- End Content -->
                      </td>
                    </tr>
                  </table>
                  <!-- End Body -->
                </td>
              </tr>
            </table>
          </td>
        </tr>
      </table>
    </div>
  </body>
</html>
