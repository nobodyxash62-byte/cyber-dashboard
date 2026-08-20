<?php
// Shared SMTP settings for the app.
// This is the sender account used for all password emails.
// Each signed-in user receives the email at their own account email address.
// You can override these values with environment variables in your server config.
define('SMTP_HOST', getenv('SMTP_HOST') ?: 'smtp.gmail.com');
define('SMTP_PORT', getenv('SMTP_PORT') ?: 587);
define('SMTP_SECURE', getenv('SMTP_SECURE') ?: 'tls');
define('SMTP_USERNAME', getenv('SMTP_USERNAME') ?: 'olatunbosunjude3939@gmail.com');
define('SMTP_PASSWORD', getenv('SMTP_PASSWORD') ?: 'dgcxmeiycvwbksis');
define('SMTP_FROM', getenv('SMTP_FROM') ?: SMTP_USERNAME);
