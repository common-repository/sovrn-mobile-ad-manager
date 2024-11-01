<?php

require_once 'class.SovrnAdBase.php';
require_once 'class.SovrnAdAdmin.php';
require_once 'class.SovrnAdModel.php';
require_once 'class.SovrnAdView.php';

/**
 * Sovrn ad class responsible for composing admin/model/view classes
 */
class SovrnAdManager extends SovrnAdBase {

    /**
     * Admin object, if applicable
     * @var object
     */
    public $admin;

    /**
     * SovrnAdModel instance
     * @var object
     */
    public $model;

    /**
     * Frontend view object, instance of SovrnAdView
     * @var object
     */
    public $view;

    function __construct() {
        $this->model = new SovrnAdModel;

        if (is_admin()) {
            $this->admin = new SovrnAdAdmin($this->model);
        }

        $this->registerFilters();
    }

    /**
     * Register Wordpress filters
     * @return void
     */
    public function registerFilters() {
        if (!is_admin()) {
            // Using a filter (wptouch_functions_start) that doesn't run on the plain 'ol Wordpress 
            // side of things here to initialize our view
            add_filter('wptouch_functions_start', array($this, 'initializeView'));
        }
    }

    /**
     * Initialize frontend/view object
     * @return void
     */
    public function initializeView() {
        $this->view = new SovrnAdView($this->model);
    }
}

?>