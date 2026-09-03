<?php
/* ═══════════════════════════════════════════════════════════════
   IUC Edu — Homepage v3.0
   ═══════════════════════════════════════════════════════════════ */

require_once __DIR__ . '/includes/functions.php';

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/includes/analytics.php';

// ── Contact Form Handler ──────────────────────────────────
$formSuccess = !empty($_SESSION['contact_success']);
unset($_SESSION['contact_success']);
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
        } elseif (!isset($conn) || !$conn) {
            $formError = 'Enquiry service is temporarily unavailable. Please call or WhatsApp us.';
            error_log('Enquiry insert skipped: database unavailable.');
        } else {
            $cleanField = static function ($key, $max = 512) {
                $value = trim((string)($_POST[$key] ?? ''));
                return mb_substr($value, 0, $max);
            };
            $pageUrl = $cleanField('page_url');
            $landingPage = $cleanField('landing_page');
            $referrer = $cleanField('referrer');
            $visitorId = $cleanField('visitor_id', 36);
            $sessionId = $cleanField('session_id', 36);
            $utmSource = $cleanField('utm_source', 100);
            $utmMedium = $cleanField('utm_medium', 100);
            $utmCampaign = $cleanField('utm_campaign', 100);
            $utmContent = $cleanField('utm_content', 100);
            $utmTerm = $cleanField('utm_term', 255);
            if (!an_valid_uuid($visitorId)) $visitorId = null;
            if (!an_valid_uuid($sessionId)) $sessionId = null;

            $stmt = $conn->prepare("INSERT INTO enquiries
                (full_name, phone, email, course, message, page_url, landing_page, referrer,
                 visitor_id, session_id, utm_source, utm_medium, utm_campaign, utm_content, utm_term)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            if (!$stmt) {
                $formError = 'We could not save your enquiry right now. Please call or WhatsApp us.';
                error_log('Enquiry prepare failed: ' . $conn->error);
            } else {
                $stmt->bind_param('sssssssssssssss', $name, $phone, $email, $course, $message, $pageUrl,
                    $landingPage, $referrer, $visitorId, $sessionId, $utmSource, $utmMedium,
                    $utmCampaign, $utmContent, $utmTerm);
                $saved = $stmt->execute();
                $enquiryId = $saved ? (int)$stmt->insert_id : 0;
                $insertError = $stmt->error;
                $stmt->close();

                if (!$saved) {
                    $formError = 'We could not save your enquiry right now. Please call or WhatsApp us.';
                    error_log('Enquiry insert failed: ' . $insertError);
                } else {
                    if (an_ensure_tables($conn)) {
                        $eventType = 'contact_form';
                        $eventLabel = 'Enquiry #' . $enquiryId . ' saved';
                        $eventTime = date('Y-m-d H:i:s');
                        $event = $conn->prepare("INSERT INTO analytics_events
                            (visitor_id, session_id, event_type, event_label, event_value, page_url, enquiry_id, created_at)
                            VALUES (?, ?, ?, ?, 1, ?, ?, ?)");
                        if ($event) {
                            $event->bind_param('sssssis', $visitorId, $sessionId, $eventType, $eventLabel, $pageUrl, $enquiryId, $eventTime);
                            if (!$event->execute()) error_log('Enquiry analytics event failed: ' . $event->error);
                            $event->close();
                        }
                    }

                    $safeMailName = str_replace(["\r", "\n"], ' ', $name);
                    $to = SITE_EMAIL;
                    $subject = "New Inquiry from $safeMailName – " . SITE_NAME;
                    $body = "Enquiry ID: $enquiryId\nName: $name\nPhone: $phone\nEmail: $email\nCourse: $course\nMessage: $message\nPage: $pageUrl\nLanding page: $landingPage\nUTM campaign: $utmCampaign";
                    $headers = "From: noreply@" . str_replace('www.', '', parse_url(SITE_URL, PHP_URL_HOST));
                    @mail($to, $subject, $body, $headers);

                    $_SESSION['contact_success'] = true;
                    $_SESSION['captcha'] = rand(1000, 9999);
                    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                    header('Location: ' . BASE_URL . '/#contact', true, 303);
                    exit;
                }
            }
        }
    }
}

$pageTitle = 'Computer & IT Training Institute in Chennai | IUC Edu';
$metaDesc = 'IUC Edu is a computer and IT training institute in Chennai offering programming, coding, software and beginner IT courses with practical training and placement support.';
$ogTitle = $pageTitle;
$ogDesc = $metaDesc;
$ogImage = SITE_URL . '/assets/images/about-education.jpg';
$ogImageAlt = 'Students learning technology skills at IUC Edu in Chennai';
$canonical = '';
$structuredData = [
    [
        '@type' => 'FAQPage',
        '@id' => SITE_URL . '/#faq',
        'mainEntity' => array_map(static function ($faq) {
            return [
                '@type' => 'Question',
                'name' => $faq['q'],
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => $faq['a'],
                ],
            ];
        }, $faqs),
    ],
];

require_once __DIR__ . '/includes/header.php';
?>

<?php require __DIR__ . '/includes/hero.php'; ?>
<?php require __DIR__ . '/includes/about.php'; ?>
<?php require __DIR__ . '/includes/courses.php'; ?>
<?php require __DIR__ . '/includes/seo-training.php'; ?>
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
