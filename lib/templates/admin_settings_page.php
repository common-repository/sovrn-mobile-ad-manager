<div class="wrap">
    <h2>sovrn mobile ad manager</h2>
    <?php
    if (!$this->wpTouchIsInstalled()) {
        require_once 'admin_dependency_notice.php';
    }
    ?>
    <form method="post" action="options.php">
        <?php
        settings_fields($this->getOptionGroupKey());
        do_settings_sections($this->getPageKey());
        submit_button();
        ?>
    </form>
</div>