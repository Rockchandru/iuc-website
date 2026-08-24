<?php
/* ═══════════════════════════════════════════════════════════════
   IUC Edu — Course Syllabus PDF Download (pure PHP, no deps)
   Professional branded layout: header band, info cards, colored
   section headers, module blocks, contact band, page footers.
   ═══════════════════════════════════════════════════════════════ */

require_once __DIR__ . '/includes/functions.php';

$slug = isset($_GET['course']) ? preg_replace('/[^a-z0-9-]/', '', $_GET['course']) : '';
$course = getCourseBySlug($slug);

if (!$course) {
    header('HTTP/1.0 404 Not Found');
    echo 'Course not found.';
    exit;
}

/* ── Text helpers ─────────────────────────────────────── */

function pdf_clean($s) {
    $repl = [
        '₹' => 'Rs. ', '–' => '-', '—' => '-', '“' => '"', '”' => '"',
        '‘' => "'", '’' => "'", '•' => '-', '·' => '.', '⭐' => '*',
        '◆' => '*', '●' => '-', '▶' => '>', '★' => '*', '═' => '=',
        '─' => '-', '│' => '|', '║' => '|', '✓' => '', '&' => 'and',
    ];
    $s = strtr($s, $repl);
    $s = preg_replace('/[^\x20-\x7E]/', '', $s);
    return trim($s);
}

function pdf_escape($s) {
    return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $s);
}

function pdf_rgb($hex) {
    return [
        hexdec(substr($hex, 1, 2)) / 255,
        hexdec(substr($hex, 3, 2)) / 255,
        hexdec(substr($hex, 5, 2)) / 255,
    ];
}

function pdf_widths() {
    return [
        ' ' => 278, '!' => 278, '"' => 355, '#' => 556, '$' => 556, '%' => 889,
        '&' => 667, "'" => 191, '(' => 333, ')' => 333, '*' => 389, '+' => 584,
        ',' => 278, '-' => 333, '.' => 278, '/' => 278, ':' => 278, ';' => 278,
        '<' => 584, '=' => 584, '>' => 584, '?' => 556, '@' => 1015,
        '0' => 556, '1' => 556, '2' => 556, '3' => 556, '4' => 556,
        '5' => 556, '6' => 556, '7' => 556, '8' => 556, '9' => 556,
        'A' => 667, 'B' => 667, 'C' => 722, 'D' => 722, 'E' => 667, 'F' => 611,
        'G' => 778, 'H' => 722, 'I' => 278, 'J' => 500, 'K' => 667, 'L' => 556,
        'M' => 833, 'N' => 722, 'O' => 778, 'P' => 667, 'Q' => 778, 'R' => 722,
        'S' => 667, 'T' => 611, 'U' => 722, 'V' => 667, 'W' => 944, 'X' => 667,
        'Y' => 667, 'Z' => 611,
        'a' => 556, 'b' => 556, 'c' => 500, 'd' => 556, 'e' => 556, 'f' => 278,
        'g' => 556, 'h' => 556, 'i' => 222, 'j' => 222, 'k' => 500, 'l' => 222,
        'm' => 833, 'n' => 556, 'o' => 556, 'p' => 556, 'q' => 556, 'r' => 333,
        's' => 500, 't' => 278, 'u' => 556, 'v' => 500, 'w' => 722, 'x' => 500,
        'y' => 500, 'z' => 500,
    ];
}

function pdf_strwidth($s, $size) {
    $W = pdf_widths();
    $total = 0;
    for ($i = 0, $len = strlen($s); $i < $len; $i++) {
        $c = $s[$i];
        $total += isset($W[$c]) ? $W[$c] : 556;
    }
    return $total / 1000 * $size;
}

function pdf_wrap($text, $size, $maxWidth) {
    $words = preg_split('/\s+/', trim($text));
    $lines = [];
    $line = '';
    foreach ($words as $word) {
        $test = $line === '' ? $word : $line . ' ' . $word;
        if (pdf_strwidth($test, $size) <= $maxWidth) {
            $line = $test;
        } else {
            if ($line !== '') $lines[] = $line;
            $line = $word;
        }
    }
    if ($line !== '') $lines[] = $line;
    return $lines;
}

/* ── Professional PDF builder ─────────────────────────── */

class PdfDoc {
    const W = 595.28;
    const H = 841.89;
    const ML = 44;
    const MR = 44;
    const TOP = 46;
    const BOTTOM = 46;

    private $content = '';
    private $pages = [];
    private $y = 0;
    private $cr = 0.1176;
    private $cg = 0.1608;
    private $cb = 0.2196;

    public function __construct() {
        $this->y = self::H - self::TOP;
    }

    private function push($ops) {
        $this->content .= $ops . "\n";
    }

    private function newPage() {
        if ($this->content !== '') $this->pages[] = $this->content;
        $this->content = '';
        $this->y = self::H - self::TOP;
    }

    private function ensure($h) {
        if ($this->y - $h < self::BOTTOM) $this->newPage();
    }

    private function txtColor($hex) {
        list($r, $g, $b) = pdf_rgb($hex);
        return sprintf('%.3f %.3f %.3f rg ', $r, $g, $b);
    }

    /* ── Flowing content ─────────────────────────────── */

    public function write($text, $font = 'F1', $size = 9.5, $indent = 0, $hex = '#1E293B') {
        $lines = pdf_wrap(pdf_clean($text), $size, self::W - self::ML - self::MR - $indent);
        foreach ($lines as $line) {
            $this->ensure($size * 1.3);
            $this->push(sprintf(
                'BT %s/%s %.1f Tf %.1f %.1f Td (%s) Tj ET',
                $this->txtColor($hex), $font, $size, self::ML + $indent, $this->y, pdf_escape($line)
            ));
            $this->y -= $size * 1.34;
        }
    }

    public function blank($h) {
        $this->ensure($h);
        $this->y -= $h;
    }

    public function sectionTitle($title) {
        $this->ensure(24);
        $this->y -= 3;
        $this->box(self::ML, $this->y - 3.2, 5, 5, '#06B6D4');
        $this->push(sprintf(
            'BT %s/F2 11.5 Tf %.1f %.1f Td (%s) Tj ET',
            $this->txtColor('#1D4ED8'), self::ML + 11, $this->y, pdf_escape(pdf_clean($title))
        ));
        $this->y -= 15;
        $this->line(self::ML, $this->y, self::W - self::MR, '#D9E2EC', 0.7);
        $this->y -= 9;
    }

    public function bullet($text, $size = 9.5, $indent = 12) {
        $lines = pdf_wrap(pdf_clean($text), $size, self::W - self::ML - self::MR - $indent);
        foreach ($lines as $i => $line) {
            $this->ensure($size * 1.3);
            if ($i === 0) {
                $this->box(self::ML + 1.2, $this->y - 2.4, 2.8, 2.8, '#06B6D4');
            }
            $this->push(sprintf(
                'BT %s/F1 %.1f Tf %.1f %.1f Td (%s) Tj ET',
                $this->txtColor('#334155'), $size, self::ML + $indent, $this->y, pdf_escape($line)
            ));
            $this->y -= $size * 1.34;
        }
    }

    public function module($num, $name, $desc) {
        $lines = pdf_wrap(pdf_clean($desc), 9, self::W - self::ML - self::MR - 14);
        $this->ensure(16 + count($lines) * 9 * 1.34);
        $label = 'MODULE ' . str_pad($num, 2, '0', STR_PAD_LEFT);
        $this->push(sprintf(
            'BT %s/F2 8 Tf %.1f %.1f Td (%s) Tj ET',
            $this->txtColor('#0891B2'), self::ML + 12, $this->y, pdf_escape($label)
        ));
        $this->push(sprintf(
            'BT %s/F2 10 Tf %.1f %.1f Td (%s) Tj ET',
            $this->txtColor('#0F172A'),
            self::ML + 12 + $this->textWidth($label, 8) + 10,
            $this->y,
            pdf_escape(pdf_clean($name))
        ));
        $this->y -= 13;
        foreach ($lines as $line) {
            $this->push(sprintf(
                'BT %s/F1 9 Tf %.1f %.1f Td (%s) Tj ET',
                $this->txtColor('#475569'), self::ML + 12, $this->y, pdf_escape($line)
            ));
            $this->y -= 9 * 1.34;
        }
        $this->y -= 4;
    }

    public function contactBand() {
        $this->ensure(74);
        $this->y -= 4;
        $h = 62;
        $x = self::ML;
        $w = self::W - self::ML - self::MR;
        $yBottom = $this->y - $h;
        $this->box($x, $yBottom, $w, $h, '#1e3a8a');
        $this->box($x, $yBottom, $w, 3, '#06B6D4');
        $this->textAt('READY TO BUILD YOUR TECH CAREER?', $x + 18, $yBottom + $h - 18, 'F2', 12, '#FFFFFF');
        $this->textAt('Call +91 ' . SITE_PHONE . '   |   ' . SITE_EMAIL, $x + 18, $yBottom + $h - 33, 'F1', 9.5, '#E2E8F0');
        $this->textAt(SITE_URL, $x + 18, $yBottom + $h - 47, 'F1', 9.5, '#93C5FD');
        $this->y = $yBottom - 8;
    }

    /* ── Absolute elements (bottom-origin y) ─────────── */

    public function box($x, $yBottom, $w, $h, $hex) {
        list($r, $g, $b) = pdf_rgb($hex);
        $this->push(sprintf('%.3f %.3f %.3f rg %.1f %.1f %.1f %.1f re f', $r, $g, $b, $x, $yBottom, $w, $h));
    }

    public function line($x1, $y, $x2, $hex = '#D9E2EC', $w = 0.8) {
        list($r, $g, $b) = pdf_rgb($hex);
        $this->push(sprintf('%.3f %.3f %.3f RG %.1f w %.1f %.1f m %.1f %.1f l S', $r, $g, $b, $w, $x1, $y, $x2, $y));
    }

    public function textAt($text, $x, $yBottom, $font, $size, $hex) {
        $this->push(sprintf(
            'BT %s/%s %.1f Tf %.1f %.1f Td (%s) Tj ET',
            $this->txtColor($hex), $font, $size, $x, $yBottom, pdf_escape(pdf_clean($text))
        ));
    }

    public function textWidth($text, $size) {
        return pdf_strwidth(pdf_clean($text), $size);
    }

    /* ── First-page branded header ───────────────────── */

    public function header($course) {
        $bandH = 44;
        $this->box(0, self::H - $bandH, self::W, $bandH, '#1e3a8a');
        $this->box(0, self::H - $bandH, self::W, 3, '#06B6D4');

        $brand = strtoupper(SITE_NAME);
        $this->textAt($brand, self::ML, self::H - 28, 'F2', 19, '#FFFFFF');
        $sub = 'COURSE SYLLABUS & FULL DETAILS';
        $this->textAt($sub, self::W - self::MR - $this->textWidth($sub, 9), self::H - 20, 'F2', 9, '#BFDBFE');
        $this->textAt('PROFESSIONAL IT TRAINING INSTITUTE', self::ML, self::H - 41, 'F1', 7.5, '#93C5FD');

        $this->textAt($course['title'], self::ML, self::H - $bandH - 22, 'F2', 17, '#0F172A');
        $this->box(self::ML, self::H - $bandH - 29, 58, 3, '#06B6D4');
        $this->textAt('Complete course details - curriculum, eligibility, projects, career outcomes and more.',
            self::ML, self::H - $bandH - 38, 'F1', 8.5, '#64748B');

        $facts = [
            ['Duration', $course['duration']],
            ['Mode', $course['mode']],
            ['Level', $course['level']],
            ['Price', $course['price'] . ' (' . $course['emi'] . ')'],
            ['Rating', $course['rating'] . ' / 5'],
            ['Enrolled', $course['enrolled'] . ' students'],
        ];
        $gap = 10;
        $cols = 3;
        $cardW = (self::W - self::ML - self::MR - ($cols - 1) * $gap) / $cols;
        $cardH = 38;
        $rowGap = 10;
        $cardsTop = self::H - $bandH - 48;

        foreach ($facts as $i => $f) {
            $col = $i % $cols;
            $row = intdiv($i, $cols);
            $x = self::ML + $col * ($cardW + $gap);
            $yBottom = $cardsTop - $row * ($cardH + $rowGap) - $cardH;
            $this->box($x, $yBottom, $cardW, $cardH, '#F1F5F9');
            $this->box($x, $yBottom, 3, $cardH, '#06B6D4');
            $this->textAt(strtoupper(pdf_clean($f[0])), $x + 10, $yBottom + $cardH - 11, 'F1', 6.8, '#64748B');
            $this->textAt($f[1], $x + 10, $yBottom + 8, 'F2', 9.5, '#0F172A');
        }

        $this->y = $cardsTop - 2 * ($cardH + $rowGap) + $rowGap - 12;
    }

    /* ── Footers & assembly ──────────────────────────── */

    private function footerOps($i, $n) {
        $ops = sprintf(
            "0.85 0.88 0.92 RG 0.7 w %.1f %.1f m %.1f %.1f l S\n",
            self::ML, 34, self::W - self::MR, 34
        );
        $left = 'IUC Edu   |   info@iucedu.com   |   +91 ' . SITE_PHONE;
        $ops .= sprintf(
            'BT %s/F1 7 Tf %.1f %.1f Td (%s) Tj ET' . "\n",
            $this->txtColor('#94A3B8'), self::ML, 24, pdf_escape($left)
        );
        $pg = 'Page ' . $i . ' of ' . $n;
        $ops .= sprintf(
            'BT %s/F1 7 Tf %.1f %.1f Td (%s) Tj ET' . "\n",
            $this->txtColor('#94A3B8'), self::W - self::MR - $this->textWidth($pg, 7), 24, pdf_escape($pg)
        );
        return $ops;
    }

    public function build() {
        if ($this->content !== '') $this->pages[] = $this->content;
        if (count($this->pages) === 0) $this->pages[] = '';

        $n = count($this->pages);
        for ($i = 0; $i < $n; $i++) {
            $this->pages[$i] .= "\n" . $this->footerOps($i + 1, $n);
        }

        $f1 = 3 + 2 * $n;
        $f2 = 4 + 2 * $n;
        $total = $f2;

        $objects = [];
        $objects[1] = '<< /Type /Catalog /Pages 2 0 R >>';

        $kids = [];
        for ($i = 0; $i < $n; $i++) {
            $pageObj = 3 + $i * 2;
            $contentObj = $pageObj + 1;
            $kids[] = $pageObj . ' 0 R';
            $objects[$pageObj] = sprintf(
                '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 %.2f %.2f] ' .
                '/Resources << /Font << /F1 %d 0 R /F2 %d 0 R >> >> /Contents %d 0 R >>',
                self::W, self::H, $f1, $f2, $contentObj
            );
            $objects[$contentObj] = sprintf(
                "<< /Length %d >>\nstream\n%s\nendstream",
                strlen($this->pages[$i]), $this->pages[$i]
            );
        }

        $objects[2] = '<< /Type /Pages /Kids [' . implode(' ', $kids) . '] /Count ' . $n . ' >>';
        $objects[$f1] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>';
        $objects[$f2] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >>';

        $pdf = "%PDF-1.4\n";
        $offsets = [];
        for ($i = 1; $i <= $total; $i++) {
            $offsets[$i] = strlen($pdf);
            $pdf .= $i . " 0 obj\n" . $objects[$i] . "\nendobj\n";
        }
        $xrefPos = strlen($pdf);
        $pdf .= "xref\n0 " . ($total + 1) . "\n";
        $pdf .= "0000000000 65535 f \n";
        for ($i = 1; $i <= $total; $i++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
        }
        $pdf .= "trailer\n<< /Size " . ($total + 1) . " /Root 1 0 R >>\nstartxref\n" . $xrefPos . "\n%%EOF";

        return $pdf;
    }
}

/* ── Build the syllabus document ──────────────────────── */

$doc = new PdfDoc();
$doc->header($course);
$doc->blank(2);

$doc->sectionTitle('Program Overview');
$doc->write($course['description']);
$doc->blank(8);

$doc->sectionTitle('Eligibility');
$doc->write($course['eligibility']);
$doc->blank(8);

$doc->sectionTitle('Course Highlights');
foreach ($course['highlights'] as $h) {
    $doc->bullet($h);
}
$doc->blank(8);

$doc->sectionTitle('Module-Wise Curriculum');
foreach ($course['syllabus'] as $i => $m) {
    $doc->module($i + 1, $m[0], $m[1]);
}
$doc->blank(8);

$doc->sectionTitle('Hands-On Projects');
foreach ($course['projects'] as $i => $p) {
    $doc->bullet(($i + 1) . '. ' . $p);
}
$doc->blank(8);

$doc->sectionTitle('Technologies & Tools');
$doc->write(implode(', ', $course['technologies']), 'F1', 9.5);
$doc->write(implode(', ', $course['tools']), 'F1', 9.5);
$doc->blank(8);

$doc->sectionTitle('Career Opportunities');
foreach ($course['career'] as $c) {
    $doc->bullet($c);
}
$doc->blank(8);

$doc->sectionTitle('Certification');
$doc->write($course['certification']);
$doc->blank(12);

$doc->contactBand();

$pdf = $doc->build();

header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . $course['slug'] . '-syllabus-iuc-edutech.pdf"');
header('Content-Length: ' . strlen($pdf));
header('Cache-Control: no-store');

echo $pdf;
