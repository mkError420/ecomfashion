<?php
require_once __DIR__ . '/../config/db.php';
admin_logout();
flash_set('success', 'You have been logged out.');
redirect('admin/login.php');
