<?php
// lib/simple_pdf.php - Generator PDF minimal (tanpa library eksternal)
// Mendukung teks + embed gambar JPEG. Cukup untuk poster WANTED.

class SimplePDF
{
    public $w = 595.28;   // A4 width (pt)
    public $h = 841.89;   // A4 height (pt)

    private $n = 0;             // total object count
    private $content = '';      // content stream halaman
    private $imageObjects = []; // {id, data, w, h, bpc, filters}
    private $contentId;
    private $fontId;
    private $pageId;
    private $pagesId;

    public function __construct($w = 595.28, $h = 841.89)
    {
        $this->w = $w;
        $this->h = $h;
        // 1 = catalog, 2 = pages, 3 = page, 4 = font, 5 = content
        $this->n = 5;
        $this->contentId = 5;
        $this->fontId = 4;
        $this->pageId = 3;
        $this->pagesId = 2;
    }

    private function esc($s)
    {
        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $s);
    }

    private function addCmd($cmd)
    {
        $this->content .= $cmd . "\n";
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

        $id = ++$this->n;
        $this->imageObjects[$id] = [
            'stream' => $data,
            'w' => $imgW,
            'h' => $imgH,
            'bpc' => 8,
            'filters' => '/DCTDecode',
        ];
        $this->addCmd(sprintf(
            "q %.2f 0 0 %.2f %.2f %.2f cm /Im%d Do Q",
            $width, $height, $x, $this->h - $y - $height, $id
        ));
    }

    public function output($filename = null)
    {
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
        $emit(1, "<< /Type /Catalog /Pages 2 0 R >>");

        // 2: Pages
        $emit(2, sprintf("<< /Type /Pages /Kids [%d 0 R] /Count 1 >>", $this->pageId));

        // 3: Page
        $xobjects = '/XObject <<';
        foreach ($this->imageObjects as $id => $obj) {
            $xobjects .= " /Im$id $id 0 R";
        }
        $xobjects .= ' >>';
        $emit(3, sprintf(
            "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 %.2f %.2f] /Resources << /Font << /F1 %d 0 R >> %s >> /Contents %d 0 R >>",
            $this->w, $this->h, $this->fontId, $xobjects, $this->contentId
        ));

        // 4: Font
        $emit(4, "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>");

        // 5: Content stream
        $emit(5, sprintf("<< /Length %d >>", strlen($this->content)), $this->content);

        // 6+: Images
        foreach ($this->imageObjects as $id => $obj) {
            $body = sprintf(
                "<< /Type /XObject /Subtype /Image /Width %d /Height %d /ColorSpace /DeviceRGB /BitsPerComponent %d /Filter %s /Length %d >>",
                $obj['w'], $obj['h'], $obj['bpc'], $obj['filters'], strlen($obj['stream'])
            );
            $emit($id, $body, $obj['stream']);
        }

        // xref
        $count = $this->n + 1;
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
