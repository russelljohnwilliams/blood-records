<?php
if (!defined('ABSPATH')) {
    exit;
}

class WF_CustomerImpExpCsv_Admin_Screen {

    /**
     * Constructor
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'admin_menu'));
        add_action('admin_print_styles', array($this, 'admin_scripts'));
        add_action('admin_notices', array($this, 'admin_notices'));
    }

    /**
     * Notices in admin
     */
    public function admin_notices() {
        if (!function_exists('mb_detect_encoding')) {
            echo '<div class="error"><p>' . __('User/Customer CSV Import Export requires the function <code>mb_detect_encoding</code> to import and export CSV files. Please ask your hosting provider to enable this function.', 'wf_customer_import_export') . '</p></div>';
        }
    }

    /**
     * Admin Menu
     */
    public function admin_menu() {
        $page = add_users_page( __( 'User Import Export', 'wf_customer_import_export' ), __( 'User Import Export', 'wf_customer_import_export' ), 'list_users', 'hf_wordpress_customer_im_ex', array( $this, 'output' ) );
        $page1 = add_submenu_page( 'woocommerce', __( 'Customer Import Export', 'wf_customer_import_export' ), __( 'Customer Import Export', 'wf_customer_import_export' ),  'manage_woocommerce', 'hf_wordpress_customer_im_ex', array( $this, 'output' ) );
    }

    /**
     * Admin Scripts
     */
    public function admin_scripts() {
        global $wp_scripts;
        if(function_exists('WC')){
        wp_enqueue_style('woocommerce_admin_styles', WC()->plugin_url() . '/assets/css/admin.css');
        }
        wp_enqueue_style('woocommerce-user-csv-importer', plugins_url(basename(plugin_dir_path(WF_CustomerImpExpCsv_FILE)) . '/styles/wf-style.css', basename(__FILE__)), '', '1.0.0', 'screen');
        wp_enqueue_style('woocommerce-user-csv-importer1', plugins_url(basename(plugin_dir_path(WF_CustomerImpExpCsv_FILE)) . '/styles/select2.css', basename(__FILE__)), '', '4.0.6', '');
        wp_enqueue_script('woocommerce-user-csv-importer', plugins_url(basename(plugin_dir_path(WF_CustomerImpExpCsv_FILE)) . '/styles/select2.js', basename(__FILE__)), array(), '4.0.6', true);
        
    }
    

    /**
     * Admin Screen output
     */
    public function output() {
        $tab = 'export';

        if (!empty($_GET['tab'])) {
            if ($_GET['tab'] == 'import') {
                $tab = 'import';
            } else if ($_GET['tab'] == 'settings') {
                $tab = 'settings';
            } else if ($_GET['tab'] == 'help') {
                $tab = 'help';
            }
        }
        
        include( 'views/html-wf-admin-screen.php' );
    }

    /**
     * Admin page for help
     */
    public function admin_help_page() {        
        include('views/html-wf-help-guide.php');
    }

    /**
     * Admin Page for exporting
     */
    public function admin_export_page() {
        $post_columns = include( 'exporter/data/data-wf-post-columns.php' );
        include( 'views/export/html-wf-export-customers.php' );
    }


}

new WF_CustomerImpExpCsv_Admin_Screen();