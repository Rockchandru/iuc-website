<?php
/* ═══════════════════════════════════════════════════════════════
   IUC Edu — Homepage v3.0
   ═══════════════════════════════════════════════════════════════ */

require_once __DIR__ . '/includes/functions.php';

require_once __DIR__ . '/db.php';

// ── Contact Form Handler ──────────────────────────────────
$formSuccess = false;
$formError   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['contact_submit'])) {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $formError = 'Security validation failed. Please refresh and try again.';
    } else {
        $name    = trim($_POST['name']    ?? '');
        $phone   = trim($_POST['phone']   ?? '');
        $email   = trim($_POST['email']   ?? '');
        $course  = trim($_POST['course']  ?? '');
        $message = trim($_POST['message'] ?? '');
        $captcha = trim($_POST['captcha'] ?? '');

        if (!$name || !$phone || !$email || !$course) {
            $formError = 'Please fill in all required fields.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $formError = 'Please enter a valid email address.';
        } elseif ($captcha != $_SESSION['captcha']) {
            $formError = 'Invalid security code. Please try again.';
            $_SESSION['captcha'] = rand(1000, 9999);
        } else {
            if (isset($conn) && $conn) {
                $stmt = $conn->prepare("INSERT INTO enquiries (full_name, phone, email, course, message) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("sssss", $name, $phone, $email, $course, $message);
                if ($stmt->execute()) {
                    $formSuccess = true;
                }
                $stmt->close();
            }
            // Email notification
            $to      = SITE_EMAIL;
            $subject = "New Inquiry from $name – " . SITE_NAME;
            $body    = "Name: $name\nPhone: $phone\nEmail: $email\nCourse: $course\nMessage: $message";
            $headers = "From: noreply@" . str_replace('www.', '', parse_url(SITE_URL, PHP_URL_HOST));
            @mail($to, $subject, $body, $headers);

            $formSuccess = true;
            $_SESSION['captcha'] = rand(1000, 9999);
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            $_POST = [];
        }
    }
}

$pageTitle = 'IUC Edu – Premier IT Training Institute in Chennai | AI, ML, Full Stack, Data Science';
$metaDesc = 'IUC Edu – Chennai\'s most trusted IT training institute. 50+ programs in AI/ML, Full Stack, Data Science, Cyber Security, Cloud & Digital Marketing with 98% placement support.';

require_once __DIR__ . '/includes/header.php';
?>

<?php require __DIR__ . '/includes/hero.php'; ?>
<?php require __DIR__ . '/includes/about.php'; ?>
<?php require __DIR__ . '/includes/courses.php'; ?>
<?php require __DIR__ . '/includes/ai-course.php'; ?>
<?php require __DIR__ . '/includes/why-choose.php'; ?>
<?php require __DIR__ . '/includes/admissions.php'; ?>
<?php require __DIR__ . '/includes/certificates.php'; ?>
<?php require __DIR__ . '/includes/batches.php'; ?>
<?php require __DIR__ . '/includes/technologies.php'; ?>
<?php require __DIR__ . '/includes/projects.php'; ?>
<?php require __DIR__ . '/includes/internship.php'; ?>
<?php require __DIR__ . '/includes/placement.php'; ?>
<?php require __DIR__ . '/includes/testimonials.php'; ?>
<?php require __DIR__ . '/includes/companies.php'; ?>
<?php require __DIR__ . '/includes/faq.php'; ?>
<?php require __DIR__ . '/includes/blog.php'; ?>
<?php require __DIR__ . '/includes/contact.php'; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
