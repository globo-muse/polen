<?php
if ( ! defined( 'WPINC' ) ) {
    die;
}

use Polen\Includes\Polen_Update_Fields;
use Polen\Includes\Polen_Bancos;
?>
<div class="metaboxSellerData" id="metaboxSellerData">
    <h2>Dados do Talento</h2>
    <?php
    $current_screen = get_current_screen();
    $Polen_Update_Fields = new Polen_Update_Fields();
    $user_id = $_REQUEST['user_id'];
    if(!empty($user_id)) {
        $user = get_user_by('id', $user_id);
    }
    $vendorInfo = $Polen_Update_Fields->get_vendor_data( intval( $user_id ?? null ) );
    ?>
    <table class="form-table">
        <tbody>
            <td>
                <div id="PolenVendorTabs" class="PolenVendorTabs">
                    <ul>
                        <li><a href="#PolenVendorProfileTab0">Geral</a></li>
                        <li><a href="#PolenVendorProfileTab1">Dados do Talento</a></li>
                        <li><a href="#PolenVendorProfileTab2">Informações de Contato</a></li>
                        <li><a href="#PolenVendorProfileTab3">Redes Sociais</a></li>
                        <?php if( is_admin() && ( $current_screen->base == 'user-edit' || ( $current_screen->base == 'user' && $current_screen->action == 'add' ) ) ) { ?>
                        <li><a href="#PolenVendorProfileTab5">Dados Bancários</a></li>
                        <li><a href="#PolenVendorProfileTab6">Configurações Financeiras</a></li>
                        <?php } ?>
                    </ul>

                    <!-- Dados da Loja -->
                    <div id="PolenVendorProfileTab0">
                        <table class="form-table">
                            <tr id="tr_talent_alias">
                                <th>
                                    Slug do Perfil
                                </th>
                                <td>
                                    <?php 
                                    $current_screen = get_current_screen();
                                    if( isset( $current_screen->base ) && $current_screen->base == 'user-edit' ) {
                                    ?>
                                    <?php bloginfo( 'url' ); ?>/<strong><?php echo ( isset( $vendorInfo->talent_alias ) ) ? $vendorInfo->talent_alias : ''; ?></strong>
                                    <input type="hidden" name="talent_alias" value="<?php echo ( isset( $vendorInfo->talent_alias ) ) ? $vendorInfo->talent_alias : ''; ?>">
                                    <?php  
                                    } else {
                                    ?>
                                    <input type="text" name="talent_alias" value="<?php echo ( isset( $vendorInfo->talent_alias ) ) ? $vendorInfo->talent_alias : ''; ?>" autocomplete="off" class="widefat" maxlength="255">
                                    <small>Ex: <?php bloginfo( 'url' ); ?><strong>/nome-do-talento/</strong></small>
                                    <?php } ?>
                                </td>
                            </tr>
                            <tr id="tr_talent_alias">
                                <th>
                                    Imagem de capa
                                </th>
                                <td>
                                    <input type="hidden" name="talent_cover_image_id" class="polen-input-image-id" value="<?php echo ( isset( $vendorInfo->cover_image_id ) && ! empty( $vendorInfo->cover_image_id ) ) ? $vendorInfo->cover_image_id : ''; ?>">
                                    <input type="hidden" name="talent_cover_image_url" class="polen-input-image-url" value="<?php echo ( isset( $vendorInfo->cover_image_url ) && ! empty( $vendorInfo->cover_image_url ) ) ? $vendorInfo->cover_image_url : ''; ?>">
                                    <input type="hidden" name="talent_cover_image_thumb" class="polen-input-image-thumb" value="<?php echo ( isset( $vendorInfo->cover_image_thumb ) && ! empty( $vendorInfo->cover_image_thumb ) ) ? $vendorInfo->cover_image_thumb : ''; ?>">
                                    <div class="polen-image-gallery-data"><?php echo ( isset( $vendorInfo->cover_image_thumb ) && ! empty( $vendorInfo->cover_image_thumb ) ) ? '<img src="' . $vendorInfo->cover_image_thumb . '" alt="Imagem de Capa">' : ''; ?></div>
                                    <input type="button" name="talent_cover_image" class="button button-primary polen-media-manager" value="Selecionar Imagem">
                                </td>
                            </tr>
                            <tr id="tr_talent_alias">
                                <th>
                                    Vídeo do perfil
                                </th>
                                <td>
                                    <input type="text" name="talent_profile_video" value="<?php echo ( isset( $vendorInfo->talent_profile_video ) ) ? $vendorInfo->talent_profile_video : ''; ?>" autocomplete="off" class="widefat">
                                    <small>Ex: https://vimeo.com/video/1234567890</small>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    Responde em
                                </th>
                                <td>
                                    <input type="text" name="tempo_resposta" value="<?php echo ( isset( $vendorInfo->tempo_resposta ) ) ? $vendorInfo->tempo_resposta : ''; ?>" autocomplete="off" class="widefat" maxlength="255">
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    Profissão
                                </th>
                                <td>
                                    <input type="text" name="profissao" value="<?php echo ( isset( $vendorInfo->profissao ) ) ? $vendorInfo->profissao : ''; ?>" autocomplete="off" class="widefat" maxlength="255">
                                </td>
                            </tr>
<!--                            <tr>
                                <th>
                                    Descrição
                                </th>
                                <td>
                                    <textarea name="descricao" rows="5" cols="80" class="widefat" style="width: 100%;"><?php echo ( isset( $vendorInfo->descricao ) ) ? $vendorInfo->descricao : ''; ?></textarea>
                                </td>
                            </tr>-->
                        </table>
                    </div>

                    <!-- Dados do Talento -->
                    <div id="PolenVendorProfileTab1">
                        <table class="form-table">
                            <tr>
                                <th>
                                    Data de nascimento
                                </th>
                                <td>
                                    <input type="text" name="nascimento" value="<?php echo ( isset( $vendorInfo->nascimento ) ) ? $vendorInfo->nascimento : ''; ?>" autocomplete="off" class="widefat polen-date" maxlength="10">
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    Categorias do Talento
                                </th>
                                <td>
                                    <select name="talent_category[]" id="talent_category" class="widefat" multiple >
                                        <option value=""><?php echo esc_html__('Choose a talent category', 'polen' );?></option>
                                        <?php 
                                        $terms = get_terms( array(
                                            'taxonomy' => 'talent_category',
                                            'hide_empty' => false,
                                        ) );
                                        if( ! is_wp_error( $terms ) && count( $terms ) > 0 ) {
                                            $arrSelected = array();
                                            $storeCategories = wp_get_object_terms( $_REQUEST['user_id'], 'talent_category' );
                                            foreach( $storeCategories as $talentCategory ){
                                                $arrSelected[] = $talentCategory->slug;
                                            }

                                            foreach( $terms as $term) {
                                        ?> 
                                                <option value="<?php echo $term->slug; ?>" <?php echo selected( in_array( $term->slug, $arrSelected )); ?> >
                                                    <?php echo $term->name; ?>
                                                </option> 
                                        <?php } } ?>
                                    </select>
                                </td>
                            </tr>
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
                                    <input type="text" name="razao_social" required value="<?php echo ( isset( $vendorInfo->razao_social ) ) ? $vendorInfo->razao_social : ''; ?>" autocomplete="off" class="widefat" maxlength="255">
                                </td>
                            </tr>
                            <tr class="natureza-juridica-pj">
                                <th>
                                    Nome Fantasia
                                </th>
                                <td>
                                    <input type="text" name="nome_fantasia" value="<?php echo ( isset( $vendorInfo->nome_fantasia ) ) ? $vendorInfo->nome_fantasia : ''; ?>" autocomplete="off" class="widefat" maxlength="255">
                                </td>
                            </tr>
                            <tr class="natureza-juridica-pj" id="cnpj-natureza-juridica-pj">
                                <th>
                                    CNPJ
                                </th>
                                <td>
                                    <input type="text" name="cnpj" value="<?php echo ( isset( $vendorInfo->cnpj ) ) ? $vendorInfo->cnpj : ''; ?>" autocomplete="off" class="widefat polen-cnpj" maxlength="18">
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
                                    <input type="text" name="nome" value="<?php echo ( isset( $vendorInfo->nome ) ) ? $vendorInfo->nome : ''; ?>" autocomplete="off" class="widefat" maxlength="255">
                                </td>
                            </tr>
                            <tr class="natureza-juridica-pf" id="cpf-natureza-juridica-pf">
                                <th>
                                    CPF
                                </th>
                                <td>
                                    <input type="text" name="cpf" value="<?php echo ( isset( $vendorInfo->cpf ) ) ? $vendorInfo->cpf : ''; ?>" autocomplete="off" class="widefat polen-cpf" maxlength="14">
                                </td>
                            </tr>
                        </table>
                    </div>

                    <!-- Informações de Contato -->
                    <div id="PolenVendorProfileTab2">
                        <table class="form-table">
                            <tr style="overflow: hidden;height: 0;display: block;">
                                <th>
                                    E-mail
                                </th>
                                <td>
                                    <input type="text" id="store_email" name="store_email" value="<?php echo ( isset( $vendorInfo->email ) ) ? $vendorInfo->email : ''; ?>" autocomplete="off" class="widefat" maxlength="255">
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    E-mail Contato
                                </th>
                                <td>
                                    <input type="text" id="contact_email" name="contact_email" value="<?php echo ( isset( $user->contact_email ) ) ? $user->contact_email : ''; ?>" autocomplete="off" class="widefat" maxlength="255">
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    Telefone
                                </th>
                                <td>
                                    <input type="text" name="telefone" value="<?php echo ( isset( $vendorInfo->telefone ) ) ? $vendorInfo->telefone : ''; ?>" autocomplete="off" class="widefat polen-phone" maxlength="15">
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    Celular
                                </th>
                                <td>
                                    <input type="text" name="celular" value="<?php echo ( isset( $vendorInfo->celular ) ) ? $vendorInfo->celular : ''; ?>" autocomplete="off" class="widefat polen-phone" maxlength="15">
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    WhatsApp
                                </th>
                                <td>
                                    <input type="text" name="whatsapp" value="<?php echo ( isset( $vendorInfo->whatsapp ) ) ? $vendorInfo->whatsapp : ''; ?>" autocomplete="off" class="widefat polen-phone" maxlength="15">
                                </td>
                            </tr>
                        </table>
                    </div>

                    <!-- Redes Sociais -->
                    <div id="PolenVendorProfileTab3">
                        <table class="form-table">
                            <tr>
                                <th>
                                    Facebook
                                </th>
                                <td>
                                    <input type="text" name="facebook" value="<?php echo ( isset( $vendorInfo->facebook ) ) ? $vendorInfo->facebook : ''; ?>" autocomplete="off" class="widefat">
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    Instagram
                                </th>
                                <td>
                                    <input type="text" name="instagram" value="<?php echo ( isset( $vendorInfo->instagram ) ) ? $vendorInfo->instagram : ''; ?>" autocomplete="off" class="widefat">
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    Twitter
                                </th>
                                <td>
                                    <input type="text" name="twitter" value="<?php echo ( isset( $vendorInfo->twitter ) ) ? $vendorInfo->twitter : ''; ?>" autocomplete="off" class="widefat">
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    Pinterest
                                </th>
                                <td>
                                    <input type="text" name="pinterest" value="<?php echo ( isset( $vendorInfo->pinterest ) ) ? $vendorInfo->pinterest : ''; ?>" autocomplete="off" class="widefat">
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    Linked In
                                </th>
                                <td>
                                    <input type="text" name="linkedin" value="<?php echo ( isset( $vendorInfo->linkedin ) ) ? $vendorInfo->linkedin : ''; ?>" autocomplete="off" class="widefat">
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    YouTube
                                </th>
                                <td>
                                    <input type="text" name="youtube" value="<?php echo ( isset( $vendorInfo->youtube ) ) ? $vendorInfo->youtube : ''; ?>" autocomplete="off" class="widefat">
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
                                    <input type="text" name="agencia" value="<?php echo ( isset( $vendorInfo->agencia ) ) ? $vendorInfo->agencia : ''; ?>" autocomplete="off" class="widefat polen-digits-only" maxlength="4">
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    Conta
                                </th>
                                <td>
                                    <input type="text" name="conta" value="<?php echo ( isset( $vendorInfo->conta ) ) ? $vendorInfo->conta : ''; ?>" autocomplete="off" class="widefat polen-bank-account" maxlength="20">
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
                                    <input type="text" name="subordinate_merchant_id" value="<?php echo ( isset( $vendorInfo->subordinate_merchant_id ) ) ? $vendorInfo->subordinate_merchant_id : ''; ?>" autocomplete="off" class="widefat">
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    MDR
                                </th>
                                <td>
                                    <input type="text" name="mdr" value="<?php echo ( isset( $vendorInfo->mdr ) ) ? $vendorInfo->mdr : ''; ?>" autocomplete="off" class="widefat">
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    Fee
                                </th>
                                <td>
                                    <input type="text" name="fee" value="<?php echo ( isset( $vendorInfo->fee ) ) ? $vendorInfo->fee : ''; ?>" autocomplete="off" class="widefat polen-digits-only" maxlength="5">
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