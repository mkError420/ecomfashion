<?php
require_once __DIR__ . '/config/db.php';
customer_logout();
flash_set('success', 'You have been logged out.');
redirect('index.php');
