<?php
// URL path where the app lives. Folder htdocs/gabay => '/gabay'.
define('BASE_URL', '/gabay');

define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'gabay');
define('DB_USER', 'root');
define('DB_PASS', '');            // XAMPP default is empty

// Students must register with this email domain, e.g. 'school.edu.ph'. Empty = any email.
define('SCHOOL_EMAIL_DOMAIN', '');

// Admin mood chart hides any week with fewer entries than this, so small groups
// cannot be identified. Set to 1 for a demo with few accounts.
define('MIN_GROUP_SIZE', 3);

// Shown in the privacy notice when a student registers. Replace with the real OSDS contact before the demo.
define('PRIVACY_CONTACT', 'the OSDS office (add the OSDS email or phone number here)');

date_default_timezone_set('Asia/Manila');

// PLACEHOLDERS. Confirm every number with the campus and the agency before the demo,
// then set HOTLINES_VERIFIED to true (this hides the "pending verification" notice).
define('HOTLINES_VERIFIED', false);
function hotlines(): array {
    return [
        ['name' => 'NCMH Crisis Hotline',        'number' => '1553',          'note' => 'Free landline call, 24 hours'],
        ['name' => 'NCMH Crisis Hotline (mobile)', 'number' => '0917 899 8727', 'note' => 'Call or text, 24 hours'],
        ['name' => 'Hopeline Philippines',       'number' => '0917 558 4673',          'note' => 'Toll-free for some mobile networks'],
        ['name' => 'Campus OSDS counseling office', 'number' => '(02) 8539-9950', 'note' => 'Office hours only'],
    ];
}
