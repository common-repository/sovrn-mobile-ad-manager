<?php

require_once 'class.SovrnAdBase.php';

$aligmentOptions = array(
    'left' => 'Left',
    'center' => 'Center',
    'right' => 'Right'
);

/**
 * Sovrn ad data model used for saving and fetching data consistently. Since WPTouch (and WP) 
 * settings are key/value, ads are stored with a naming convention of:
 *
 *      ($fieldPrefix)(position key)
 */
class SovrnAdModel extends SovrnAdBase {

    /**
     * Array of settings from Wordpress DB
     * @var array/null
     */
    public $settings;

    /**
     * The key for Wordpress to store our settings (serialized) into
     * @var string
     */
    public $settingKey = 'sovrn_mobile_ad_manager_settings';

    /**
     * Prefix for all ad field names
     * @var string
     */
    public $fieldPrefix = 'sovrn_ad_';

    /**
     * Array of all settings fields and their configuration
     * @var array
     */
    public $fields = array(
        array(
            'name' => 'enable_ads', // Unprefixed field name
            'label' => 'Enable Mobile Ads', // Field label
            'input' => 'checkbox', // WPTouch admin input type, see wptouch-pro-3/admin/settings/html/ for all options
            'default' => 1 // Default value
        ),
        array(
            'name' => 'ad_code_post_content_before',
            'label' => 'Before Post Content Ad Code',
            'input' => 'textarea',
            'default' => ''
        ),
        array(
            'name' => 'ad_code_post_content_after',
            'label' => 'After Post Content Ad Code',
            'input' => 'textarea',
            'default' => ''
        ),
        array(
            'name' => 'ad_code_fixed_top',
            'label' => 'Fixed Top Ad Code',
            'input' => 'textarea',
            'default' => ''
        ),
        array(
            'name' => 'ad_alignment_fixed_top',
            'label' => 'Fixed Top Alignment',
            'input' => 'select',
            'options' => array(
                'left' => 'Left',
                'center' => 'Center',
                'right' => 'Right'
            ),
            'default' => 'center'
        ),
        array(
            'name' => 'ad_code_fixed_bottom',
            'label' => 'Fixed Bottom Ad Code',
            'input' => 'textarea',
            'default' => ''
        ),
        array(
            'name' => 'ad_alignment_fixed_bottom',
            'label' => 'Fixed Bottom Alignment',
            'input' => 'select',
            'options' => array(
                'left' => 'Left',
                'center' => 'Center',
                'right' => 'Right'
            ),
            'default' => 'center'
        ),
    );

    /**
     * SovrnAdModel constructor
     */
    public function __construct() {
        $this->ensureSettings();
    }

    /**
     * If settings don't exist, create them with default values
     * @return void
     */
    public function ensureSettings() {
        $settings = $this->getSettings();
        if (!$settings) {
            $defaults = $this->getDefaultSettings();
            add_option($this->getSettingsKey(), $defaults, '', 'yes');
        }
    }

    /**
     * Get settings array from DB
     * @return array
     */
    public function getSettings() {
        if (!$this->settings) {
            $settings = get_option($this->getSettingsKey());
            $this->settings = $settings;
        }
        return $this->settings;
    }

    /**
     * Get an array of default settings
     * @return array
     */
    public function getDefaultSettings() {
        $defaults = array();
        $fields = $this->getFields();
        foreach ($fields as $field) {
            $default = '';
            if (array_key_exists('default', $field) && $field['default']) {
                $default = $field['default'];
            }
            $defaults[$field['name']] = $default;
        }
        return $defaults;
    }

    /**
     * Sanitize settings on save
     * @param  array $settings Array of settings on POST
     * @return array
     */
    public function sanitizeSettings($settings) {
        // Make sure 'enable_ads' isn't missing altogether when not checked
        if (!array_key_exists('enable_ads', $settings)) {
            $settings['enable_ads'] = '0';
        }
        return $settings;
    }

    /**
     * Getter for `settingsKey`
     * @return string
     */
    public function getSettingsKey() {
        return $this->settingKey;
    }

    /**
     * Field prefix getter
     * @return string
     */
    public function getFieldPrefix() {
        return $this->fieldPrefix;
    }

    /**
     * Get field name as stored into settings for passed in short field name
     * @param  array $field Field config array to extract name from
     * @return string
     */
    public function getFieldName($field) {
        return $field['name'];
    }

    /**
     * Getter for $fields
     * @return array
     */
    public function getFields() {
        return $this->fields;
    }

    /**
     * Get an array of all ad field names
     * @return array
     */
    public function getFieldNames() {
        $fields = array();
        foreach ($this->getFields() as $field) {
            $fields[] = $this->getFieldName($field);
        }
        return $fields;
    }

    /**
     * Finds a field by the passed in unprefixed field name
     * @param  string $name Unprefixed field name to find
     * @return array Returns array of field config as set in $fields, or an empty array if nothing found
     */
    public function _findField($name) {
        $fields = $this->getFields();
        $filtered = array_filter($fields, function($field) use ($name) {
            return $field['name'] == $name;
        });

        if ($filtered) {
            return array_shift($filtered);
        }

        return array();
    }

    /**
     * Extract an array of ads from settings array, excluding empty ones. Should be an array of arrays 
     * whose keys indicate position on the page, and who should contain keys of their own: 'code' and 'alignment' 
     * (if applicable). Example return value:
     *
     *  array(
     *      'fixed_top' => array(
     *          'code' => '...',
     *          'alignment' => 'left'
     *      ),
     *      'post_content_after' => array(
     *          'code' => '...'
     *      )
     *  )
     * 
     * @param  array $settings Array of plugin settings as retrieved from `getSettings`
     * @return array
     */
    public function getAdsFromSettings($settings) {
        $ads = array();
        $fieldNameRegex = '/^ad_(code|alignment)_(.+)$/';
        
        // Loop through all settings variables, include non-empty ones that match our ad names
        
        foreach ($settings as $fieldName => $settingValue) {
            if (preg_match($fieldNameRegex, $fieldName, $matches) && $settingValue != '') {
                $adKey = $matches[2];
                $shortFieldName = $matches[1];
                if (!array_key_exists($adKey, $ads)) {
                    $ads[$adKey] = array();
                }
                $ads[$adKey][$shortFieldName] = $settingValue;
            }
        }

        $adsWithCode = array_filter($ads, function($ad) {
            return array_key_exists('code', $ad) && !empty($ad['code']);
        });

        return $adsWithCode;
    }

    /**
     * Get an array of ads from this object's `$settings` variable
     * @return array
     */
    public function getAds() {
        return $this->getAdsFromSettings($this->getSettings());
    }
}

?>