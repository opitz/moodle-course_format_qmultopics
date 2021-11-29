<?php
/**
 * Created by PhpStorm.
 * User: opitz
 * Date: 29/11/21
 * Time: 17:22
 */

class format_qmultopics_generator extends component_generator_base {
    public function create_config_plugins($plugin_config) {
        global $DB;
        $DB->insert_record('mdl_config_plugins', $plugin_config);
    }
}