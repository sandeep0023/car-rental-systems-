<?php
session_start();
include('includes/config.php');
error_reporting(0);

if (!isset($_SESSION['login'])) {
    header('Location: car-listing.php');
    exit();
}

$bookingno = isset($_GET['bno']) ? $_GET['bno'] : (isset($_POST['bno']) ? $_POST['bno'] : '');

// Fetch the booking + vehicle details to calculate the amount due
$sql = "SELECT tblbooking.*, tblvehicles.VehiclesTitle, tblvehicles.PricePerDay, tblbrands.BrandName,
        DATEDIFF(tblbooking.ToDate, tblbooking.FromDate) as totaldays
        FROM tblbooking
        JOIN tblvehicles ON tblvehicles.id = tblbooking.VehicleId
        JOIN tblbrands ON tblbrands.id = tblvehicles.VehiclesBrand
        WHERE tblbooking.BookingNumber = :bno AND tblbooking.userEmail = :useremail";
$query = $dbh->prepare($sql);
$query->bindParam(':bno', $bookingno, PDO::PARAM_STR);
$query->bindParam(':useremail', $_SESSION['login'], PDO::PARAM_STR);
$query->execute();
$booking = $query->fetch(PDO::FETCH_OBJ);

if (!$booking) {
    header('Location: my-booking.php');
    exit();
}

$totaldays = max(1, intval($booking->totaldays));
$amount = $totaldays * intval($booking->PricePerDay);

$paymentDone = false;
$transactionId = '';

// ---- Simulated payment processing ----
// NOTE: This is a classroom/demo simulation, not a real payment gateway
// integration. No money moves and no external API (Razorpay/Stripe) is
// called. It mimics the UX (order summary -> "Pay Now" -> success) so the
// booking flow can be demonstrated end-to-end without live API keys.
if (isset($_POST['pay_now'])) {
    $transactionId = 'PAY' . strtoupper(substr(md5(uniqid(rand(), true)), 0, 10));
    $method = isset($_POST['payment_method']) ? $_POST['payment_method'] : 'Card';

    $update = $dbh->prepare("UPDATE tblbooking SET PaymentStatus=1, TransactionId=:tid, PaymentMethod=:method, TotalAmount=:amt WHERE BookingNumber=:bno");
    $update->bindParam(':tid', $transactionId, PDO::PARAM_STR);
    $update->bindParam(':method', $method, PDO::PARAM_STR);
    $update->bindParam(':amt', $amount, PDO::PARAM_INT);
    $update->bindParam(':bno', $bookingno, PDO::PARAM_STR);
    $update->execute();

    // Log simulated email + SMS confirmations (see tblnotifications comment in feature_updates.sql)
    $emailMsg = "Your booking #$bookingno for {$booking->BrandName} {$booking->VehiclesTitle} is confirmed. Amount paid: $" . $amount . ". Transaction ID: $transactionId.";
    $smsMsg = "CarRental: Booking #$bookingno confirmed. Paid $" . $amount . ". Txn: $transactionId";

    $notif = $dbh->prepare("INSERT INTO tblnotifications (userEmail, BookingNumber, NotifyType, Message) VALUES (:email, :bno, 'email', :msg)");
    $notif->execute([':email' => $_SESSION['login'], ':bno' => $bookingno, ':msg' => $emailMsg]);

    $notif2 = $dbh->prepare("INSERT INTO tblnotifications (userEmail, BookingNumber, NotifyType, Message) VALUES (:email, :bno, 'sms', :msg)");
    $notif2->execute([':email' => $_SESSION['login'], ':bno' => $bookingno, ':msg' => $smsMsg]);

    $paymentDone = true;
}
?>
<!DOCTYPE HTML>
<html lang="en">
<head>
<title>Car Rental | Payment</title>
<link rel="stylesheet" href="assets/css/bootstrap.min.css" type="text/css">
<link rel="stylesheet" href="assets/css/style.css" type="text/css">
<link href="assets/css/font-awesome.min.css" rel="stylesheet">
<style>
.pay-wrap { max-width: 480px; margin: 60px auto; }
.pay-card { background: #fff; border-radius: 10px; box-shadow: 0 4px 24px rgba(0,0,0,.12); padding: 30px; }
.pay-card h3 { margin-top: 0; }
.order-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #eee; }
.order-row.total { font-weight: bold; font-size: 18px; border-bottom: none; padding-top: 14px; }
.method-choice { display: flex; gap: 10px; margin: 18px 0; }
.method-choice label { flex: 1; border: 1px solid #ddd; border-radius: 6px; padding: 10px; text-align: center; cursor: pointer; }
.method-choice input[type="radio"]:checked + span { color: #fff; }
.method-choice label:has(input:checked) { background: #2e7d32; color: #fff; border-color: #2e7d32; }
.pay-btn { width: 100%; background: #2e7d32; color: #fff; border: none; padding: 14px; border-radius: 6px; font-size: 16px; cursor: pointer; margin-top: 10px; }
.pay-btn:hover { background: #256428; }
.success-box { text-align: center; }
.success-box i { font-size: 60px; color: #2e7d32; }
.sim-badge { display:inline-block; background:#fff3cd; color:#7a5b00; font-size:12px; padding:3px 10px; border-radius:12px; margin-bottom: 14px; }
</style>
</head>
<body style="background:#f4f4f4;">
<?php include('includes/header.php'); ?>

<div class="pay-wrap">
  <div class="pay-card">
    <?php if ($paymentDone): ?>
      <div class="success-box">
        <i class="fa fa-check-circle" aria-hidden="true"></i>
        <h3>Payment Successful</h3>
        <p>Transaction ID: <strong><?php echo htmlentities($transactionId); ?></strong></p>
        <p>A confirmation email and SMS have been sent (simulated) for booking #<?php echo htmlentities($bookingno); ?>.</p>
        <a href="invoice.php?bno=<?php echo htmlentities($bookingno); ?>" class="btn" style="margin-right:8px;">View Invoice</a>
        <a href="my-booking.php" class="btn btn-default">My Bookings</a>
      </div>
    <?php else: ?>
      <span class="sim-badge">Simulated Checkout — demo only, no real charge</span>
      <h3>Complete Your Payment</h3>
      <div class="order-row"><span>Vehicle</span><span><?php echo htmlentities($booking->BrandName . ' ' . $booking->VehiclesTitle); ?></span></div>
      <div class="order-row"><span>Rental Period</span><span><?php echo htmlentities($booking->FromDate . ' to ' . $booking->ToDate); ?></span></div>
      <div class="order-row"><span>Days &times; Rate</span><span><?php echo $totaldays; ?> &times; $<?php echo htmlentities($booking->PricePerDay); ?></span></div>
      <div class="order-row total"><span>Total Amount</span><span>$<?php echo $amount; ?></span></div>

      <form method="post">
        <input type="hidden" name="bno" value="<?php echo htmlentities($bookingno); ?>">
        <p style="margin-top:18px; margin-bottom:6px;"><strong>Choose payment method</strong></p>
        <div class="method-choice">
          <label><input type="radio" name="payment_method" value="Card" checked> <span>Card</span></label>
          <label><input type="radio" name="payment_method" value="UPI"> <span>UPI</span></label>
          <label><input type="radio" name="payment_method" value="NetBanking"> <span>Net Banking</span></label>
        </div>
        <button type="submit" name="pay_now" class="pay-btn">Pay $<?php echo $amount; ?> Now</button>
      </form>
    <?php endif; ?>
  </div>
</div>

<?php include('includes/footer.php'); ?>
</body>
</html>
