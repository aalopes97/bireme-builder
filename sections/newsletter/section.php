<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

return array(
    'label'           => __( 'Newsletter', 'bireme-builder' ),
    'description'     => __( 'Caixa de inscrição na newsletter.', 'bireme-builder' ),
    'icon'            => 'dashicons-email-alt',
    'external_styles' => array(
        'bireme-font-inter' => 'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&display=swap',
    ),
    'fields'          => array(
        'title'          => array(
            'type'    => 'text',
            'label'   => __( 'Título', 'bireme-builder' ),
            'default' => __( 'Receba o Boletim BIREME na sua caixa de entrada', 'bireme-builder' ),
        ),
        'text'           => array(
            'type'    => 'textarea',
            'label'   => __( 'Texto', 'bireme-builder' ),
            'default' => __( 'Assine nossa newsletter e fique por dentro das principais notícias e publicações', 'bireme-builder' ),
        ),
        'image'          => array(
            'type'  => 'image',
            'label' => __( 'Imagem', 'bireme-builder' ),
            'help'  => __( 'Ilustração do quadro branco à direita. Sem imagem, usa um ícone de e-mail.', 'bireme-builder' ),
        ),
        'placeholder'    => array(
            'type'    => 'text',
            'label'   => __( 'Texto do campo de e-mail', 'bireme-builder' ),
            'default' => __( 'Seu e-mail', 'bireme-builder' ),
        ),
        'button_text'    => array(
            'type'    => 'text',
            'label'   => __( 'Texto do botão', 'bireme-builder' ),
            'default' => __( 'Assinar', 'bireme-builder' ),
        ),
        'destination'    => array(
            'type'    => 'select',
            'label'   => __( 'Destino das inscrições', 'bireme-builder' ),
            'options' => array(
                'wordpress' => __( 'Salvar no WordPress (menu Newsletter, com exportação CSV)', 'bireme-builder' ),
                'external'  => __( 'Enviar para serviço externo (Mailchimp, Brevo, RD Station...)', 'bireme-builder' ),
            ),
        ),
        'success'        => array(
            'type'      => 'text',
            'label'     => __( 'Mensagem de sucesso', 'bireme-builder' ),
            'default'   => __( 'Inscrição realizada! Obrigado por assinar.', 'bireme-builder' ),
            'condition' => array( 'destination' => 'wordpress' ),
        ),
        'external_url'   => array(
            'type'      => 'text',
            'label'     => __( 'URL de envio do formulário (action)', 'bireme-builder' ),
            'help'      => __( 'Copie o atributo "action" do formulário de inscrição gerado pelo serviço.', 'bireme-builder' ),
            'condition' => array( 'destination' => 'external' ),
        ),
        'external_field' => array(
            'type'      => 'text',
            'label'     => __( 'Nome do campo de e-mail', 'bireme-builder' ),
            'help'      => __( 'Ex.: EMAIL (Mailchimp), email (Brevo/RD Station).', 'bireme-builder' ),
            'default'   => 'EMAIL',
            'condition' => array( 'destination' => 'external' ),
        ),
        'external_blank' => array(
            'type'      => 'checkbox',
            'label'     => __( 'Abrir a confirmação do serviço em nova aba', 'bireme-builder' ),
            'default'   => true,
            'condition' => array( 'destination' => 'external' ),
        ),
    ),
);
