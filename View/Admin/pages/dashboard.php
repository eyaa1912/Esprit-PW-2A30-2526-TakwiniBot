<?php
$title = "Dashboard";

// NO LOGIN CHECK (as you requested)

// TOP
include __DIR__ . '/includes/layout_top.php';
?>

<div class="container-xxl p-4">

    <h4>Bienvenue 👋</h4>

    <div class="row">

        <div class="col-md-6">
            <div class="card p-3">
                <h5>Users</h5>
                <p>Manage users</p>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card p-3">
                <h5>Reports</h5>
                <p>Analytics</p>
            </div>
        </div>

    </div>

</div>

<?php include __DIR__ . '/includes/layout_bottom.php'; ?>