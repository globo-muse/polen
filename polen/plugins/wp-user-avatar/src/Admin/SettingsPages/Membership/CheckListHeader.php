<?php

namespace ProfilePress\Core\Admin\SettingsPages\Membership;

use ProfilePress\Core\Membership\PaymentMethods\PaymentMethods as PaymentGateways;
use ProfilePress\Core\Membership\Repositories\PlanRepository;

if ( ! defined('ABSPATH')) {
    exit;
}

class CheckListHeader
{
    public function __construct()
    {
        add_action('ppress_settings_page_header', [$this, 'checklist_header']);
    }

    private function is_required_page_done()
    {
        $check = array_filter([
            ppress_settings_by_key('checkout_page_id', false, true),
            ppress_settings_by_key('payment_success_page_id', false, true),
            ppress_settings_by_key('payment_failure_page_id', false, true),
            ppress_settings_by_key('edit_user_profile_url', false, true)
        ]);

        return count($check) === 4;
    }

    private function is_business_info_done()
    {
        $check = array_filter([
            ppress_settings_by_key('business_name', false, true),
            ppress_settings_by_key('business_address', false, true),
            ppress_settings_by_key('business_city', false, true),
            ppress_settings_by_key('business_country', false, true),
            ppress_settings_by_key('business_state', false, true),
            ppress_settings_by_key('business_postal_code', false, true)
        ]);

        return count($check) === 6;
    }

    private function is_currency_set_done()
    {
        return ! empty(ppress_settings_by_key('payment_currency', false, true));
    }

    private function is_create_plan_done()
    {
        return ppress_is_any_active_plan();
    }

    private function is_payment_method_done()
    {
        return ppress_is_any_enabled_payment_method();
    }

    public function checklist_header()
    {
        if ( ! in_array(ppressGET_var('page'), [
            PPRESS_DASHBOARD_SETTINGS_SLUG,
            PPRESS_MEMBERSHIP_PLANS_SETTINGS_SLUG,
            PPRESS_MEMBERSHIP_ORDERS_SETTINGS_SLUG,
            PPRESS_MEMBERSHIP_CUSTOMERS_SETTINGS_SLUG,
            PPRESS_MEMBERSHIP_SUBSCRIPTIONS_SETTINGS_SLUG
        ])) {
            return;
        }

        if (
            $this->is_create_plan_done() &&
            $this->is_currency_set_done() &&
            $this->is_business_info_done() &&
            $this->is_required_page_done() &&
            $this->is_payment_method_done()) {
            return;
        }

        $required_pages_url = PPRESS_SETTINGS_SETTING_PAGE . '#global_pages?checkout_page_id_row';
        $payment_method_url = add_query_arg(['view' => 'payments', 'section' => 'payment-methods'], PPRESS_SETTINGS_SETTING_PAGE);
        $set_currency_url   = add_query_arg(['view' => 'payments'], PPRESS_SETTINGS_SETTING_PAGE);
        $business_info_url  = PPRESS_SETTINGS_SETTING_PAGE . '#business_info';
        $plan_url           = PPRESS_MEMBERSHIP_SUBSCRIPTION_PLANS_SETTINGS_PAGE;
        ?>
        <div class="ppress-checklist">
            <div>
                <h2><span><?php esc_html_e('Get started with Paid Memberships', 'wp-user-avatar') ?></span></h2>
                <p class="ppress-checklist-teaser"><?php esc_html_e('Follow the steps below to get your website ready for selling membership plans.', 'wp-user-avatar') ?></p>
            </div>

            <div class="ppress-checklist-dashboard-steps">
                <ul>
                    <li<?php echo $this->is_required_page_done() ? ' class="ppress-checklist-done"' : '' ?>>
                        <a href="<?= $required_pages_url ?>">
                            <svg version="1.1" viewBox="0 0 50 45" xmlns="http://www.w3.org/2000/svg">
                                <g fill="none" fill-rule="evenodd">
                                    <g class="svg-color" transform="translate(-298 -394)" fill="#97A3B4" fill-rule="nonzero">
                                        <g transform="translate(298 394)">
                                            <polygon id="a" points="36 41.5 29.429 41.5 29.429 38 19.571 38 19.571 41.5 13 41.5 13 45 36 45"></polygon>
                                            <path d="m48.333 0h-46.667c-0.92 0-1.6667 0.745-1.6667 1.6667v31.667c0 0.92167 0.74667 1.6667 1.6667 1.6667h46.667c0.92 0 1.6667-0.745 1.6667-1.6667v-31.667c0-0.92167-0.74667-1.6667-1.6667-1.6667zm-21.667 16.667v11.667h-3.3333v-11.667h-8.3333l10-10 10 10h-8.3333z"></path>
                                        </g>
                                    </g>
                                </g>
                            </svg>
                            <span><?php esc_html_e('Create the Required Pages', 'wp-user-avatar') ?></span>
                        </a>
                    </li>
                    <li<?php echo $this->is_payment_method_done() ? ' class="ppress-checklist-done"' : '' ?>>
                        <a href="<?= $payment_method_url ?>">
                            <svg version="1.1" viewBox="0 0 50 47" xmlns="http://www.w3.org/2000/svg">
                                <g fill-rule="evenodd">
                                    <g class="svg-color" transform="translate(-512 -392)" fill="#97A3B4" fill-rule="nonzero">
                                        <g transform="translate(512 392)">
                                            <rect x="6.25" y="26.562" width="14.062" height="3.125"></rect>
                                            <path id="a" d="m43.75 31.25h-1.5625v-3.125c0-3.5047-2.7453-6.25-6.25-6.25s-6.25 2.7453-6.25 6.25v3.125h-1.5625c-0.86406 0-1.5625 0.7-1.5625 1.5625v12.5c0 0.8625 0.69844 1.5625 1.5625 1.5625h15.625c0.86406 0 1.5625-0.7 1.5625-1.5625v-12.5c0-0.8625-0.69844-1.5625-1.5625-1.5625zm-4.6875 0h-6.25v-3.125c0-1.7812 1.3422-3.125 3.125-3.125s3.125 1.3438 3.125 3.125v3.125z"></path>
                                            <path d="m3.125 32.812v-14.062h43.75v9.375h3.125v-23.438c0-2.5844-2.1031-4.6875-4.6875-4.6875h-40.625c-2.5844 0-4.6875 2.1031-4.6875 4.6875v28.125c0 2.5844 2.1031 4.6875 4.6875 4.6875h18.75v-3.125h-18.75c-0.8625 0-1.5625-0.70156-1.5625-1.5625zm0-28.125c0-0.86094 0.7-1.5625 1.5625-1.5625h40.625c0.8625 0 1.5625 0.70156 1.5625 1.5625v4.6875h-43.75v-4.6875z"></path>
                                        </g>
                                    </g>
                                </g>
                            </svg>
                            <span><?php esc_html_e('Integrate a Payment Method', 'wp-user-avatar') ?></span>
                        </a>
                    </li>
                    <li<?php echo $this->is_business_info_done() ? ' class="ppress-checklist-done"' : '' ?>>
                        <a href="<?= $business_info_url ?>">
                            <svg version="1.1" viewBox="0 0 50 47" xmlns="http://www.w3.org/2000/svg">
                                <g fill="none" fill-rule="evenodd">
                                    <g class="svg-color" transform="translate(-719 -392)" fill="#97A3B4" fill-rule="nonzero">
                                        <g transform="translate(652 337)">
                                            <g transform="translate(67 55)">
                                                <path id="a" d="m41.406 25h-0.048438c-1.8807-0.030708-3.7278-0.50333-5.3922-1.3797-1.6887 0.90067-3.5721 1.3744-5.4859 1.3797-1.9108-0.021361-3.789-0.49695-5.4797-1.3875-3.4244 1.8503-7.5506 1.8503-10.975 0-1.6449 0.86813-3.4687 1.3431-5.3281 1.3875-0.82226-0.0027149-1.6421-0.089097-2.4469-0.25781v20.57c0 0.86294 0.69956 1.5625 1.5625 1.5625h12.5v-10.938h9.375v10.938h12.5c0.86294 0 1.5625-0.69956 1.5625-1.5625v-20.553c-0.77118 0.15874-1.5564 0.23936-2.3438 0.24062z"></path>
                                                <path d="m49.647 13.125-9.3344-12.5c-0.29508-0.39345-0.75819-0.625-1.25-0.625h-28.125c-0.49181 0-0.95492 0.23155-1.25 0.625l-9.3344 12.5c-0.23661 0.31804-0.34432 0.71373-0.30156 1.1078 0.48623 4.3646 4.185 7.6604 8.5766 7.6422 1.978-0.043087 3.8844-0.74842 5.4141-2.0031 1.5426 1.2968 3.4941 2.0063 5.5094 2.0031 1.992-0.023866 3.9154-0.731 5.4484-2.0031 1.5396 1.2746 3.4702 1.9817 5.4688 2.0031 2.0043 9.522e-4 3.9446-0.7063 5.4781-1.9969 1.5401 1.2666 3.4656 1.9709 5.4594 1.9969 4.3779 7.418e-4 8.0566-3.2897 8.5422-7.6406 0.043165-0.3946-0.064568-0.79093-0.30156-1.1094z"></path>
                                            </g>
                                        </g>
                                    </g>
                                </g>
                            </svg>
                            <span><?php esc_html_e('Add Your Business Information', 'wp-user-avatar') ?></span>
                        </a>
                    </li>
                    <li<?php echo $this->is_currency_set_done() ? ' class="ppress-checklist-done"' : '' ?>>
                        <a href="<?= $set_currency_url ?>">
                            <svg version="1.1" viewBox="0 0 50 50" xmlns="http://www.w3.org/2000/svg">
                                <g fill="none" fill-rule="evenodd">
                                    <g class="svg-color" transform="translate(-932 -389)" fill="#97A3B4" fill-rule="nonzero">
                                        <g transform="translate(866 337)">
                                            <g transform="translate(66 52)">
                                                <path id="Shape" d="m20.642 16.559c-0.84844 0.51562-1.2266 1.1875-1.2266 2.1781 0 2.0266 1.4875 3.0406 4.0219 3.9797v-6.9844c-1.0828 0.12344-2.0609 0.38125-2.7953 0.82656z"></path>
                                                <path id="a" d="m29.189 33.191c0.89062-0.6375 1.3062-1.5359 1.3062-2.8281 0-1.5781-1.2812-2.3828-3.9328-3.3062v7.075c1.0344-0.15938 1.9453-0.45312 2.6266-0.94062z"></path>
                                                <path d="m25 0c-13.784 0-25 11.216-25 25s11.216 25 25 25 25-11.216 25-25-11.216-25-25-25zm8.6203 30.364c0 2.2891-0.90312 4.1469-2.6125 5.3703-1.2109 0.86562-2.7578 1.3391-4.4453 1.5406v4.9125h-3.125v-4.85c-2.6078-0.14062-5.2938-0.71562-7.5109-1.4719l-1.4781-0.50469 1.0094-2.9578 1.4797 0.50469c1.9953 0.67969 4.3297 1.1844 6.5 1.3266v-8.1984c-3.4734-1.1531-7.1469-2.8344-7.1469-7.3016 0-2.0875 0.94375-3.7641 2.7297-4.8484 1.2344-0.74844 2.7828-1.1422 4.4172-1.2938v-4.7797h3.125v4.7609c2.8516 0.2 5.5531 0.92812 6.9703 1.6656l1.3875 0.72031-1.4406 2.7734-1.3875-0.72031c-1.1281-0.58594-3.2938-1.1266-5.5297-1.3062v8.05c3.3312 1.0672 7.0578 2.5438 7.0578 6.6078z"></path>
                                            </g>
                                        </g>
                                    </g>
                                </g>
                            </svg>
                            <span><?php esc_html_e('Set Your Membership Currency', 'wp-user-avatar') ?></span>
                        </a>
                    </li>
                    <li<?php echo $this->is_create_plan_done() ? ' class="ppress-checklist-done"' : '' ?>>
                        <a href="<?= $plan_url ?>">
                            <svg version="1.1" viewBox="0 0 50 47" xmlns="http://www.w3.org/2000/svg">
                                <g fill="none" fill-rule="evenodd">
                                    <g class="svg-color" transform="translate(-1146 -392)" fill="#97A3B4" fill-rule="nonzero">
                                        <g transform="translate(1080 337)">
                                            <g transform="translate(66 55)">
                                                <path id="Shape" d="m49.738 13.195-6.25-9.375c-0.23438-0.35312-0.60312-0.59531-1.0203-0.67031l-17.188-3.125c-0.017188-0.003125-0.034375 0-0.05-0.003125-0.076562-0.009375-0.15312-0.009375-0.22969-0.009375s-0.15312 0-0.22812 0.010938c-0.017188 0.003125-0.034375-0.0015625-0.05 0.003125l-17.188 3.125c-0.41875 0.073438-0.7875 0.31719-1.0219 0.66875l-6.25 9.375c-0.31875 0.47969-0.35 1.0953-0.078125 1.6031s0.80156 0.82656 1.3781 0.82656h17.188c0.59219 0 1.1328-0.33438 1.3984-0.86406l4.8516-9.7047 4.8516 9.7047c0.26562 0.52969 0.80625 0.86406 1.3984 0.86406h17.188c0.57656 0 1.1062-0.31719 1.3781-0.825s0.24062-1.125-0.078125-1.6047z"></path>
                                                <path id="a" d="m31.25 18.75c-1.7859 0-3.3906-0.99219-4.1922-2.5875l-0.49531-0.99219v31.148l16.272-7.3969c0.55781-0.25312 0.91562-0.80781 0.91562-1.4219v-18.75h-12.5z"></path>
                                                <path d="m23.438 15.17-0.49375 0.98906c-0.80312 1.5984-2.4078 2.5906-4.1938 2.5906h-12.5v18.75c0 0.61406 0.35781 1.1688 0.91562 1.4219l16.272 7.3969v-31.148z"></path>
                                            </g>
                                        </g>
                                    </g>
                                </g>
                            </svg>
                            <span><?php esc_html_e('Create a Membership Plan', 'wp-user-avatar') ?></span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
        <?php
    }

    public static function get_instance()
    {
        static $instance = null;

        if (is_null($instance)) {
            $instance = new self();
        }

        return $instance;
    }
}