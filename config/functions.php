<?php

/**
 * functions.php - Helper functions for Pelayanan Desa Kedungwuni
 * 
 * Updated with enhanced security, better error handling, and additional utilities
 */

/**
 * Authentication & Authorization Functions
 */

/**
 * Check if user is logged in
 * @return bool True if user is logged in
 */
function is_logged_in(): bool
{
    return isset($_SESSION['user']['id']) && !empty($_SESSION['user']['id']);
}

/**
 * Redirect to dashboard if already logged in
 */
function redirect_if_logged_in(): void
{
    if (is_logged_in()) {
        $role = $_SESSION['user']['role'] ?? 'warga';
        $redirect = ($role === 'admin') ? 'admin/dashboard.php' : 'dashboard.php';
        header("Location: $redirect");
        exit();
    }
}

/**
 * Require user to be logged in
 * @param string|null $redirect Optional redirect path if not logged in
 */
function require_login(?string $redirect = 'login.php'): void
{
    if (!is_logged_in()) {
        if ($redirect) {
            $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
            header("Location: $redirect");
            exit();
        }
        throw new Exception('Authentication required');
    }
}

/**
 * Require specific user role
 * @param string|array $roles Required role(s)
 * @param string $redirect Redirect path if unauthorized
 */
function require_role($roles, string $redirect = 'unauthorized.php'): void
{
    require_login();

    $user_role = $_SESSION['user']['role'] ?? 'warga';

    if (is_array($roles)) {
        if (!in_array($user_role, $roles)) {
            header("Location: $redirect");
            exit();
        }
    } elseif ($user_role !== $roles) {
        header("Location: $redirect");
        exit();
    }
}

/**
 * Membuat potongan teks dengan panjang tertentu (support UTF-8)
 * 
 * @param string $text Teks yang akan dipotong
 * @param int $limit Panjang maksimal teks
 * @return string Teks yang sudah dipotong
 */
function excerpt($text, $limit = 100) {
    $text = strip_tags($text); // Hilangkan tag HTML jika ada
    if (mb_strlen($text, 'UTF-8') > $limit) {
        $text = mb_substr($text, 0, $limit, 'UTF-8');
        $lastSpace = mb_strrpos($text, ' ', 0, 'UTF-8');
        if ($lastSpace !== false) {
            $text = mb_substr($text, 0, $lastSpace, 'UTF-8');
        }
        $text .= '...';
    }
    return $text;
}

/**
 * Get current user data
 * @param string|null $key Specific user data key to retrieve
 * @return mixed User data or null if not logged in
 */
function current_user(?string $key = null)
{
    if (!is_logged_in()) {
        return null;
    }

    if ($key) {
        return $_SESSION['user'][$key] ?? null;
    }

    return $_SESSION['user'];
}

/**
 * Check if user has permission based on role
 * @param string|array $required_roles Required role(s)
 * @return bool True if authorized
 */
function is_authorized($required_roles): bool
{
    if (!is_logged_in()) {
        return false;
    }

    $user_role = $_SESSION['user']['role'] ?? 'warga';

    // Define role hierarchy
    $hierarchy = [
        'admin' => ['admin', 'petugas', 'warga'],
        'petugas' => ['petugas', 'warga'],
        'warga' => ['warga']
    ];

    if (is_array($required_roles)) {
        foreach ($required_roles as $role) {
            if (in_array($role, $hierarchy[$user_role])) {
                return true;
            }
        }
        return false;
    }

    return in_array($required_roles, $hierarchy[$user_role]);
}

/**
 * UI Helper Functions
 */

/**
 * Get CSS classes for status badges
 * @param string $status Status from database
 * @return string CSS classes
 */
function get_status_badge(string $status): string
{
    $classes = [
        'selesai' => 'bg-green-100 text-green-800',
        'diproses' => 'bg-yellow-100 text-yellow-800',
        'menunggu' => 'bg-blue-100 text-blue-800',
        'ditolak' => 'bg-red-100 text-red-800',
        'default' => 'bg-gray-100 text-gray-800'
    ];

    return $classes[strtolower($status)] ?? $classes['default'];
}

/**
 * Get icon for status
 * @param string $status Status from database
 * @param string $icon_set 'fa' for Font Awesome or 'bi' for Bootstrap Icons
 * @return string Icon class
 */
function get_status_icon(string $status, string $icon_set = 'fa'): string
{
    $icons = [
        'fa' => [
            'selesai' => 'fa-check-circle',
            'diproses' => 'fa-spinner fa-pulse',
            'menunggu' => 'fa-clock',
            'ditolak' => 'fa-times-circle',
            'default' => 'fa-question-circle'
        ],
        'bi' => [
            'selesai' => 'bi-check-circle-fill',
            'diproses' => 'bi-arrow-repeat',
            'menunggu' => 'bi-clock',
            'ditolak' => 'bi-x-circle-fill',
            'default' => 'bi-question-circle'
        ]
    ];

    $status = strtolower($status);
    return $icons[$icon_set][$status] ?? $icons[$icon_set]['default'];
}

/**
 * Formatting Functions
 */

/**
 * Format date to Indonesian format
 * @param string $date Date string
 * @param bool $include_time Whether to include time
 * @return string Formatted date
 */
function format_tanggal_indonesia(string $date, bool $include_time = false): string
{
    $bulan = [
        1 => 'Januari',
        'Februari',
        'Maret',
        'April',
        'Mei',
        'Juni',
        'Juli',
        'Agustus',
        'September',
        'Oktober',
        'November',
        'Desember'
    ];

    $timestamp = strtotime($date);
    if (!$timestamp) {
        return $date; // Return original if invalid date
    }

    $tanggal = date('j', $timestamp);
    $bulan = $bulan[date('n', $timestamp)];
    $tahun = date('Y', $timestamp);

    $result = "$tanggal $bulan $tahun";

    if ($include_time) {
        $waktu = date('H:i', $timestamp);
        $result .= " pukul $waktu";
    }

    return $result;
}

/**
 * Get document type label
 * @param string $type Document type from database
 * @return string Human-readable label
 */
function get_document_label(string $type): string
{
    $labels = [
        'KTP' => 'Kartu Tanda Penduduk',
        'KK' => 'Kartu Keluarga',
        'Surat Domisili' => 'Surat Keterangan Domisili',
        'Akte Kelahiran' => 'Akta Kelahiran',
        'Surat Kematian' => 'Surat Keterangan Kematian',
        'SKTM' => 'Surat Keterangan Tidak Mampu',
        'SKU' => 'Surat Keterangan Usaha'
    ];

    return $labels[$type] ?? $type;
}

/**
 * Session & Message Functions
 */

/**
 * Set flash message
 * @param string $type Message type (success, error, warning, info)
 * @param string $message The message content
 */
function set_flash_message(string $type, string $message): void
{
    $_SESSION['flash_messages'][] = [
        'type' => $type,
        'message' => $message,
        'timestamp' => time()
    ];
}

/**
 * Display all flash messages
 * @param bool $clear Whether to clear messages after display
 */
function display_flash_messages(bool $clear = true): void
{
    if (empty($_SESSION['flash_messages'])) {
        return;
    }

    $colors = [
        'success' => 'alert-success',
        'error' => 'alert-danger',
        'warning' => 'alert-warning',
        'info' => 'alert-info'
    ];

    foreach ($_SESSION['flash_messages'] as $message) {
        $type = $message['type'];
        $class = $colors[$type] ?? 'alert-info';

        echo '<div class="alert ' . $class . ' alert-dismissible fade show" role="alert">';
        echo htmlspecialchars($message['message']);
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
        echo '</div>';
    }

    if ($clear) {
        unset($_SESSION['flash_messages']);
    }
}

/**
 * Data Handling & Security Functions
 */

/**
 * Sanitize input data
 * @param mixed $data Input data
 * @param string $type Type of sanitization (string, int, float, email, etc.)
 * @return mixed Sanitized data
 */
function sanitize_input($data, string $type = 'string')
{
    if (is_array($data)) {
        return array_map('sanitize_input', $data);
    }

    switch ($type) {
        case 'int':
            return (int) filter_var($data, FILTER_SANITIZE_NUMBER_INT);
        case 'float':
            return (float) filter_var($data, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        case 'email':
            return filter_var(trim($data), FILTER_SANITIZE_EMAIL);
        case 'url':
            return filter_var(trim($data), FILTER_SANITIZE_URL);
        case 'string':
        default:
            return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
    }
}

/**
 * Generate CSRF token
 * @return string Generated token
 */
function generate_csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validate CSRF token
 * @param string $token Token to validate
 * @return bool True if valid
 */
function validate_csrf_token(string $token): bool
{
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Database Helper Functions
 */

/**
 * Get pagination parameters from request
 * @param int $default_per_page Default items per page
 * @return array [current_page, per_page, offset]
 */
function get_pagination_params(int $default_per_page = 10): array
{
    $current_page = max(1, $_GET['page'] ?? 1);
    $per_page = max(1, $_GET['per_page'] ?? $default_per_page);
    $offset = ($current_page - 1) * $per_page;

    return [$current_page, $per_page, $offset];
}

/**
 * Get user's full name by ID
 * @param PDO $pdo Database connection
 * @param int $user_id User ID
 * @return string|null User's full name or null if not found
 */
function get_user_fullname(PDO $pdo, int $user_id): ?string
{
    try {
        $stmt = $pdo->prepare("SELECT nama_lengkap FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        return $stmt->fetchColumn();
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        return null;
    }
}

/**
 * Get all document types from database
 * @param PDO $pdo Database connection
 * @return array List of document types
 */
function get_document_types(PDO $pdo): array
{
    try {
        $stmt = $pdo->query("SELECT DISTINCT jenis_dokumen FROM dokumen");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        return ['KTP', 'KK', 'Surat Domisili', 'Akte Kelahiran', 'Surat Kematian'];
    }
}

/**
 * File Handling Functions
 */

/**
 * Validate uploaded file
 * @param array $file $_FILES array element
 * @param array $allowed_types Allowed MIME types
 * @param int $max_size Maximum file size in bytes
 * @return array [bool $valid, string $message]
 */
function validate_uploaded_file(array $file, array $allowed_types, int $max_size = 2097152): array
{
    // Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors = [
            UPLOAD_ERR_INI_SIZE => 'File terlalu besar',
            UPLOAD_ERR_FORM_SIZE => 'File terlalu besar',
            UPLOAD_ERR_PARTIAL => 'File hanya terunggah sebagian',
            UPLOAD_ERR_NO_FILE => 'Tidak ada file yang diunggah',
            UPLOAD_ERR_NO_TMP_DIR => 'Folder temp tidak ada',
            UPLOAD_ERR_CANT_WRITE => 'Gagal menulis file',
            UPLOAD_ERR_EXTENSION => 'Upload dihentikan oleh ekstensi'
        ];
        return [false, $errors[$file['error']] ?? 'Error upload tidak diketahui'];
    }

    // Check file size
    if ($file['size'] > $max_size) {
        return [false, 'Ukuran file melebihi batas maksimal'];
    }

    // Check file type
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);

    if (!in_array($mime, $allowed_types)) {
        return [false, 'Tipe file tidak diizinkan'];
    }

    return [true, 'File valid'];
}

/**
 * Utility Functions
 */

/**
 * Generate random string
 * @param int $length Length of string
 * @return string Generated string
 */
function generate_random_string(int $length = 16): string
{
    $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $result = '';
    for ($i = 0; $i < $length; $i++) {
        $result .= $chars[rand(0, strlen($chars) - 1)];
    }
    return $result;
}

/**
 * Get current URL with query params
 * @param array $new_params New query parameters to add/update
 * @return string Current URL with modified query params
 */
function get_current_url(array $new_params = []): string
{
    $url = parse_url($_SERVER['REQUEST_URI']);
    $query = [];

    if (isset($url['query'])) {
        parse_str($url['query'], $query);
    }

    $query = array_merge($query, $new_params);

    return $url['path'] . '?' . http_build_query($query);
}

/**
 * Debugging Functions
 */

/**
 * Pretty print variable for debugging
 * @param mixed $var Variable to debug
 * @param bool $return Whether to return the output
 * @return string|null Debug output if $return is true
 */
function debug($var, bool $return = false): ?string
{
    $output = '<pre>' . print_r($var, true) . '</pre>';

    if ($return) {
        return $output;
    }

    echo $output;
    return null;
}
function createSlug($text) {
    // Replace non-letter or non-digit characters with -
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    
    // Transliterate
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    
    // Remove unwanted characters
    $text = preg_replace('~[^-\w]+~', '', $text);
    
    // Trim
    $text = trim($text, '-');
    
    // Remove duplicate -
    $text = preg_replace('~-+~', '-', $text);
    
    // Convert to lowercase
    $text = strtolower($text);
    
    if (empty($text)) {
        return 'n-a';
    }
    
    return $text;
}