<?php if ( ! is_user_logged_in()) : ?>

    <div class="ppress-main-checkout-form__login_form_wrap" style="display:none">


        <div class="ppress-main-checkout-form__block__item ppress-co-half">
            <label for="ppmb_user_login">
                <?php esc_html_e('Username or Email', 'wp-user-avatar') ?>
            </label>
            <input name="ppmb_user_login" id="ppmb_user_login" type="text">
        </div>
        <div class="ppress-main-checkout-form__block__item ppress-co-half">
            <label for="ppmb_user_pass"><?php esc_html_e('Password', 'wp-user-avatar') ?></label>
            <input id="ppmb_user_pass" name="ppmb_user_pass" type="password">
            <span class="ppress-main-checkout-form__login_form__lostp">
            <a class="ppress-checkout__link" href="<?php echo wp_lostpassword_url() ?>"><?php esc_html_e('Forgot your password?', 'wp-user-avatar') ?></a>
        </span>
        </div>
        <div class="ppress-main-checkout-form__block__item ppress-login-submit-btn">
            <input name="ppmb_login_submit" type="submit" value="<?php esc_html_e('Log in', 'wp-user-avatar') ?>">
            <p><?php esc_html_e('Or continue with your order below.', 'wp-user-avatar') ?></p>
        </div>
    </div>

<?php else : $user = wp_get_current_user(); ?>

    <div class="ppress-main-checkout-form__logged_in_text_wrap">
        <div class="ppress-main-checkout-form__block__item">
            <p>
                <?php
                /* Translators: %s display name. */
                printf(esc_html__('Logged in as %s. Not you?', 'wp-user-avatar'), esc_html($user->display_name));
                ?>
                <a href="<?php echo esc_url(wp_logout_url(ppress_plan_checkout_url($plan->id))); ?>">
                    <?php esc_html_e('log out', 'wp-user-avatar'); ?>
                </a>
            </p>
        </div>
    </div>

<?php endif; ?>
