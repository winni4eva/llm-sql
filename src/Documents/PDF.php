<?php
use Dompdf\Dompdf;

class PDF {
    protected $dompdf;
    public function __construct(protected $html, protected $filename, protected $options = []) {
        $this->dompdf = new Dompdf($options);
    }

    public function generate() {
        $this->dompdf->loadHtml($this->html);
        $this->dompdf->render();
        $this->dompdf->stream($this->filename);
    }
}