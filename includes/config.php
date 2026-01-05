<?php
declare(strict_types=1);

date_default_timezone_set('Asia/Kuala_Lumpur');

define('APP_NAME', 'UniRental');
define('APP_DEBUG', true);

define('DB_FILE', __DIR__ . '/../data/app.db');
define('UPLOAD_DIR_ITEMS', __DIR__ . '/../public/uploads/items');

define('DEFAULT_ADMIN_EMAIL', 'admin@unirental.local');
define('DEFAULT_ADMIN_PASSWORD', 'Admin1!');

// SMTP (optional)
define('SMTP_HOST', getenv('SMTP_HOST') ?: 'smtp.gmail.com');
define('SMTP_PORT', (int)(getenv('SMTP_PORT') ?: '587'));
define('SMTP_USER', getenv('SMTP_USER') ?: 'alastairjayden@gmail.com');
define('SMTP_PASS', getenv('SMTP_PASS') ?: 'vjcdezqaxqbzmvci');
define('SMTP_FROM', getenv('SMTP_FROM') ?: (SMTP_USER ?: 'noreply@unirental.local'));