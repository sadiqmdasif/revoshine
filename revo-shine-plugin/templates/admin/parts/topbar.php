<?php

global $user_preferred_theme;

?>

<div class="admin-topbar">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center">
        <div class="admin-topbar-title"><?php echo REVO_SHINE_PLUGIN_NAME; ?> Android / iOS Mobile App</div>
        <div class="d-flex align-items-center align-self-end gap-1">
            <?php

            include REVO_SHINE_TEMPLATE_PATH . 'admin/components/icons/styling.php';

            echo '<div class="pe-1">Dark Mode</div>';

            ?>

            <div class="form-check form-switch mt-1">
                <input class="form-check-input mini dark-mode-switch mt-0" name="dark_mode" type="checkbox" role="switch" <?php echo $user_preferred_theme === 'dark' ? 'checked' : '' ?>>
            </div>
        </div>
    </div>
</div>