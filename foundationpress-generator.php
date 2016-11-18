<?php

/**
 * FoundationPress Generator
 *
 * FoundationPress Generator is based on the generator website for Components http://components.underscores.me
 * (C) 2012-2016 Automattic, Inc. Underscores is distributed under the terms of the GNU GPL v2 
 * or later.
 *
 * @link              http://www.colinduwe.com
 * @since             1.0.0
 * @package           Foundationpress_Generator
 *
 * @wordpress-plugin
 * Plugin Name:       FoundationPress Generator
 * Plugin URI:        https://github.com/colinduwe/foundationpress-generator
 * Description:       This provides a form to create a custom-named FoudnationPress theme
 * Version:           1.0.0
 * Author:            Colin Duwe
 * Author URI:        http://www.colinduwe.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       foundationpress-generator
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-foundationpress-generator-activator.php
 */
function activate_foundationpress_generator() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-foundationpress-generator-activator.php';
	Foundationpress_Generator_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-foundationpress-generator-deactivator.php
 */
function deactivate_foundationpress_generator() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-foundationpress-generator-deactivator.php';
	Foundationpress_Generator_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_foundationpress_generator' );
register_deactivation_hook( __FILE__, 'deactivate_foundationpress_generator' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-foundationpress-generator.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_foundationpress_generator() {

	$plugin = new Foundationpress_Generator();
	$plugin->run();

}
run_foundationpress_generator();
