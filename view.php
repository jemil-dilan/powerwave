<?php
// File: admin/view_order.php
require_once 'includes/config.php';
require_once 'includes/functions.php';

requireAdmin();

$orderId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$orderId) {
    redirect('orders.php');
}
