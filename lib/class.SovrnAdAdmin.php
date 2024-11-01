<?php

require_once 'class.SovrnAdBase.php';

/**
 * Handles the Wordpress admin side of the Sovrn Mobile Ad Manager plugin
 *
 * For reference:
 * 
 *     http://codex.wordpress.org/Adding_Administration_Menus
 *     http://codex.wordpress.org/Creating_Options_Pages
 */
class SovrnAdAdmin extends SovrnAdBase {

    /**
     * Copy of SovrnAdModel object
     * @var object
     */
    public $model;

    /**
     * The Wordpress admin "capability" level required to modify settings for Sovrn
     * @see https://codex.wordpress.org/Roles_and_Capabilities
     * @var string
     */
    public $wpCapabilityLevel = 'manage_options';

    /**
     * The page key used for our Wordpress settings page. Used to create the page, as well 
     * as to assign fields and sections
     * @var string
     */
    public $wpSettingsPageKey = SOVRN_AD_MANAGER_KEY;

    /**
     * @param object $model Copy of SovrnAdModel object
     */
    function __construct($model) {
        $this->model = $model;
        $this->settings = $this->model->getSettings();
        $this->registerFilters();
    }

    /**
     * Register relevant Wordpress filter hooks
     * @return void
     */
    public function registerFilters() {
        add_filter('admin_menu', array($this, 'handleAdminMenu'));
        add_filter('admin_init', array($this, 'handleAdminInit'));
        add_filter('plugin_action_links_' . SOVRN_AD_MANAGER_PLUGIN_FILE, array($this, 'setPluginActionLinks'));
    }

    /**
     * Respond to 'admin_menu' hook, registering our options/settings page
     * @return void
     */
    public function handleAdminMenu() {
        // Note: Change this to 'add_menu_page' to make it top level
        add_menu_page(
            'sovrn ad manager settings',
            'sovrn ad manager',
            $this->wpCapabilityLevel,
            $this->wpSettingsPageKey,
            array($this, 'renderOptionsPage')
        );
    }

    /**
     * Respond to 'admin_init' hook, building/configuring our settings page
     * @return void
     */
    public function handleAdminInit() {

        // All settings are serialized and stored into this single Wordpress setting

        register_setting(
            $this->getOptionGroupKey(),
            $this->model->getSettingsKey(),
            array($this->model, 'sanitizeSettings')
        );
        
        add_settings_section(
            $this->getSectionKey(),
            null,
            array($this, 'renderSettingsSection'),
            $this->getPageKey()
        );

        $this->addSettingsFields($this->model->getFields());
    }

    /**
     * Handle 'plugin_action_links_(plugin file path)' action to modify link list that 
     * displays under plugin title on plugins page
     * @param array $links
     * @return array
     */
    public function setPluginActionLinks($links) {
        $link = sprintf('<a href="%s">Settings</a>', admin_url('admin.php?page=' . $this->getPageKey()));
        array_push($links, $link);
        return $links;
    }

    /**
     * Getter for page key, used as a unique page identifier in Wordpress admin for Sovrn settings editor page
     * @return string
     */
    public function getPageKey() {
        return $this->wpSettingsPageKey;
    }

    /**
     * Getter for option group key for option group on Sovrn settings admin page. Since we only have one group 
     * of options, we can just use this one.
     * @return string
     */
    public function getOptionGroupKey() {
        return $this->getPageKey() . '-option-group';
    }

    /**
     * Getter for section key for Sovrn settings admin page. Again, we only have one, so this works
     * @return string
     */
    public function getSectionKey() {
        return $this->getPageKey() . '-section';
    }

    /**
     * Add Wordpress settings field for all `$fields` passed in
     * @param array $fields Fields config array as retrieved from SovrnAdModel::getFields
     */
    public function addSettingsFields($fields) {
        foreach ($fields as $field) {
            add_settings_field(
                $field['name'],
                $field['label'],
                array($this, 'renderField'),
                $this->getPageKey(),
                $this->getSectionKey(),
                $field
            );
        }
    }

    /**
     * Render output for settings page, should be referenced as a callback by `add_options_page`
     * @return void
     */
    public function renderOptionsPage() {
        require "templates/admin_settings_page.php";
    }

    /**
     * Render settings section on out options admin page, should be referenced as a callback by `add_settings_section`
     * @return void
     */
    public function renderSettingsSection() {
        require "templates/admin_settings_section.php";
    }

    /**
     * Render a single field, should be referenced as a callback by `add_settings_field`
     * @param  array $field Field config, should be a single item of SovrnAdModel::getFields() result array
     * @return void
     */
    public function renderField($field) {
        require "templates/admin_settings_field.php";
    }
}

?>