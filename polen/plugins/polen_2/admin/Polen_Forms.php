<?php

namespace Polen\Admin;

use Exception;
use Polen\Includes\Polen_Form_DB;
use Polen\Includes\Polen_Zapier;

class Polen_Forms {

    public function __construct()
    {
        add_action('admin_menu', array($this, 'addMenu'));
        add_action('wp_ajax_submit_form', array($this, 'submitForm'));
        add_action('wp_ajax_nopriv_submit_form', array($this, 'submitForm'));

        if( isset( $_GET['export_form'] ) ){
            $csv = $this->export_to_csv($_GET['export_form']);
            header('Content-Type: text/csv; charset=utf-8');
            header( 'Content-Disposition: attachment;filename=export_'. date('d_m_Y').'.csv');
            echo $csv;
            exit;
        }
    }

    /**
     * Adiciona os menus no dashboard do wordpress
     *
     * @since    1.0.0
     */
    public function addMenu()
    {
        add_menu_page('Formulários',
            'Formulários',
            'manage_options',
            'forms',
            array($this, 'showForms'),
            'dashicons-email-alt'
        );

        add_submenu_page(
            'forms',
            'Ajuda',
            'Ajuda',
            'manage_options',
            'help-form',
            array($this, 'showFormHelp'),
        );
    }

    /**
     * View página principal
     *
     * @since    1.0.0
     */
    public function showForms()
    {
        $form_db = new Polen_Form_DB();
        $leads = $form_db->getLeads();
        require 'partials/form-enterprise.php';
    }

    /**
     * View página Ajuda
     *
     * @since    1.0.0
     */
    public function showFormHelp()
    {
        $form_db = new Polen_Form_DB();
        $leads = $form_db->getLeads(2);
        require 'partials/form-help.php';
    }

    /**
     * Salvar formulários no banco
     */
    public function submitForm()
    {
        try{
            $fields = $_POST;
            $requiredFields = $this->requiredFields();
            $data = array();

            foreach ($fields as $key => $field) {
                if (key_exists($key, $requiredFields)) {
                    unset($requiredFields[$key]);
                }

                $data[$key] = sanitize_text_field($field);
            }

            if (!empty($requiredFields)) {
                foreach ($requiredFields as $key => $requiredField) {
                    throw new Exception("O campo {$requiredField} é obrigatório", 422);
                }
            }

            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Email Inválido', 422);
            }

//            if(!wp_verify_nonce($data['nonce'], self::NONCE_ACTION )) {
//                throw new Exception('Erro na verificação de segurança', 422);
//            }

            $form_db = new Polen_Form_DB();
            $form_db->insert($data);
            $zapier = new Polen_Zapier();
            $zapier->send('https://hooks.zapier.com/hooks/catch/10583855/br5beyv/', $data);
            $this->mailSend($data);

            wp_send_json_success('ok', 200);
            wp_die();

        } catch (\Exception $e) {
            wp_send_json_error( array( 'Error' => $e->getMessage() ), 422 );
            wp_die();
        }
    }

    /**
     * Retorna todos os campos do formulário que são obrigatórios
     */
    private function requiredFields(): array
    {
        return [
            'name' => 'Nome',
            'email' => 'E-mail',
            'company' => 'Empresa',
            'phone' => 'Telefone',
            // 'terms' => 'Termos',
            'form_id' => 'ID do formulário',
        ];
    }

    /**
     * Retornar nome do campo em portugues para ser usado no disparo de email
     *
     * @param $field
     * @return null|string
     */
    private function translateFields($field): ?string
    {
        $fields = [
            'name' => 'Nome',
            'email' => 'E-mail',
            'company' => 'Empresa',
            // 'employees_quantity' => 'Quantidade de funcionários',
            // 'job' => 'Cargo',
            'product' => 'Código do Produto',
            'phone' => 'Telefone',
            // 'talent_name' => 'Nome do talento',
            // 'message' => 'Mensagem'
        ];

        if (!isset($fields[$field])) {
            return null;
        }

        return $fields[$field];
    }

    /**
     * Disparar email de novo cadastro
     * @param array $fields
     */
    public function mailSend(array $fields)
    {
        global $Polen_Plugin_Settings;
        $emails_company_page = $Polen_Plugin_Settings['recipient_email_polen_company'];
        $emails_help_page = $Polen_Plugin_Settings['recipient_email_polen_help'];

        $form_company = 1;
        $form_help = 2;

        if ($fields['form_id'] == $form_company) {
            $name = 'empresas';
            $emails =  $emails_company_page;
        }

        if ($fields['form_id'] == $form_help) {
            $name = 'ajuda';
            $emails = $emails_help_page;
        }

        $to = explode(',', $emails);
        $subject = "Novo cadastro Polen {$name} - {$fields['name']}";

        $body = '';
        foreach ($fields as $key => $field) {
            $name_value = $this->translateFields($key);
            if ($name_value === null) {
                continue;
            }

            $body .= "<p>{$name_value}: {$field}</p>";
        }

        $headers = array('Content-Type: text/html; charset=UTF-8; From: polen.me');

        if (!wp_mail($to, $subject, $body, $headers)) {
            throw new \Exception( 'Erro ao disparar email' );
        }
    }

    public function export_to_csv()
    {
        $titles = array(
            'ID',
            'Nome',
            'Empresa',
            'Email',
            'Quantidade de Funcionarios',
            'Orçamento',
            'Telefone',
            'Talento',
            'Mensagem',
            'Cadastro',
        );

        $output = implode(';', $titles);
        $output .= PHP_EOL;

        $form_db = new Polen_Form_DB();
        $leads = $form_db->getLeads();

        if (count($leads) > 0) {
            foreach ($leads as $row) {
                $output .= $row->id . ';';
                $output .= $row->name . ';';
                $output .= $row->company . ';';
                $output .= $row->email . ';';
                $output .= $row->employees_quantity . ';';
                $output .= $row->job . ';';
                $output .= $row->phone . ';';
                $output .= $row->talent . ';';
                $output .= $row->message . ';';
                $output .= $row->created_at . ';';
                $output .= PHP_EOL;
            }
        }

        echo $output;
    }
}