<?php

if ( ! defined( 'WPINC' ) ) {
    die;
}

use Polen\Includes\Polen_Update_Fields;
use Polen\Includes\Polen_Bancos;
?>
<div class="metaboxSellerData" id="metaboxSellerData">
    <h2>Dados da Instituição de Caridade</h2>
    <?php
    $current_screen = get_current_screen();
    $Polen_Update_Fields = new Polen_Update_Fields();
    $vendorInfo = $Polen_Update_Fields->get_vendor_data( intval( $_REQUEST['user_id'] ?? null ) );
    ?>
    <table class="form-table">
        <tbody>
            <td>
                <div id="PolenVendorTabs" class="PolenVendorTabs">
                    <ul>
                        <li><a href="#PolenVendorProfileTab1">Dados da Instituição</a></li>
                        <li><a href="#PolenVendorProfileTab2">Informações de Contato</a></li>
                        <?php if( is_admin() && ( $current_screen->base == 'user-edit' || ( $current_screen->base == 'user' && $current_screen->action == 'add' ) ) ) { ?>
                        <li><a href="#PolenVendorProfileTab5">Dados Bancários</a></li>
                        <li><a href="#PolenVendorProfileTab6">Configurações Financeiras</a></li>
                        <?php } ?>
                    </ul>

                    <!-- Dados do Talento -->
                    <div id="PolenVendorProfileTab1">
                        <table class="form-table">
                            <tr>
                                <th>
                                    Natureza Jurídica
                                </th>
                                <td>
                                    <select name="natureza_juridica" id="polen_natureza_juridica" class="widefat">
                                        <option value="PJ" <?php echo ( isset( $vendorInfo->natureza_juridica ) && $vendorInfo->natureza_juridica == 'PJ' ) ? ' selected' : ''; ?>>Pessoa Jurídica</option>
                                        <option value="PF" <?php echo ( isset( $vendorInfo->natureza_juridica ) && $vendorInfo->natureza_juridica == 'PF' ) ? ' selected' : ''; ?>>Pessoa Física</option>
                                    </select>
                                </td>
                            </tr>
                            <tr class="natureza-juridica-pj">
                                <th>
                                    Razão Social
                                </th>
                                <td>
                                    <input type="text" name="razao_social" value="<?php echo ( isset( $vendorInfo->razao_social ) ) ? $vendorInfo->razao_social : ''; ?>" autocomplete="password" class="widefat" maxlength="255">
                                </td>
                            </tr>
                            <tr class="natureza-juridica-pj">
                                <th>
                                    Nome Fantasia
                                </th>
                                <td>
                                    <input type="text" name="nome_fantasia" value="<?php echo ( isset( $vendorInfo->nome_fantasia ) ) ? $vendorInfo->nome_fantasia : ''; ?>" autocomplete="password" class="widefat" maxlength="255">
                                </td>
                            </tr>
                            <tr class="natureza-juridica-pj">
                                <th>
                                    CNPJ
                                </th>
                                <td>
                                    <input type="text" name="cnpj" value="<?php echo ( isset( $vendorInfo->cnpj ) ) ? $vendorInfo->cnpj : ''; ?>" autocomplete="password" class="widefat polen-cnpj" maxlength="18">
                                </td>
                            </tr>
                            <tr class="natureza-juridica-pj">
                                <th>
                                    Reter ISS na Fonte?
                                </th>
                                <td>
                                    <input type="radio" name="reter_iss" value="S"<?php echo ( isset( $vendorInfo->reter_iss ) && $vendorInfo->reter_iss == 'S' ) ? ' checked="checked"' : ''; ?>> Sim
                                    &nbsp;
                                    <input type="radio" name="reter_iss" value="N"<?php echo ( ( isset( $vendorInfo->reter_iss ) && $vendorInfo->reter_iss == 'N' ) || ( ! isset( $vendorInfo->reter_iss ) || is_null( $vendorInfo->reter_iss ) || $vendorInfo->reter_iss != 'S' ) ) ? ' checked="checked"' : ''; ?>> Não
                                </td>
                            </tr>
                            <tr class="natureza-juridica-pf">
                                <th>
                                    Nome
                                </th>
                                <td>
                                    <input type="text" name="nome" value="<?php echo ( isset( $vendorInfo->nome ) ) ? $vendorInfo->nome : ''; ?>" autocomplete="password" class="widefat" maxlength="255">
                                </td>
                            </tr>
                            <tr class="natureza-juridica-pf">
                                <th>
                                    CPF
                                </th>
                                <td>
                                    <input type="text" name="cpf" value="<?php echo ( isset( $vendorInfo->cpf ) ) ? $vendorInfo->cpf : ''; ?>" autocomplete="password" class="widefat polen-cpf" maxlength="14">
                                </td>
                            </tr>
                        </table>
                    </div>

                    <!-- Informações de Contato -->
                    <div id="PolenVendorProfileTab2">
                        <table class="form-table">
                            <tr>
                                <th>
                                    E-mail
                                </th>
                                <td>
                                    <input type="text" id="store_email" name="store_email" value="<?php echo ( isset( $vendorInfo->email ) ) ? $vendorInfo->email : ''; ?>" autocomplete="password" class="widefat" maxlength="255">
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    Telefone
                                </th>
                                <td>
                                    <input type="text" name="telefone" value="<?php echo ( isset( $vendorInfo->telefone ) ) ? $vendorInfo->telefone : ''; ?>" autocomplete="password" class="widefat polen-phone" maxlength="15">
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    Celular
                                </th>
                                <td>
                                    <input type="text" name="celular" value="<?php echo ( isset( $vendorInfo->celular ) ) ? $vendorInfo->celular : ''; ?>" autocomplete="password" class="widefat polen-phone" maxlength="15">
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    WhatsApp
                                </th>
                                <td>
                                    <input type="text" name="whatsapp" value="<?php echo ( isset( $vendorInfo->whatsapp ) ) ? $vendorInfo->whatsapp : ''; ?>" autocomplete="password" class="widefat polen-phone" maxlength="15">
                                </td>
                            </tr>
                        </table>
                    </div>

                    <?php if( is_admin() && ( $current_screen->base == 'user-edit' || ( $current_screen->base == 'user' && $current_screen->action == 'add' ) ) ) { ?>
                    <!-- Dados Bancários -->
                    <div id="PolenVendorProfileTab5">
                    <table class="form-table">
                            <tr>
                                <th>
                                    Banco
                                </th>
                                <td>
                                    <?php
                                    $Polen_Bancos = new Polen_Bancos();
                                    $bancos = $Polen_Bancos->listar();
                                    ?>
                                    <select name="banco" class="widefat">
                                        <option value="">Selecione...</option>
                                        <?php
                                        if( $bancos && is_array( $bancos ) && count( $bancos ) > 0 ) {
                                            foreach( $bancos as $k => $banco ) {
                                                $codigo_banco = str_pad( $banco->codigo, 3, "0", STR_PAD_LEFT );
                                                $selected = ( isset( $vendorInfo->codigo_banco ) && strval( $vendorInfo->codigo_banco ) == strval( $codigo_banco ) ) ? ' selected' : '';
                                        ?>
                                        <option <?php echo $selected; ?> value="<?php echo $codigo_banco; ?>:<?php echo $banco->nome; ?>"><?php echo $codigo_banco . ' - ' . $banco->nome; ?></option>
                                        <?php
                                            }
                                        }
                                        ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    Agência
                                </th>
                                <td>
                                    <input type="text" name="agencia" value="<?php echo ( isset( $vendorInfo->agencia ) ) ? $vendorInfo->agencia : ''; ?>" autocomplete="password" class="widefat polen-digits-only" maxlength="4">
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    Conta
                                </th>
                                <td>
                                    <input type="text" name="conta" value="<?php echo ( isset( $vendorInfo->conta ) ) ? $vendorInfo->conta : ''; ?>" autocomplete="password" class="widefat polen-bank-account" maxlength="20">
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    Tipo de Conta
                                </th>
                                <td>
                                    <select name="tipo_conta" class="widefat">
                                        <option value="">Selecione...</option>
                                        <option value="CC" <?php echo ( isset( $vendorInfo->tipo_conta ) && $vendorInfo->tipo_conta == 'CC' ) ? ' selected' : ''; ?>>Conta Corrente</option>
                                        <option value="CP" <?php echo ( isset( $vendorInfo->tipo_conta ) && $vendorInfo->tipo_conta == 'CP' ) ? ' selected' : ''; ?>>Conta Poupança</option>
                                    </select>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <!-- Configurações -->
                    <div id="PolenVendorProfileTab6">
                        <table class="form-table">
                            <tr>
                                <th>
                                    Subordinate Merchant ID
                                </th>
                                <td>
                                    <input type="text" name="subordinate_merchant_id" value="<?php echo ( isset( $vendorInfo->subordinate_merchant_id ) ) ? $vendorInfo->subordinate_merchant_id : ''; ?>" autocomplete="password" class="widefat">
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    MDR
                                </th>
                                <td>
                                    <input type="text" name="mdr" value="<?php echo ( isset( $vendorInfo->mdr ) ) ? $vendorInfo->mdr : ''; ?>" autocomplete="password" class="widefat">
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    Fee
                                </th>
                                <td>
                                    <input type="text" name="fee" value="<?php echo ( isset( $vendorInfo->fee ) ) ? $vendorInfo->fee : ''; ?>" autocomplete="password" class="widefat polen-digits-only" maxlength="5">
                                </td>
                            </tr>
                        </table>
                    </div>
                    <?php } ?>
                </div>
            </td>
        </tbody>
    </table>
</div>