<?php

namespace SDTDL;
if (!defined('ABSPATH')) {
    exit;
}
define('SDTDL_VERSION', '1.0.4');

class SDTDL
{

    private $_path;
    private $_user_id;
    private $_option;
    private $_user_option;
    private $_network_admin;
    private $_date_time_format;

    public function __construct()
    {
        $this->_init_user();
        $this->_init_settings();
        $this->_path = plugins_url('', SDTDL_PLUGIN_FILE);
        if ($this->_network_admin) {
            add_action('wp_network_dashboard_setup', [$this, 'widget_setup']);
        } else {
            add_action('wp_dashboard_setup', [$this, 'widget_setup']);
        }
        add_action('wp_footer', [$this, 'front_list']);
        add_action("wp_ajax_sdtdl_update", [$this, "update_list"]);
        add_action("wp_ajax_sdtdl_settings", [$this, "save_settings"]);
        add_action("init", [$this, 'load_text_domain']);
    }

    public function front_list(): void
    {
        if (is_admin() || $this->_user_option['extra']['front'] === 'false') {
            return;
        }
        wp_enqueue_script('jquery-ui-dialog');
        wp_enqueue_style('wp-jquery-ui-dialog');
        wp_enqueue_style('sdtdl', $this->_path . '/css/sdtdl-front.css', ['wp-jquery-ui-dialog'], SDTDL_VERSION);
        wp_enqueue_script('sdtdl', $this->_path . '/js/sdtdl-front.js', ['jquery-ui-dialog'], SDTDL_VERSION);
        $this->_localize_strings();
        //Make sure we use the user's language preferences on the front
        unload_textdomain('sortable-dashboard-to-do-list');
        switch_to_locale(get_user_locale());
        $this->load_text_domain();
        ob_start();
        require_once __DIR__ . '/../templates/list-front.php';
        echo ob_get_clean();
        restore_current_locale();
    }

    public function load_text_domain(): void
    {
        load_plugin_textdomain('sortable-dashboard-to-do-list', false, dirname(plugin_basename(SDTDL_PLUGIN_FILE)) . '/lang');
    }

    private function _init_user(): void
    {
        global $current_user;
        wp_get_current_user();
        $this->_network_admin = is_network_admin();
        $this->_user_id = $current_user->ID;
        $this->_option = $this->_get_option();
        $this->_user_option = $this->_option[$this->_user_id] ?? [];
        $this->_date_time_format = get_option('date_format') . ' ' . get_option('time_format');
    }

    private function _init_settings(): void
    {
        $userSettings = $this->_user_option['extra'] ?? [];
        if (!isset($userSettings['front'])) {
            $userSettings['front'] = 'false';
        }
        $this->_user_option['extra'] = $userSettings;
    }

    private function _get_option(): array
    {
        if ($this->_network_admin) {
            return array_filter((array)get_site_option('sdtdl_todo_list_items'));
        }
        return array_filter((array)get_option('sdtdl_todo_list_items'));
    }

    private function _update_option($data): void
    {
        if ($this->_network_admin) {
            update_site_option('sdtdl_todo_list_items', $data);
        } else {
            update_option('sdtdl_todo_list_items', $data);
        }
    }

    public function widget_setup(): void
    {
        wp_add_dashboard_widget('sdtdl_to_do_widget', esc_html__('To-Do List', 'sortable-dashboard-to-do-list'), ['SDTDL\SDTDL', 'widget'], null, [
            'option' => $this->_user_option,
            'date_time_format' => $this->_date_time_format,
            'network_admin' => $this->_network_admin
        ]);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);
    }

    public function enqueue_admin_scripts(): void
    {
        wp_enqueue_script('jquery-ui-dialog');
        wp_enqueue_style('wp-jquery-ui-dialog');
        wp_enqueue_style('sdtdl', $this->_path . '/css/sdtdl-admin.css', ['wp-jquery-ui-dialog'], SDTDL_VERSION);
        wp_enqueue_script('jquery-ui-sortable');
        wp_enqueue_script('sdtdl', $this->_path . '/js/sdtdl-admin.js', ['jquery-ui-sortable', 'jquery-ui-dialog'], SDTDL_VERSION);
        $this->_localize_strings();
    }

    private function _check_nonce(): void
    {
        if (!isset($_REQUEST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_REQUEST['nonce'])), 'sdtdl_update')) {
            wp_send_json_error();
        }
    }

    private function _localize_strings(): void
    {
        $SDTDL = [
            "strings" => [
                'RecentlyAdded' => esc_html__('Recently added', 'sortable-dashboard-to-do-list'),
                'RecentlyEdited' => esc_html__('Recently edited', 'sortable-dashboard-to-do-list'),
                'Close' => esc_html__('Close'),
                'Edit' => esc_html__('Edit'),
                'Save' => esc_html__('Save'),
                'SaveEdits' => esc_html__('Save Edits'),
                'Cancel' => esc_html__('Cancel'),
                'Delete' => esc_html__('Delete'),
                'nonce' => wp_create_nonce('sdtdl_update'),
                'UserID' => $this->_user_id,
                'BlogID' => get_current_blog_id()
            ]
        ];
        wp_localize_script('sdtdl', 'sdtdl', $SDTDL);
    }


    public static function widget($var, $args): void
    {
        ob_start();
        require_once __DIR__ . '/../templates/list-back.php';
        echo ob_get_clean();
    }


    public function update_list(): void
    {
        $this->_check_nonce();
        $data = $this->_option;
        $requestedData = $_REQUEST['data'] ?? [];
        $requests = $this->_sanitize_data($requestedData);
        $response = $this->_get_formatted_date();
        $data[$this->_user_id]['data'] = $requests;
        $this->_update_option($data);
        wp_send_json_success($response);
    }

    public function save_settings(): void
    {
        $this->_check_nonce();
        $data = $this->_option;
        $settings = $_REQUEST['settings'] ?? [];
        $settings = $this->_sanitize_settings($settings);
        $data[$this->_user_id]['extra'] = $settings;
        $this->_update_option($data);
        wp_send_json_success();
    }

    private function _get_formatted_date()
    {
        $request = $_REQUEST['date_data'];
        $type = sanitize_text_field($request['type']);
        if (!$type) {
            return null;
        }
        $timestamp = (int)$request['timestamp'];
        $date = wp_date($this->_date_time_format, $timestamp);
        if ($type == 'add') {
            $iconClass = "dashicons-plus";
            $formattedDate = sprintf(esc_html__("Added %s", 'sortable-dashboard-to-do-list'), $date);
        } else {
            $iconClass = "dashicons-edit";
            $formattedDate = sprintf(esc_html__("Last edit %s", 'sortable-dashboard-to-do-list'), $date);
        }
        return ["full" => '<span class="dashicons ' . $iconClass . '"></span>' . $formattedDate, "short" => $formattedDate];
    }

    private function _sanitize_settings($settings): array
    {
        foreach ($settings as $key => $setting) {
            if ($key === 'front') {
                if (in_array($setting, ['true', 'false'])) {
                    $settings[$key] = $setting;
                } else {
                    $settings[$key] = 'false';
                }
            } else {
                unset($settings[$key]);
            }
        }
        return $settings;
    }

    private function _sanitize_data($requests): array
    {
        foreach ($requests as $key => $request) {
            foreach ($request as $type => $data) {
                if ($type === 'added' || $type === 'last_edited') {
                    $requests[$key][$type] = (int)$data;
                } elseif ($type === 'front') {
                    if (in_array($data, ['true', 'false'])) {
                        $requests[$key][$type] = $data;
                    } else {
                        $requests[$key][$type] = 'false';
                    }
                } elseif ($type === 'title' || $type === 'id') {
                    $requests[$key][$type] = sanitize_text_field($data);
                } elseif ($type === 'content') {
                    $requests[$key][$type] = self::sanitize_item_content($data);
                } else {
                    unset($requests[$key][$type]);
                }
            }
        }
        return $requests;
    }

    public static function sanitize_item_content($content): string
    {
        $content = str_replace(['<br>', '<br/>', '<br />'], PHP_EOL, $content);
        return wp_kses($content, [
            'a' => [
                'href' => [],
                'target' => [],
                'title' => []
            ],
            'strong' => [],
            'em' => [],
            'u' => [],
            'ul' => [],
            'p' => [],
            'ol' => [],
            'li' => [],
            'span' => [
                'style' => []
            ]
        ]);
    }

    public static function init(): void
    {
        //Make sure user may need this feature (at least blog author)
        if (!is_user_logged_in() || !current_user_can('edit_posts')) {
            return;
        }
        new self();
    }

    public static function uninstall_plugin(): void
    {
        delete_site_option('sdtdl_todo_list_items');
        if (is_network_admin()) {
            $sites = get_sites();
            foreach ($sites as $site) {
                delete_blog_option($site->blog_id, 'sdtdl_todo_list_items');
            }
        }

    }
}


