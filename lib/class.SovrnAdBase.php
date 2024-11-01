<?php

/**
 * Base class for all Sovrn Mobile Ad Manager plugin classes
 */
class SovrnAdBase {

    /**
     * Indicates whether WPTouch plugin is installed
     * @return bool
     */
    public function wpTouchIsInstalled() {
        $activePlugins = wp_get_active_and_valid_plugins();
        return preg_grep('/.*wptouch\/wptouch\.php$/', $activePlugins) || preg_grep('/.*wptouch-pro-3\/wptouch-pro-3\.php$/', $activePlugins);
    }
}

?>
