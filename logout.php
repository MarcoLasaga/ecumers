<?php
require_once __DIR__ . '/config.php';

logout_user();
set_flash('success', 'You have been logged out.');
redirect('index.php');