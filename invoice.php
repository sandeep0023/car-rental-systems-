<?php
session_start();
include('includes/config.php');
error_reporting(0);

if (!isset($_SESSION['login'])) {
    header('Location: car-listing.php');
    exit();
}

$bookingno = isset($_GET['bno']) ? $_GET['bno'] : '';

$sql = "SELECT tblbooking.*, tblvehicles.VehiclesTitle, tblvehicles.PricePerDay, tblbrands.BrandName,
        tblusers.FullName, tblusers.Address, tblusers.City, tblusers.Country,
        DATEDIFF(tblbooking.ToDate, tblbooking.FromDate) as totaldays
        FROM tblbooking
        JOIN tblvehicles ON tblvehicles.id = tblbooking.VehicleId
        JOIN tblbrands ON tblbrands.id = tblvehicles.VehiclesBrand
        JOIN tblusers ON tblusers.EmailId = tblbooking.userEmail
        WHERE tblbooking.BookingNumber = :bno AND tblbooking.userEmail = :useremail";
$query = $dbh->prepare($sql);
$query->bindParam(':bno', $bookingno, PDO::PARAM_STR);
$query->bindParam(':useremail', $_SESSION['login'], PDO::PARAM_STR);
$query->execute();
$b = $query->fetch(PDO::FETCH_OBJ);

if (!$b) {
    echo "Invoice not found.";
    exit();
}

$totaldays = max(1, intval($b->totaldays));
$amount = $b->TotalAmount ? $b->TotalAmount : ($totaldays * intval($b->PricePerDay));
?>
<!DOCTYPE HTML>
<html lang="en">
<head>
<title>Invoice #<?php echo htmlentities($bookingno); ?></title>
<link rel="stylesheet" href="assets/css/bootstrap.min.css" type="text/css">
<style>
body { font-family: Arial, sans-serif; color: #222; }
.invoice-wrap { max-width: 720px; margin: 40px auto; padding: 30px; border: 1px solid #ddd; }
.invoice-head { display: flex; justify-content: space-between; align-items: center; border-bottom: 3px solid #2e7d32; padding-bottom: 16px; margin-bottom: 20px; }
.invoice-head h2 { color: #2e7d32; margin: 0; }
.status-paid { color: #2e7d32; font-weight: bold; }
.status-unpaid { color: #c62828; font-weight: bold; }
table.inv-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
table.inv-table th, table.inv-table td { border: 1px solid #ddd; padding: 10px; text-align: left; }
table.inv-table th { background: #f4f4f4; }
.grand-total { font-size: 18px; font-weight: bold; }
.print-btn { margin: 20px 0; }
@media print {
  .print-btn, .no-print { display: none; }
  .invoice-wrap { border: none; margin: 0; }
}
</style>
</head>
<body>
<div class="invoice-wrap">
  <div class="print-btn no-print">
    <button onclick="window.print()" class="btn btn-primary">Print / Save as PDF</button>
    <a href="my-booking.php" class="btn btn-default">Back to My Bookings</a>
  </div>

  <div class="invoice-head">
    <h2>Car Rental Portal</h2>
    <div style="text-align:right;">
      <div>Invoice for Booking #<?php echo htmlentities($bookingno); ?></div>
      <div><?php echo date('d M Y', strtotime($b->PostingDate)); ?></div>
    </div>
  </div>

  <table class="inv-table">
    <tr><th>Billed To</th><td><?php echo htmlentities($b->FullName); ?><br><?php echo htmlentities($b->Address . ', ' . $b->City . ' ' . $b->Country); ?></td></tr>
    <tr><th>Vehicle</th><td><?php echo htmlentities($b->BrandName . ' ' . $b->VehiclesTitle); ?></td></tr>
    <tr><th>Rental Period</th><td><?php echo htmlentities($b->FromDate); ?> to <?php echo htmlentities($b->ToDate); ?> (<?php echo $totaldays; ?> day<?php echo $totaldays > 1 ? 's' : ''; ?>)</td></tr>
    <tr><th>Rate / Day</th><td>$<?php echo htmlentities($b->PricePerDay); ?></td></tr>
    <tr><th>Payment Status</th>
      <td>
        <?php if ($b->PaymentStatus == 1): ?>
          <span class="status-paid">PAID</span> via <?php echo htmlentities($b->PaymentMethod); ?><br>
          Transaction ID: <?php echo htmlentities($b->TransactionId); ?>
        <?php else: ?>
          <span class="status-unpaid">UNPAID</span> — <a href="payment.php?bno=<?php echo htmlentities($bookingno); ?>">Pay now</a>
        <?php endif; ?>
      </td>
    </tr>
    <tr><th class="grand-total">Grand Total</th><td class="grand-total">$<?php echo htmlentities($amount); ?></td></tr>
  </table>

  <p style="margin-top:24px; color:#777; font-size:12px;">This is a computer-generated invoice for a student project demo. No real payment was processed.</p>
</div>
</body>
</html>
