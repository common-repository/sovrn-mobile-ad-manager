<?php

require_once 'class.SovrnAdBase.php';

/**
 * Responsible for all output to the frontend of the Wordpress/WPTouch site
 */
class SovrnAdView extends SovrnAdBase {

    /**
     * Copy of SovrnAdModel object
     * @var object
     */
    public $model;

    /**
     * Ad position key values that wrap around post content
     * @var array
     */
    public $contentPositions = array(
        'post_content_before',
        'post_content_after'
    );

    /**
     * Ad position key values that are fixed
     * @var array
     */
    public $fixedPositions = array(
        'fixed_top',
        'fixed_bottom'
    );

    /**
     * SovrnAdView constructor
     * @param object $model Copy of SovrnAdModel object
     */
    public function __construct($model) {
        $this->model = $model;
        $this->ads = $this->model->getAds();
        if ($this->isEnabled()) {
            $this->registerFilters();
        }
    }

    /**
     * Indicates whether ads should be spit out or not
     * @return boolean
     */
    public function isEnabled() {
        $settings = $this->model->getSettings();
        return $this->wpTouchIsInstalled() && $this->ads && $settings['enable_ads'] == '1';
    }

    /**
     * Register Wordpress filters
     * @return void
     */
    public function registerFilters() {

        //  Extract all positions used in ads
        
        $positions = array_keys($this->ads);

        // See if any post content positions are set and apply hook if so

        if (array_intersect($positions, $this->contentPositions)) {
            add_filter('the_content', array($this, 'renderContentAds'));
        }

        // Apply footer hook if any fixed positions are set

        if (array_intersect($positions, $this->fixedPositions)) {
            add_filter('wptouch_body_bottom', array($this, 'renderFixedAds'));
        }

        add_filter('wp_enqueue_scripts', array($this, 'addAssets'));
    }

    /**
     * Link in necessary CSS and JS assets
     */
    public function addAssets() {
        $staticDir = rtrim(SOVRN_AD_MANAGER_STATIC_DIR, '/') . '/';

        $CSSKey = sprintf('%s-css', SOVRN_AD_MANAGER_KEY);
        $JSKey = sprintf('%s-js', SOVRN_AD_MANAGER_KEY);

        // Asset URLs

        $sovrnCSSURL = plugins_url($staticDir . 'css/sovrn-ad-manager.css');
        $sovrnJSURL = plugins_url($staticDir . 'js/sovrn-ad-manager.js');
        
        wp_enqueue_style($CSSKey, $sovrnCSSURL, false, SOVRN_AD_MANAGER_VERSION);

        // If a jQuery hasn't been loaded, load it
        
        if (!wp_script_is('jquery', 'registered')) {
            wp_enqueue_script('jquery');
        }

        wp_enqueue_script($JSKey, $sovrnJSURL, array('jquery'), SOVRN_AD_MANAGER_VERSION);
    }

    /**
     * Render an array of ads and return the result
     * @param  array $ads Array of ads
     * @return string
     */
    public function renderAds($ads) {
        $output = '';
        foreach ($ads as $positionKey => $code) {
            $output .= $this->getAdMarkup($positionKey, $code);
        }
        return $output;
    }

    /**
     * Render fixed position ads
     * @return void
     */
    public function renderFixedAds() {
        $ads = array_intersect_key($this->ads, array_flip($this->fixedPositions));
        echo $this->renderAds($ads);
    }

    /**
     * Render all before/after content ads
     * @return string
     */
    public function renderContentAds($content) {
        $before = '';
        $after = '';

        if (array_key_exists('post_content_before', $this->ads)) {
            $before = $this->getAdMarkup('post_content_before', $this->ads['post_content_before']);
        }

        if (array_key_exists('post_content_after', $this->ads)) {
            $after = $this->getAdMarkup('post_content_after', $this->ads['post_content_after']);
        }

        return $before . $content . $after;
    }

    /**
     * Wrap ad in <div> and apply appropriate classes for positioning
     * @param  array $ad Ad data array with 'position' and 'code' keys
     * @return string Ad HTML
     */
    public function getAdMarkup($positionKey, $ad) {
        $classes = array('sovrn-ad');
        $positionBits = explode('_', $positionKey);

        // If fixed position, map the position bits to class names and add them 
        // to our $classes list
        
        if (array_shift($positionBits) == 'fixed') {
            $classes[] = 'sovrn-ad-fixed';
            $classes[] = sprintf('sovrn-ad-fixed-%s', array_shift($positionBits));
            $classes[] = sprintf('sovrn-ad-fixed-%s', $ad['alignment']);
        }
        
        return sprintf('<div class="%s"><div class="sovrn-ad-inner">%s</div></div>', implode(' ', $classes), $ad['code']);
    }
}

?>