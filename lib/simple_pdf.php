<?php
// lib/simple_pdf.php - Generator PDF minimal (tanpa library eksternal)
// Mendukung teks + embed gambar JPEG + multi-halaman.
// API: text(), textCenter(), imageJpeg(), wrap(), textWidth(), newPage(), output()

class SimplePDF
{
    public $w = 595.28;   // A4 width (pt)
    public $h = 841.89;   // A4 height (pt)

    private $pages = [];        // kumpulan content stream per halaman
    private $cur = '';          // content stream halaman aktif
    private $imageObjects = []; // {stream, w, h, bpc, filters}

    public function __construct($w = 595.28, $h = 841.89)
    {
        $this->w = $w;
        $this->h = $h;
    }

    // Tutup halaman aktif (jika ada isi) dan mulai halaman baru
    public function newPage()
    {
        if ($this->cur !== '') {
            $this->pages[] = $this->cur;
            $this->cur = '';
        }
    }

    private function esc($s)
    {
        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $s);
    }

    private function addCmd($cmd)
    {
        $this->cur .= $cmd . "\n";
    }

    // Estimasi lebar teks dalam pt (Helvetica, factor rata-rata)
    public function textWidth($text, $size)
    {
        return mb_strlen($text) * $size * 0.5;
    }

    // Pecah teks jadi baris-baris yang muat dalam maxWidth (pt)
    public function wrap($text, $size, $maxWidth)
    {
        $text = trim($text);
        if ($text === '') return [''];
        $words = preg_split('/\s+/u', $text);
        $lines = [];
        $line = '';
        foreach ($words as $w) {
            $cand = $line === '' ? $w : $line . ' ' . $w;
            if ($this->textWidth($cand, $size) <= $maxWidth || $line === '') {
                $line = $cand;
            } else {
                $lines[] = $line;
                $line = $w;
            }
        }
        if ($line !== '') $lines[] = $line;
        return $lines;
    }

    // Teks di posisi (x,y) dari kiri-atas (pt)
    public function text($x, $y, $size, $text)
    {
        $this->addCmd(sprintf(
            "BT /F1 %.2f Tf %.2f %.2f Td (%s) Tj ET",
            $size, $x, $this->h - $y, $this->esc($text)
        ));
    }

    // Teks rata tengah pada x = w/2
    public function textCenter($y, $size, $text)
    {
        $this->text($this->w / 2, $y, $size, $text);
    }

    // Embed gambar JPEG pada posisi (x,y) kiri-atas, lebar width (pt)
    public function imageJpeg($path, $x, $y, $width)
    {
        $data = @file_get_contents($path);
        if ($data === false) return;

        $info = @getimagesize($path);
        $imgW = $info[0] ?? 100;
        $imgH = $info[1] ?? 100;
        $height = $width * $imgH / $imgW;

        $key = 'img' . (count($this->imageObjects) + 1);
        $this->imageObjects[$key] = [
            'stream' => $data,
            'w' => $imgW,
            'h' => $imgH,
            'bpc' => 8,
            'filters' => '/DCTDecode',
        ];
        $this->addCmd(sprintf(
            "q %.2f 0 0 %.2f %.2f %.2f cm /%s Do Q",
            $width, $height, $x, $this->h - $y - $height, $key
        ));
    }

    public function output($filename = null)
    {
        if ($this->cur !== '') {
            $this->pages[] = $this->cur;
            $this->cur = '';
        }

        $nPages = count($this->pages);
        if ($nPages === 0) $nPages = 1; // halaman kosong tetap valid

        // Alokasikan object id
        $pagesTreeId = 2;
        $pageIds = range(3, 2 + $nPages);
        $fontId = 3 + $nPages;
        $contentIds = range($fontId + 1, $fontId + $nPages);
        $imgKeys = array_keys($this->imageObjects);
        $imgIds = [];
        $next = $fontId + $nPages + 1;
        foreach ($imgKeys as $k) $imgIds[$k] = $next++;

        $pdf = "%PDF-1.4\n";
        $offsets = [];

        $emit = function ($oid, $body, $stream = null) use (&$pdf, &$offsets) {
            $offsets[$oid] = strlen($pdf);
            $pdf .= "$oid 0 obj\n";
            if ($stream !== null) {
                $body .= "\nstream\n" . $stream . "\nendstream";
            }
            $pdf .= $body . "\nendobj\n";
        };

        // 1: Catalog
        $emit(1, "<< /Type /Catalog /Pages $pagesTreeId 0 R >>");

        // 2: Pages tree
        $kids = implode(' ', array_map(fn($id) => "$id 0 R", $pageIds));
        $emit($pagesTreeId, "<< /Type /Pages /Kids [$kids] /Count $nPages >>");

        // 3..: halaman
        $xobjects = '/XObject <<';
        foreach ($imgIds as $k => $id) $xobjects .= " /$k $id 0 R";
        $xobjects .= ' >>';
        foreach ($pageIds as $i => $pid) {
            $emit($pid, sprintf(
                "<< /Type /Page /Parent $pagesTreeId 0 R /MediaBox [0 0 %.2f %.2f] /Resources << /Font << /F1 $fontId 0 R >> %s >> /Contents %d 0 R >>",
                $this->w, $this->h, $xobjects, $contentIds[$i]
            ));
        }

        // Font
        $emit($fontId, "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>");

        // Content streams
        foreach ($contentIds as $i => $cid) {
            $body = ($i < count($this->pages)) ? $this->pages[$i] : '';
            $emit($cid, sprintf("<< /Length %d >>", strlen($body)), $body);
        }

        // Images
        foreach ($imgIds as $k => $id) {
            $o = $this->imageObjects[$k];
            $emit($id, sprintf(
                "<< /Type /XObject /Subtype /Image /Width %d /Height %d /ColorSpace /DeviceRGB /BitsPerComponent %d /Filter %s /Length %d >>",
                $o['w'], $o['h'], $o['bpc'], $o['filters'], strlen($o['stream'])
            ), $o['stream']);
        }

        // xref
        $count = $next;
        $xrefPos = strlen($pdf);
        $pdf .= "xref\n0 $count\n0000000000 65535 f \n";
        for ($i = 1; $i < $count; $i++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$i] ?? 0);
        }
        $pdf .= "trailer\n<< /Size $count /Root 1 0 R >>\nstartxref\n$xrefPos\n%%EOF";

        if ($filename !== null) {
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="' . basename($filename) . '"');
            echo $pdf;
            return null;
        }
        return $pdf;
    }
}
