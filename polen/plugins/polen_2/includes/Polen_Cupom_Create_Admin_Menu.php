<?php

namespace Polen\Includes;

use \Polen\Admin\Polen_Admin;

class Polen_Cupom_Create_Admin_Menu
{
    public function __construct($static = false)
    {
        if ($static) {
            add_action('admin_menu', [$this, 'create_menu']);
        }
    }

    public function create_menu()
    {
        add_submenu_page('woocommerce-marketing', 'Cupons em Lote', 'Cupons em Lote', 'manage_options', 'batch-coupon', [$this, 'coupon_layout'], 2);
    }

    public function coupon_layout()
    {
        wp_enqueue_style('woocommerce_admin_styles');
        wp_enqueue_script('batch-coupon', Polen_Admin::get_js_url('coupon.js'), array('jquery', 'select2', 'wc-enhanced-select', 'vuejs'), DEVELOPER ?  time() : "1.0.0", false);
?>
        <h1>Cupons em Lote</h1>
        <div id="batch-coupon" class="container batch-coupon mt-4">
            <form id="form-coupon" v-on:submit.prevent="createCoupon">
                <input type="hidden" name="action" value="polen_create_cupom" />
                <div class="row mb-2">
                    <div class="col">
                        <label for="prefix_name" class="bc-label">Prefixo</label>
                        <input type="text" id="prefix_name" v-model="prefix_name" placeholder="prefixo" required />
                    </div>
                    <div class="col ml-2">
                        <label for="discount_type" class="bc-label">Tipo de Desconto</label>
                        <select v-model="discount_type" v-on:change="handleChangeSelect" id="discount_type" required>
                            <option disabled value="">Escolha um tipo</option>
                            <option v-for="option in distount_type_list" v-bind:value="option.TYPE">{{option.NAME}}</option>
                        </select>
                    </div>
                </div>
                <div class="row mb-2" v-bind:class="discount_type.indexOf('product') > -1 ? 'show': 'hidden'">
                    <div class="col">
                        <label><?php _e('Products', 'woocommerce'); ?></label>
                        <select class="wc-product-search" multiple="multiple" style="width: 100%;" name="product_ids[]" data-placeholder="<?php esc_attr_e('Search for a product&hellip;', 'woocommerce'); ?>" data-action="woocommerce_json_search_products_and_variations">
                        </select>
                    </div>
                </div>
                <div class="row mb-2">
                    <div class="col">
                        <label for="amount" class="bc-label">Valor do Cupom<span v-if="symbol" class="ml-1">{{symbol}}</span></label>
                        <input type="number" id="amount" v-model="amount" placeholder="valor" required />
                    </div>
                </div>
                <div class="row mb-2">
                    <div class="col">
                        <label for="description" class="bc-label">Descrição</label>
                        <textarea cols="30" rows="10" id="description" v-model="description" placeholder="descrição" required></textarea>
                    </div>
                </div>
                <div class="row mb-2">
                    <div class="col">
                        <label for="expiry_date" class="bc-label">Data de expiração</label>
                        <input type="text" id="expiry_date" v-on:keyup="validateDate" v-model="expiry_date" placeholder="01/11/2021" maxlength="10" required />
                    </div>
                    <div class="col ml-2">
                        <label for="usage_limit" class="bc-label">Limite de uso</label>
                        <input type="text" id="usage_limit" v-model="usage_limit" placeholder="limite" required />
                    </div>
                </div>
                <div class="row">
                    <div class="col">
                        <button type="submit" class="btn-send" :disabled="loading">{{message}}</button>
                    </div>
                </div>
            </form>
        </div>
<?php
    }
}
