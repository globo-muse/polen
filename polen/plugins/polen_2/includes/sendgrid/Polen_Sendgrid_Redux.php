<?php
namespace Polen\Includes\Sendgrid;

abstract class Polen_Sendgrid_Redux
{
    const APIKEY             = 'sendgrid_apikey';
    const THEME_ID_GALO_HELP = 'sendgrid_theme_galo_help';
    
    const THEME_ID_POLEN_TALENT_ACCEPTED     = 'sendgrid_theme_polen_accepted';
    const THEME_ID_POLEN_TALENT_REJECT       = 'sendgrid_theme_polen_reject';
    const THEME_ID_POLEN_PAYMENT_APPROVED    = 'sendgrid_theme_polen_payment_approved';
    const THEME_ID_POLEN_ORDER_COMPLETED     = 'sendgrid_theme_polen_order_completed';
    const THEME_ID_POLEN_B2B_FORM_TO_CLIENT  = 'sendgrid_theme_polen_b2b_form_to_client';
    const THEME_ID_POLEN_B2B_PAYMENT_APPROV  = 'sendgrid_theme_polen_b2b_payment_approved';
}
