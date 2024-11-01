<?php
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('SATL')) {
    class SATL
    {
        /**
         * @var SATL
         */
        private static $_instance;

        /**
         *  Constructor.
         */
        public function __construct()
        {
            $this->init_hooks();
            $this->includes();
        }

        public function includes()
        {
            include_once SATL_ABSPATH . 'inc/class-db-install.php';
            include_once SATL_ABSPATH . 'inc/helper.php';

        }

        /**
         * Main Instance.
         *
         * Ensures only one instance of is loaded or can be loaded.
         *
         * @return SATL - Main instance.
         * @static
         */
        public static function instance()
        {
            if (is_null(self::$_instance)) {
                self::$_instance = new self();
            }
            return self::$_instance;
        }

        public function init_hooks()
        {
            add_action('admin_enqueue_scripts', array($this, 'admin_enqueue'));
            add_action('init', array($this, 'install'));
            add_action('admin_footer', array($this, 'modals'));
            add_action('wp_ajax_status', array($this, 'status_msg'));
            add_action('wp_ajax_save', array($this, 'save_msg'));
            add_action('wp_ajax_table', array($this, 'get_task_data'));
            add_action('admin_menu', array($this, '_action_admin_menu'));
            Load_Theme_TextDomain('satl', SATL_ABSPATH . 'languages');

        }

        /**
         * @internal
         */
        public function _action_admin_menu()
        {
            add_menu_page(
                __('Task List', 'satl'),
                __('Task List', 'satl'),
                'manage_options',
                'satl-task-list',
                array($this, 'sub_menu'),
                'dashicons-list-view'
            );
        }

        public function sub_menu()
        {

            global $wpdb;
            $admin_message = $wpdb->prefix . 'atl_admin_message';

            $limit = 10;
            $page = isset($_GET['paged']) ? (int)$_GET['paged'] : 1;
            $offset = ($page - 1) * $limit;
            $all_messages = $wpdb->get_results(
                    $wpdb->prepare("SELECT * FROM {$admin_message} ORDER BY time_create DESC LIMIT %d , %d", $offset, $limit)
            );
            $total = $wpdb->get_var("select count(*) as total from $admin_message");
            $num_of_pages = (int)ceil($total / $limit);
            $next_page = $page !== $num_of_pages ? $page + 1 : $page;
            $prev_page = $page !== 1 ? $page - 1 : $page;
            ?>
            <div class="satl-page-content satl-font">
                <h1 class="headline">- <?php _e('All Tasks', 'satl'); ?></h1>
                <?php if (!$all_messages): ?>
                    <div class="task-empty"><?php _e('There is no task !', 'satl') ?></div>
                    <input type="button" id="create-task" value="<?php _e('create new task', 'satl') ?>">
                <?php else:
                    ?>
                    <table class="table-tasks">
                        <thead>
                        <tr>
                            <th scope="col"><?php _e('Title', 'satl'); ?></th>
                            <th scope="col"><?php _e('Creator', 'satl'); ?></th>
                            <th scope="col"><?php _e('Receiver', 'satl'); ?></th>
                            <th scope="col"><?php _e('Status', 'satl'); ?></th>
                            <th scope="col"><?php _e('Action', 'satl'); ?></th>
                        </tr>
                        </thead>
                        <tbody>

                        <?php foreach ($all_messages as $item) { ?>
                            <tr>
                                <td data-label="<?php esc_attr_e('Title', 'satl'); ?>"><?php esc_html_e($item->title) ?></td>
                                <td data-label="<?php esc_attr_e('Creator', 'satl'); ?>"><?php
                                    $creator = get_userdata($item->creator_id);
                                    esc_html_e($creator->user_login) ?></td>
                                <td data-label="<?php esc_attr_e('Receiver', 'satl'); ?>"><?php
                                    if ($item->user_id == 0) {
                                        _e('ALL', 'satl');
                                    } else {
                                        $user = get_userdata($item->user_id);
                                        esc_html_e($user->user_login);
                                    }
                                    ?>
                                </td>
                                <td data-label="<?php esc_attr_e('Status', 'satl'); ?>">
                                    <?php esc_html_e(SATL_Helper::get_status_label($item->status)) ?>
                                </td>
                                <td data-label="<?php esc_attr_e('Action', 'satl'); ?>">
                                    <button class="view-msg"
                                            data-msg-detailed="<?php echo htmlspecialchars(json_encode($item),
                                                ENT_QUOTES, 'UTF-8') ?>"><?php _e('View', 'satl'); ?></button>
                                </td>
                            </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                    <div class="footer-satl-table">
                        <div class="pagination-list">
                            <a href="<?php echo esc_url(add_query_arg('paged', $prev_page)) ?>"
                               class="btn <?php echo $page === 1 ? 'disabled' : '' ?>" id="btn_prev">
                                <?php _e('Prev', 'satl'); ?>
                            </a>
                            <a href="<?php echo esc_url(add_query_arg('paged', $next_page)) ?>"
                               class="btn <?php echo $page === $num_of_pages ? 'disabled' : '' ?>"
                               id="btn_next">
                                <?php _e('Next', 'satl'); ?>
                            </a>
                        </div>
                        <span class="total-task-count"><?php echo sprintf(__('Total : %s'), $total) ?></span>
                    </div>
                <?php endif; ?>

            </div>

            <?php

        }


        public function save_msg()
        {
            if (!check_ajax_referer('ajax-nonce', 'security')) {
                wp_send_json_error('Invalid security token sent.');
                wp_die();
            }
            $user_id = (int)$_POST['user_id'] ;
            $title = sanitize_title($_POST['title']);
            $desc = sanitize_textarea_field($_POST['description']);
            if (empty($user_id) || empty($title)) {
                wp_send_json_error(false);
            }
            global $wpdb;
            $msg_table = $wpdb->prefix . 'atl_admin_message';
            $res = false;
            if (current_user_can('administrator')) {
                $res = $wpdb->insert(
                    $msg_table,
                    array(
                        'time_create' => current_time('mysql'),
                        'creator_id' => get_current_user_id(),
                        'title' => $title,
                        'description' => $desc,
                        'user_id' => $user_id,
                    )
                );
            }
            wp_send_json($res);
        }

        public function status_msg()
        {
            if (!check_ajax_referer('ajax-nonce', 'security')) {
                wp_send_json_error('Invalid security token sent.');
                wp_die();
            }
            global $wpdb;
            if (empty($_POST['status']) || empty($_POST['msg_id'])){
                wp_send_json_error(false);
            }

            $status = sanitize_text_field($_POST['status']);
            if (!in_array($status,$this->allowed_status_msg())){
                wp_send_json_error(false);
            }
            $msgId = (int)$_POST['msg_id'];
            $admin_message = $wpdb->prefix . 'atl_admin_message';
            $res = $wpdb->update($admin_message, array('time_edit' => current_time('mysql'), 'status' => $status), array('id' => $msgId));
            if (!$res) {
                wp_send_json_error(false);
            }
            wp_send_json(true);

        }

        public function modals()
        {
            return include_once SATL_ABSPATH . 'templates/modal.php';
        }

        public function install()
        {
            if (wp_doing_ajax()) {
                return;
            }
            $stored_db_version = get_option('atl_db_version');
            if (!$stored_db_version || !SATL_DB()->is_installed()) {
                // fresh installation.
                SATL_DB()->init();
            } elseif (version_compare($stored_db_version, SATL_DB_VERSION, '<')) {
                // update database.
                SATL_DB()->update($stored_db_version);
            }
        }

        public function admin_enqueue()
        {
            wp_enqueue_style('atl_font_awesome_css',
                plugins_url('../assets/css/font-awesome.min.css', __FILE__),
                array(),
                SATL_VERSION
            );
            wp_enqueue_style('atl_font_iranyekan_css',
                plugins_url('../assets/fonts/iranyekan/font-iranyekan.css', __FILE__),
                array(),
                SATL_VERSION
            );
            wp_enqueue_style('atl_select2-bootstrap4.css',
                plugins_url('../assets/css/select2-bootstrap4.css', __FILE__),
                array(),
                SATL_VERSION
            );
            wp_enqueue_style('atl_select2_css',
                plugins_url('../assets/css/select2.min.css', __FILE__),
                array(),
                SATL_VERSION
            );
            wp_enqueue_style('atl_admin_css',
                plugins_url('../assets/css/style.css', __FILE__),
                array(),
                SATL_VERSION
            );
            if (is_rtl()) {
                wp_enqueue_style('atl_admin_css_rtl',
                    plugins_url('../assets/css/rtl.css', __FILE__),
                    array(),
                    SATL_VERSION
                );
            }
            wp_enqueue_script('atl_md5_js',
                plugins_url('../assets/js/md5.min.js', __FILE__),
                array('jquery'),
                SATL_VERSION,
                true
            );
            wp_enqueue_script('atl_select2_js',
                plugins_url('../assets/js/select2.min.js', __FILE__),
                array('jquery'),
                SATL_VERSION,
                true
            );
            wp_enqueue_script('atl_functions_js',
                plugins_url('../assets/js/functions.js', __FILE__),
                array('jquery'),
                SATL_VERSION,
                true
            );
            wp_localize_script('atl_functions_js', 'ajax_var', array(
                'url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('ajax-nonce'),
                'l10n' => array(
                    'y' => __('year', 'satl'),
                    'm' => __('month', 'satl'),
                    'w' => __('week', 'satl'),
                    'd' => __('day', 'satl'),
                    'h' => __('hours', 'satl'),
                    'i' => __('min', 'satl'),
                    's' => __('sec', 'satl'),
                    'ago' => __('ago', 'satl'),
                    'required_user_and_title' => __('It is required to fill in the title and select the user', 'satl'),
                )
            ));
        }

        /**
         * Define constant if not already set.
         *
         * @param string $name Constant name.
         * @param string|bool $value Constant value.
         */
        private function define($name, $value)
        {
            if (!defined($name)) {
                define($name, $value);
            }
        }
        private function allowed_status_msg(){
            return [
                    'pending','done'
            ];
        }
    }
}