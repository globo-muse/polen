<?php

namespace ProfilePress\Core\Membership\Emails;

trait EmailDataTrait
{
    public function get_order_placeholders()
    {
        return [
            '{{email}}'                => esc_html__('Email address of the customer.', 'wp-user-avatar'),
            '{{first_name}}'           => esc_html__('First name of the customer.', 'wp-user-avatar'),
            '{{last_name}}'            => esc_html__('Last name of the customer.', 'wp-user-avatar'),
            '{{billing_address}}'      => esc_html__("Customer's billing address.", 'wp-user-avatar'),
            '{{billing_phone}}'        => esc_html__("Customer's phone number.", 'wp-user-avatar'),
            '{{customer_tax_id}}'      => esc_html__("Customer's Tax ID.", 'wp-user-avatar'),
            '{{order_id}}'             => esc_html__("Order ID.", 'wp-user-avatar'),
            '{{order_url}}'            => esc_html__("URL to view order.", 'wp-user-avatar'),
            '{{plan_name}}'            => esc_html__("Name or title of plan ordered.", 'wp-user-avatar'),
            '{{order_subtotal}}'       => esc_html__("Order price before taxes.", 'wp-user-avatar'),
            '{{order_tax}}'            => esc_html__("The taxed amount of the order.", 'wp-user-avatar'),
            '{{order_total}}'          => esc_html__("Total order amount that was paid.", 'wp-user-avatar'),
            '{{order_date}}'           => esc_html__("Order date.", 'wp-user-avatar'),
            '{{order_payment_method}}' => esc_html__("Order payment method.", 'wp-user-avatar'),
            '{{purchase_note}}'        => esc_html__("The purchase note of ordered plan.", 'wp-user-avatar'),
            '{{site_title}}'           => esc_html__('Name or title of this website.', 'wp-user-avatar'),
            '{{business_name}}'        => sprintf(
                esc_html__('Your business name as defined in %sSettings%s.', 'wp-user-avatar'),
                '<a target="_blank" href="' . PPRESS_SETTINGS_SETTING_PAGE . '#business_info">', '</a>'
            ),
            '{{business_address}}'     => sprintf(
                esc_html__('Your business address as defined in %sSettings%s.', 'wp-user-avatar'),
                '<a target="_blank" href="' . PPRESS_SETTINGS_SETTING_PAGE . '#business_info">', '</a>'
            ),
            '{{business_tax_id}}'      => sprintf(
                esc_html__('Your business Tax ID as defined in %sSettings%s.', 'wp-user-avatar'),
                '<a target="_blank" href="' . PPRESS_SETTINGS_SETTING_PAGE . '#business_info">', '</a>'
            ),
            '{{password_reset_link}}'  => esc_html__('URL to reset password.', 'wp-user-avatar'),
            '{{login_link}}'           => esc_html__('URL to login.', 'wp-user-avatar'),
        ];
    }

    public function get_subscription_placeholders()
    {
        return [
            '{{email}}'                  => esc_html__('Email address of the customer.', 'wp-user-avatar'),
            '{{first_name}}'             => esc_html__('First name of the customer.', 'wp-user-avatar'),
            '{{last_name}}'              => esc_html__('Last name of the customer.', 'wp-user-avatar'),
            '{{subscription_id}}'        => esc_html__("Subscription ID.", 'wp-user-avatar'),
            '{{subscription_url}}'       => esc_html__("URL to view subscription.", 'wp-user-avatar'),
            '{{renew_subscription_url}}' => esc_html__("URL to re-subscribe to the subscription plan.", 'wp-user-avatar'),
            '{{plan_name}}'              => esc_html__("Name or title of subscription plan.", 'wp-user-avatar'),
            '{{amount}}'                 => esc_html__("The recurring amount of the subscription.", 'wp-user-avatar'),
            '{{expiration_date}}'        => esc_html__("The expiration or renewal date for the subscription.", 'wp-user-avatar'),
            '{{site_title}}'             => esc_html__('Name or title of this website.', 'wp-user-avatar')
        ];
    }

    public function get_order_receipt_content($renewal = false)
    {
        ob_start();
        ?>
        <p>Hi {{first_name}},</p>
        <?php if ( ! $renewal) : ?>
        <p>Your order has successfully been processed.</p>
    <?php else : ?>
        <p>Your renewal order in the amount of {{order_total}} for {{plan_name}} has been successfully processed.</p>
    <?php endif; ?>
        <table style="table-layout: fixed; width: 100%; font-size: 15px; box-sizing: border-box!important; padding: 0;" width="100%" cellspacing="0" cellpadding="0">
            <thead style="box-sizing: border-box!important;">
            <tr>
                <td style="border-top-width: 1px; border-top-color: #edeff2; border-bottom-width: 1px; border-bottom-color: #edeff2; box-sizing: border-box!important; word-break: break-word; margin: 0; padding: 15px 0; border-style: dotted none solid;" colspan="2" valign="top">
                    <h1 style="box-sizing: border-box!important; margin-top: 0; margin-bottom: 10px; font-size: 20px; font-weight: bold;" align="left">Invoice</h1>
                    <span style="box-sizing: border-box!important;">Order: {{order_id}}</span><br/><span style="box-sizing: border-box!important;">Date: {{order_date}}</span>
                </td>
            </tr>
            </thead>
            <tbody style="box-sizing: border-box!important;">
            <tr>
                <td valign="top" style="font-size: 16px; word-break: break-word; margin: 0; padding: 0 5px 15px 0; border: none; box-sizing: border-box;">
                    <p align="left" style="display: block; font-size: 12px; font-weight: bold; line-height: 1.5em; margin: 15px 0 0; box-sizing: border-box;">Billed from</p>
                    <p align="left" style="font-size: 15px; line-height: 1.5em; margin: 5px 0 0; box-sizing: border-box;">{{business_name}}</p>
                    <p style="font-style: normal; font-size: 13px; line-height: 16px;margin: 5px 0 0; box-sizing: border-box;">{{business_address}}</p>
                    <p align="left" style="font-size: 15px; line-height: 1.5em; margin: 5px 0 0; box-sizing: border-box;">Tax ID: {{business_tax_id}}</p>
                </td>
                <td style="box-sizing: border-box!important; word-break: break-word; margin: 0; padding: 0 0 15px; border: none;" valign="top">
                    <p style="display: block; font-size: 12px; font-weight: bold; box-sizing: border-box!important; line-height: 1.5em; margin: 15px 0 0;" align="left">Billed to</p>
                    <p align="left" style="font-size: 15px; line-height: 1.5em; margin: 5px 0 0; box-sizing: border-box;">{{first_name}} {{last_name}}</p>
                    <p style="font-style: normal; font-size: 13px; line-height: 16px;margin: 5px 0 0; box-sizing: border-box;">{{billing_address}}</p>
                    <p align="left" style="font-size: 15px; line-height: 1.5em; margin: 5px 0 0; box-sizing: border-box;">Tax ID: {{customer_tax_id}}</p>
                </td>
            </tr>
            </tbody>
        </table>
        <table style="width: 100%; font-size: 15px; box-sizing: border-box!important; margin: 0; padding: 25px 0 0;" width="100%" cellspacing="0" cellpadding="0">
            <tbody>
            <tr>
                <td style="box-sizing: border-box!important; word-break: break-word; margin: 0; padding: 0; border: none;" colspan="2">
                    <table style="width: 100%; box-sizing: border-box!important; margin: 0; padding: 0;" width="100%" cellspacing="0" cellpadding="0">
                        <tbody>
                        <tr>
                            <th style="border-bottom-width: 1px; border-bottom-color: #edeff2; border-bottom-style: solid; box-sizing: border-box!important; padding: 0 0 8px;">
                                <p style="font-size: 12px; font-weight: bold; box-sizing: border-box!important; line-height: 1.5em; margin: 15px 0 0;" align="left">Plan</p>
                            </th>
                            <th style="border-bottom-width: 1px; border-bottom-color: #edeff2; border-bottom-style: solid; box-sizing: border-box!important; padding: 0 0 8px;">
                                <p style="font-size: 12px; font-weight: bold; box-sizing: border-box!important; line-height: 1.5em; margin: 15px 0 0;" align="right">Amount</p>
                            </th>
                        </tr>
                        <tr>
                            <td style="font-size: 15px; line-height: 18px; box-sizing: border-box!important; word-break: break-word; margin: 0; padding: 10px 0; border: none;" width="75%">{{plan_name}}</td>
                            <td style="box-sizing: border-box!important; word-break: break-word; margin: 0; padding: 0; border: none;" align="right" width="25%">{{order_total}}</td>
                        </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
            </tbody>
        </table>
        <p>{{purchase_note}}</p>
        <div style="margin:30px 0 0;padding: 10px 0 50px 0; text-align: center;">
            <a style="background: #555555; color: #fff; padding: 12px 30px; text-decoration: none; border-radius: 3px; letter-spacing: 0.3px;" href="{{order_url}}">View order details</a>
        </div>
        <?php
        return ob_get_clean();
    }

    public function get_subscription_cancelled_content()
    {
        ob_start();
        ?>
        <p>Hi {{first_name}},</p>
        <p>Your subscription for {{plan_name}} has been cancelled.</p>
        <?php
        return ob_get_clean();
    }

    public function get_subscription_completed_content()
    {
        ob_start();
        ?>
        <p>Hi {{first_name}},</p>
        <p>Your subscription for {{plan_name}} is now complete.</p>
        <?php
        return ob_get_clean();
    }

    public function get_subscription_expired_content()
    {
        ob_start();
        ?>
        <p>Hi {{first_name}},</p>
        <p>Your subscription for {{plan_name}} has now expired. If you'd like to renew it, please click below to subscribe again.</p>
        <div style="margin:30px 0 0;padding: 10px 0 50px 0; text-align: center;">
            <a style="background: #555555; color: #fff; padding: 12px 30px; text-decoration: none; border-radius: 3px; letter-spacing: 0.3px;" href="{{renew_subscription_url}}">Renew your subscription</a>
        </div>
        <?php
        return ob_get_clean();
    }

    public function get_subscription_renewal_reminder_content($expiration = false)
    {
        ob_start();
        ?>
        <p>Hi {{first_name}},</p>
        <?php if ( ! $expiration) : ?>
        <p>This is to inform you that your subscription for {{plan_name}} will renew on {{expiration_date}}.</p>
    <?php
    else : ?>
        <p>This is to inform you that your subscription for {{plan_name}} will expire on {{expiration_date}}.</p>
    <?php endif; ?>
        <div style="margin:30px 0 0;padding: 10px 0 50px 0; text-align: center;">
            <a style="background: #555555; color: #fff; padding: 12px 30px; text-decoration: none; border-radius: 3px; letter-spacing: 0.3px;" href="{{subscription_url}}">View subscription</a>
        </div>
        <?php
        return ob_get_clean();
    }

    public function get_new_order_admin_notification_content()
    {
        ob_start();
        ?>
        <h1>New Order</h1>
        <p>{{first_name}} ({{email}}) purchased {{plan_name}} for {{order_total}}.</p>
        <div style="margin:30px 0 0;padding: 10px 0 50px 0; text-align: center;">
            <a style="background: #555555; color: #fff; padding: 12px 30px; text-decoration: none; border-radius: 3px; letter-spacing: 0.3px;" href="{{order_url}}">View order details</a>
        </div>
        <?php
        return ob_get_clean();
    }
}