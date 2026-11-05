<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Tipos de campo das seções: normalização do schema, valores padrão e sanitização.
 * O formulário do admin é montado em assets/admin/builder.js a partir de to_js().
 */
class Bireme_Fields {

    public static function types() {
        return array( 'text', 'textarea', 'wysiwyg', 'image', 'link', 'color', 'select', 'checkbox', 'repeater' );
    }

    /**
     * 'condition' => array( 'outro_campo' => 'valor' | array( 'v1', 'v2' ) ) exibe o campo no admin
     * só quando os campos irmãos têm esses valores. Os dados continuam salvos mesmo ocultos.
     *
     * @param array $fields Mapa key => definição do campo, como declarado em section.php.
     * @return array Mapa key => definição completa.
     */
    public static function normalize( $fields ) {
        $normalized = array();
        if ( ! is_array( $fields ) ) {
            return $normalized;
        }

        foreach ( $fields as $key => $field ) {
            $key = sanitize_key( $key );
            if ( '' === $key || ! is_array( $field ) ) {
                continue;
            }

            $field = wp_parse_args( $field, array(
                'type'         => 'text',
                'label'        => $key,
                'help'         => '',
                'placeholder'  => '',
                'options'      => array(),
                'fields'       => array(),
                'default'      => null,
                'max'          => 0,
                'button_label' => '',
                'row_label'    => '',
                'condition'    => array(),
            ) );

            $field['condition'] = is_array( $field['condition'] ) ? $field['condition'] : array();

            if ( ! in_array( $field['type'], self::types(), true ) ) {
                $field['type'] = 'text';
            }

            if ( 'repeater' === $field['type'] ) {
                $field['fields']    = self::normalize( $field['fields'] );
                $field['row_label'] = sanitize_key( $field['row_label'] );
            }

            $field['max'] = absint( $field['max'] );

            if ( null === $field['default'] ) {
                $field['default'] = self::empty_value( $field );
            } else {
                $field['default'] = self::sanitize_value( $field, $field['default'] );
            }

            $normalized[ $key ] = $field;
        }

        return $normalized;
    }

    public static function empty_value( array $field ) {
        switch ( $field['type'] ) {
            case 'checkbox':
                return false;
            case 'image':
                return array( 'id' => 0, 'url' => '', 'alt' => '' );
            case 'link':
                return array( 'url' => '', 'text' => '', 'target' => '' );
            case 'repeater':
                return array();
            case 'select':
                if ( ! empty( $field['options'] ) ) {
                    $keys = array_keys( $field['options'] );
                    return (string) reset( $keys );
                }
                return '';
            default:
                return '';
        }
    }

    public static function defaults( array $fields ) {
        $defaults = array();
        foreach ( $fields as $key => $field ) {
            $defaults[ $key ] = $field['default'];
        }
        return $defaults;
    }

    /**
     * Sanitiza os dados de uma seção (ou de uma linha de repeater) pelo schema.
     * Chaves que não existem no schema são descartadas.
     */
    public static function sanitize_data( array $fields, $data ) {
        $data  = is_array( $data ) ? $data : array();
        $clean = array();

        foreach ( $fields as $key => $field ) {
            $value         = array_key_exists( $key, $data ) ? $data[ $key ] : $field['default'];
            $clean[ $key ] = self::sanitize_value( $field, $value );
        }

        return $clean;
    }

    public static function sanitize_value( array $field, $value ) {
        switch ( $field['type'] ) {
            case 'textarea':
                return sanitize_textarea_field( (string) $value );

            case 'wysiwyg':
                return wp_kses_post( (string) $value );

            case 'color':
                $color = sanitize_hex_color( (string) $value );
                return $color ? $color : '';

            case 'select':
                $value   = (string) $value;
                $allowed = array_map( 'strval', array_keys( (array) $field['options'] ) );
                if ( in_array( $value, $allowed, true ) ) {
                    return $value;
                }
                return $allowed ? $allowed[0] : '';

            case 'checkbox':
                return ! empty( $value ) && '0' !== $value && 'false' !== $value;

            case 'image':
                $value = is_array( $value ) ? $value : array();
                $id    = isset( $value['id'] ) ? absint( $value['id'] ) : 0;
                $url   = isset( $value['url'] ) ? esc_url_raw( $value['url'] ) : '';
                if ( $id && ! $url ) {
                    $url = (string) wp_get_attachment_url( $id );
                }
                return array(
                    'id'  => $id,
                    'url' => $url,
                    'alt' => isset( $value['alt'] ) ? sanitize_text_field( $value['alt'] ) : '',
                );

            case 'link':
                $value = is_array( $value ) ? $value : array();
                return array(
                    'url'    => isset( $value['url'] ) ? esc_url_raw( trim( $value['url'] ) ) : '',
                    'text'   => isset( $value['text'] ) ? sanitize_text_field( $value['text'] ) : '',
                    'target' => ( isset( $value['target'] ) && '_blank' === $value['target'] ) ? '_blank' : '',
                );

            case 'repeater':
                $rows = array();
                if ( is_array( $value ) ) {
                    foreach ( array_values( $value ) as $row ) {
                        if ( is_array( $row ) ) {
                            $rows[] = self::sanitize_data( $field['fields'], $row );
                        }
                    }
                }
                if ( $field['max'] > 0 ) {
                    $rows = array_slice( $rows, 0, $field['max'] );
                }
                return $rows;

            default:
                return sanitize_text_field( (string) $value );
        }
    }

    /**
     * Completa dados salvos com os defaults atuais do schema (campos adicionados depois).
     */
    public static function merge_defaults( array $fields, $data ) {
        $data   = is_array( $data ) ? $data : array();
        $merged = array();

        foreach ( $fields as $key => $field ) {
            $value = array_key_exists( $key, $data ) ? $data[ $key ] : $field['default'];

            if ( 'repeater' === $field['type'] ) {
                $rows = array();
                foreach ( (array) $value as $row ) {
                    $rows[] = self::merge_defaults( $field['fields'], $row );
                }
                $value = $rows;
            } elseif ( in_array( $field['type'], array( 'image', 'link' ), true ) ) {
                $value = wp_parse_args( (array) $value, self::empty_value( $field ) );
            }

            $merged[ $key ] = $value;
        }

        return $merged;
    }

    /**
     * Converte o schema para lista ordenada (objetos JS reordenam chaves numéricas).
     */
    public static function to_js( array $fields ) {
        $list = array();

        foreach ( $fields as $key => $field ) {
            $item = array(
                'key'         => $key,
                'type'        => $field['type'],
                'label'       => (string) $field['label'],
                'help'        => (string) $field['help'],
                'placeholder' => (string) $field['placeholder'],
                'default'     => $field['default'],
            );

            if ( ! empty( $field['condition'] ) ) {
                $item['condition'] = $field['condition'];
            }

            if ( 'select' === $field['type'] ) {
                $item['options'] = array();
                foreach ( (array) $field['options'] as $value => $label ) {
                    $item['options'][] = array(
                        'value' => (string) $value,
                        'label' => (string) $label,
                    );
                }
            }

            if ( 'repeater' === $field['type'] ) {
                $item['fields']       = self::to_js( $field['fields'] );
                $item['max']          = $field['max'];
                $item['button_label'] = (string) $field['button_label'];
                $item['row_label']    = $field['row_label'];
            }

            $list[] = $item;
        }

        return $list;
    }
}
