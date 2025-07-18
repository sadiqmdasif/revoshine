<div class="container-fluid mt-5 mb-3">
    <header class="panel-heading bg-white">
        <div class="panel-actions pt-2">
            <?php echo 'v' . REVO_SHINE_PLUGIN_VERSION ?>
        </div>

        <h2 class="panel-title">
            <div class="d-flex align-items-center">
                <img src="<?php echo revo_shine_get_logo() ?>" class="img-fluid mr-3" style="width: 30px">
                <?php echo REVO_SHINE_PLUGIN_NAME ?>
            </div>
        </h2>
    </header>
</div>

<!-- Start Wrapper -->
<div class="container-fluid">
    <div class="panel">
        <?php require_once REVO_SHINE_TEMPLATE_PATH . 'admin/parts/alert.php'; ?>

        <div class="inner-wrapper pt-0">
            <?php require_once REVO_SHINE_TEMPLATE_PATH . 'admin/parts/sidebar.php'; ?>

            <main role="main" class="content-body p-0">
                <div class="panel-body">