<?php
/**
 * The plugin bootstrap file.
 *
 * @link
 * @since
 *
 * @wordpress-plugin
 * Plugin Name:       Simple Admin Task List
 * Plugin URI:        https://github.com/alirezacrr/admin-task-list-plugin
 * Description:       This plugin is a simple version for creating tasks and managing tasks for admins and authors of your site
 * Version:           1.1.0
 * Author:            Alireza Jafari
 * Author URI:        https://alirezacrr.ir
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       satl
 * Domain Path:       /languages
 */

defined('ABSPATH') || exit;

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

if (!defined('SATL_FILE')) {
    define('SATL_FILE', __FILE__);
}
$plugin_version = '1.0.0';
$db_version = '1.0.0';
/**
 * SATL Version Define
 */
define('SATL_VERSION', $plugin_version);
define('SATL_DB_VERSION', $plugin_version);
define('SATL_ABSPATH', dirname(SATL_FILE) . '/');

if (!class_exists('SATL', false)) {
    include_once dirname(SATL_FILE) . '/inc/admin-task-list.php';
    SATL::instance();
}

