<div style="margin-bottom: 0px; display: flex; gap: 20px;">

    <div style="flex: 1;">
        <p><strong>Bank Details</strong></p>
        <p>Account Name: <?= htmlspecialchars($bankdetails[$bankname]['AccountName']) ?></p>
        <p>Account Number: <?= htmlspecialchars($bankdetails[$bankname]['AccountNumber']) ?></p>
        <p>Bank Name: <?= htmlspecialchars($bankdetails[$bankname]['Bank']) ?></p>
        <p>Branch: <?= htmlspecialchars($bankdetails[$bankname]['Branch']) ?></p>
        <?php
        if (!empty($bankdetails[$bankname]['UPI']))
            echo "<p>UPI ID: " . htmlspecialchars($bankdetails[$bankname]['UPI']) . "</p>";
        if (!empty($bankdetails[$bankname]['SwitftCode']))
            echo "<p>SWIFT Code: " . htmlspecialchars($bankdetails[$bankname]['SwitftCode']) . "</p>";
        if (!empty($bankdetails[$bankname]['IBAN']))
            echo "<p>IBAN: " . htmlspecialchars($bankdetails[$bankname]['IBAN']) . "</p>";
        if (!empty($bankdetails[$bankname]['IFSC']))
            echo "<p>IFSC Code: " . htmlspecialchars($bankdetails[$bankname]['IFSC']) . "</p>";
        ?>
    </div>

    <?php
    // Render UPI QR code if UPI ID is available
    if (!empty($bankdetails[$bankname]['UPI']))
    {
        $upiId = $bankdetails[$bankname]['UPI'];
        $merchantName = $bankdetails[$bankname]['AccountName'];
        $amount = $invoice['total'];
        $invoiceId = isset($invoice['id_invoice']) ? $invoice['id_invoice'] : (isset($id_invoice) ? $id_invoice : '');
        $transactionNote = "Invoice $invoiceId Payment";

        $invoiceId = sprintf($bankdetails[$bankname]['TransactionRefFormat'],$invoiceId);

        // Build UPI QR code URL with render=true to get PNG directly
        $qrCodeUrl = "/upi-qr/generate?"
            . "pa=" . urlencode($upiId)
            . "&pn=" . urlencode($merchantName)
            . "&am=" . urlencode($amount)
            . "&tr=" . urlencode($invoiceId)
            . "&tn=" . urlencode($transactionNote)
            . "&render=true";
    ?>
        <div style="flex-shrink: 0; text-align: center; margin-top: -100px;">
            <p><strong>UPI QR Code</strong></p>
            <img src="<?= $qrCodeUrl ?>" alt="UPI QR Code" style="width: 200px; height: 200px; border: 1px solid #ddd; padding: 5px;">
        </div>
    <?php
    } ?>

</div>