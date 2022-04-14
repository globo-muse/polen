<?php
namespace Polen\Includes\Module;

defined('ABSPATH') || die;

use Exception;
use Polen\Includes\Polen_SignInUser_Strong_Password;

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

    public function __construct(int $user_id)
    {
        global $wpdb;
        $this->table = $wpdb->base_prefix . 'polen_talents';
        $this->user = get_user_by('ID', $user_id);
    }

    public static function create_from_product_id($product_id)
    {
        $user = self::get_talent_from_product($product_id);

        return new self($user->ID);
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
     * Retornar informações básicas do talento
     *
     * @return array
     */
    public function get_info_talent(): array
    {
        global $wpdb;
        $sql = "
            SELECT `user_id`, `email`, `celular`, `telefone`, `whatsapp`, `email`, `nome_fantasia`, `nascimento`
            FROM `" . $this->table . "`
            WHERE `user_id`=" . $this->user->ID;

        return $wpdb->get_results($sql);
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

    /**
     * retornar influencia por estado e cidade
     *
     * @return mixed|null
     */
    public function get_influence_by_region()
    {
        $influence = get_field('metrics', 'user_'. $this->user->ID);
        if (empty($influence)) {
            return null;
        }

        return $influence;
    }

    /**
     * Retornar listagem de faixa etária
     *
     * @return array|null
     */
    public function get_age_group(): ?array
    {
        $age_group = get_field('age_group', 'user_'. $this->user->ID);
        if (empty($age_group)) {
            return null;
        }

        return $age_group;
    }

    /**
     * Retonar audiência por gênero
     *
     * @return array
     */
    public function get_audience(): array
    {
        return [
            'man' => get_field('man', 'user_'. $this->user->ID),
            'woman' => get_field('woman', 'user_'. $this->user->ID),
        ];
    }
}
