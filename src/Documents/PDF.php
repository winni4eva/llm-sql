<?php
namespace Winnipass\AiSql\Documents;

use Dompdf\Dompdf;
use Illuminate\Support\Facades\Storage;

class PDF {
    protected $dompdf;
    protected $path = '/var/www/llm-sql/storage';
    //protected $html;
    //protected $filename = "report.pdf";
    public function __construct(protected $html, protected $filename, protected $options = []) {
        $this->dompdf = new Dompdf($options);
    }

    public function generate() {
        $this->dompdf->loadHtml($this->html);
        $this->dompdf->setPaper('A4', 'portrait');
        $this->dompdf->render();

        file_put_contents($this->path . '/' . $this->filename, $this->dompdf->output());

        echo "PDF generated: <a href='/generated-pdfs/$this->filename'>Download</a>";
    }
}