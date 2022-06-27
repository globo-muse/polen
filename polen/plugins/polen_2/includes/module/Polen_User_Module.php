<?php
namespace Polen\Includes\Module;

defined('ABSPATH') || die;

use Exception;
use Polen\Includes\Polen_SignInUser_Strong_Password;
use Polen\Includes\v2\Polen_Talent_DB;

class Polen_User_Module
{

    const META_KEY_EMAIL_CONTACT = 'contact_email';
    /**
     * Obj
     * Informações básicas do usuario
     */
    public object $user;

    /**
     * Salvar tabela para uso dessa classe
     */
    public string $table;

    /**
     * Database para uso dessa classe
     */
    public Polen_Talent_DB $polen_talent_db;

    public function __construct(int $user_id)
    {
        global $wpdb;
        $this->table = $wpdb->base_prefix . 'polen_talents';
        $this->user = get_user_by('ID', $user_id);
        $this->polen_talent_db = new Polen_Talent_DB();
    }

    public static function create_from_product_id($product_id)
    {
        $user = self::get_talent_from_product($product_id);

        return new self($user->ID);
    }

    /**
     * Pega o produto pelo User_ID
     */
    public function get_product_by_user_id($user_id)
    {
        $args = array(
            'post_type' => 'product',
            'post_status' => 'any',
            'author' => $user_id,
        );
        $posts = get_posts($args);
        if (empty($posts)) {
            return null;
        }

        return wc_get_product($posts[0]);
    }

    /**
     * Retornar talento pelo ID do Produto
     *
     * @param int $product_id
     * @return object
     */
    private static function get_talent_from_product(int $product_id): object
    {
        global $wpdb;
        $sql = "SELECT `post_author` AS `ID`
            FROM `" . $wpdb->users . "` U
            LEFT JOIN `" . $wpdb->posts . "` P ON P.`post_author` = U.`ID`
            WHERE P.`ID`=" . $product_id;

        return $wpdb->get_results($sql)[0];
    }

    /**
     * Retornar email para recebimento de notificações
     *
     */
    public function get_receiving_email()
    {
        $contact_email = get_user_meta($this->user->ID, self::META_KEY_EMAIL_CONTACT, true);
        if(empty($contact_email)) {
            $contact_email = $this->user->user_email;
        }

        return $contact_email;
    }

    /**
     * Retornar nome customizado do usuario
     *
     * @return string
     */
    public function get_display_name(): string
    {
        return get_the_author_meta('display_name', $this->user->ID);
    }

    /**
     * Atualizar senha do usuario
     * @throws Exception
     */
    public function update_pass(string $current_pass, string $new_password)
    {
        $check = wp_authenticate($this->user->data->user_email, $current_pass);
        if (is_wp_error($check)) {
            throw new Exception('Senha atual incorreta', 403);
        }

        if (!$this->check_security_password(sanitize_text_field($new_password))) {
            $strong_password = new Polen_SignInUser_Strong_Password();
            throw new Exception($strong_password->get_default_message_error(), 403);
        }

        wp_set_password($new_password, $this->user->ID);
    }

    /**
     * Atualizar dados
     *
     * @param array $data
     * @throws Exception
     */
    public function update_user(array $data)
    {
        global $wpdb;

        if (empty($data)) {
            throw new Exception('Nenhum dado para ser atualizado!', 403);
        }

        $wpdb->update($this->table, $this->treatment_result($data), array('user_id'=> $this->user->ID));
    }

    /**
     * Mudar nome das chaves de acordo como está feito o banco de dados (para pt-br)
     * Tratar e limpar valores
     *
     * @param array $data
     * @return array
     */
    private function treatment_result(array $data): array
    {
        $values['celular'] = sanitize_text_field($data['phone']);
        $values['telefone'] = sanitize_text_field($data['telephone']);
        $values['whatsapp'] = sanitize_text_field($data['whatsapp']);

        return array_filter($values, 'ucfirst');
    }

    /**
     * fazer veririfação de senha
     *
     * @param $password
     * @return bool
     */
    private function check_security_password($password): bool
    {
        $strong_password = new Polen_SignInUser_Strong_Password();
        return $strong_password->verify_strong_password($password);
    }


    public function get_id()
    {
        return $this->user->ID;
    }

    public function get_email()
    {
        return $this->user->user_email;
    }

    public function get_fantasy_name()
    {
        $product = $this->get_product_by_user_id($this->get_id());
        return $product->get_title();
    }

    public function get_birthday()
    {
        $name_column_db = 'nascimento';
        return $this->polen_talent_db->get_column($name_column_db, $this->get_id());
    }

    public function get_phone()
    {
        $name_column_db = 'celular';
        return $this->polen_talent_db->get_column($name_column_db, $this->get_id());
    }

    public function get_telephone()
    {
        $name_column_db = 'telefone';
        return $this->polen_talent_db->get_column($name_column_db, $this->get_id());
    }

    public function get_whatsapp()
    {
        $name_column_db = 'whatsapp';
        return $this->polen_talent_db->get_column($name_column_db, $this->get_id());
    }

    public function get_document()
    {
        $fields = ['document_type', 'document'];
        $data = [];
        foreach ($fields as $field) {
            $data[$field] = get_field($field, 'user_'. $this->user->ID);
        }

        return $data;
    }

    public function get_merchant_id()
    {
        $config = get_field('config_split', 'user_'. $this->user->ID);
        $merchant_id = get_field('mechant_id', 'user_'. $this->user->ID);
        if ($config == 'disable' || !$merchant_id) {
            return null;
        }

        return $merchant_id;
    }
}
