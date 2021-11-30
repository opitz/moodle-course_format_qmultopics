<?php
/**
 * Created by PhpStorm.
 * User: opitz
 * Date: 29/11/21
 * Time: 17:26
 */

class behat_format_qmultopics_generator extends behat_generator_base {

    protected function get_creatable_entities(): array {
        return [
            'config plugins' => [
                'singular' => 'config plugin',
                'datagenerator' => 'config_plugins',
                'required' => []
            ],
        ];
    }
}