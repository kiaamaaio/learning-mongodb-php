<?php
// General
define('MAX_RETRY_COUNT', 10);

// MongoDB Connection
define('MONGODB_CONNECTION_SRV_FLAG', false);
define('MONGODB_CONNECTION_HOST', '127.0.0.1');
define('MONGODB_CONNECTION_USERNAME', '');
define('MONGODB_CONNECTION_PASSWORD', '');

// MongoDB Exception Code
define('MONGODB_EXCEPTION_CODE_WRITE_CONFLICT', 112);
define('MONGODB_EXCEPTION_CODE_DUPLICATE_KEY', 11000);