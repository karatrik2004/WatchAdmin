<?php

namespace App\Pdf;

use TCPDF;

class CustomPdf extends TCPDF
{
    public $invoiceNo; // Public variable to store invoice number
    public $amountDue; // Public variable to store amount due
    public $currency; // Public variable to store amount due

    // Footer method to add dynamic data and page number
    public function Footer()
    {
        $this->SetY(-20); // position footer 20mm from bottom
        $this->SetFont('helvetica', '', 10); // normal font, 10px size

        // Use the dynamic values set in the controller
        $invoiceNo = $this->invoiceNo;
        $amountDue = $this->currency=='aud' ? '$'.$this->amountDue : $this->amountDue;



        // Page number should be at the end
        $footerText = "
            <div style='text-align: center; font-size: 10px;'>
                <span style='margin-right: 20px;'>Invoice No: <strong>" . $invoiceNo . "</strong> | Amount Due: <strong>" . $amountDue . "</strong></span>
                <br> <br><span style='text-align:right;position: absolute; right: 10px;'>Page " . $this->getAliasNumPage() . " of " . $this->getAliasNbPages() . "</span>
            </div>
        ";

        // Write HTML content to the footer
        $this->writeHTMLCell(0, 0, '', '', $footerText, 0, 1, false, true, 'C');
    }
}
