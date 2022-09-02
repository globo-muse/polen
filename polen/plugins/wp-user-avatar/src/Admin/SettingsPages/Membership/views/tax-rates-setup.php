<?php use ProfilePress\Core\Admin\SettingsPages\Membership\TaxSettings\SettingsPage;

$db_tax_rates = ppress_var(get_option(PPRESS_TAXES_OPTION_NAME, []), 'tax_rates', []);
?>
<style>#tax_rates_row .description {
        width: 100%
    }</style>
<div class="ppress-tax-rates-wrap">
    <input style="display:none" type="hidden" name="ppress_taxes[tax_rates]" value="">
    <table cellspacing="0" class="widefat">
        <thead>
        <tr>
            <th class="ppress-tax-rate-table-country"><?php esc_html_e('Country', 'wp-user-avatar') ?></th>
            <th class="ppress-tax-rate-table-state"><?php esc_html_e('State Code', 'wp-user-avatar') ?></th>
            <th class="ppress-tax-rate-table-country-wide"><?php esc_html_e('Country Wide', 'wp-user-avatar') ?></th>
            <th class="ppress-tax-rate-table-rate"><?php esc_html_e('Rate', 'wp-user-avatar') ?></th>
            <th class="ppress-tax-rate-table-actions"></th>
        </tr>
        </thead>
        <tbody>
        <?php if (empty($db_tax_rates)) : ?>
            <tr class="ppress-tax-rate-row--is-empty">
                <td style="background-color:#f9f9f9;" colspan="6">
                    <?php esc_html_e('No rates found.', 'wp-user-avatar') ?>
                </td>
            </tr>

        <?php else : $index = 0;

            foreach ($db_tax_rates as $tax_rate) {

                SettingsPage::tax_rate_row(
                    $index,
                    ppress_var($tax_rate, 'country', ''),
                    ppress_var($tax_rate, 'state', ''),
                    ppress_var($tax_rate, 'global', ''),
                    ppress_var($tax_rate, 'rate', '')
                );

                $index++;
            }

        endif; ?>
        </tbody>
        <tfoot>
        <tr>
            <td colspan="6">
                <a href="#" class="button button-secondary ppress_add_tax_rate">
                    <?php esc_html_e('Add Tax Rate', 'wp-user-avatar') ?>
                </a>
            </td>
        </tr>
        </tfoot>
    </table>
</div>