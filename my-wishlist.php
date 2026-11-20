<?php
session_start();
include('includes/config.php');
error_reporting(0);
if (!isset($_SESSION['login'])) {
    header('Location: car-listing.php');
    exit();
}
?>
<!DOCTYPE HTML>
<html lang="en">
<head>
<title>Car Rental | My Wishlist</title>
<link rel="stylesheet" href="assets/css/bootstrap.min.css" type="text/css">
<link rel="stylesheet" href="assets/css/style.css" type="text/css">
<link href="assets/css/font-awesome.min.css" rel="stylesheet">
<link rel="stylesheet" id="switcher-css" type="text/css" href="assets/switcher/css/switcher.css" media="all" />
<link rel="alternate stylesheet" type="text/css" href="assets/switcher/css/red.css" title="red" media="all" data-default-color="true" />
</head>
<body>
<?php include('includes/colorswitcher.php'); ?>
<?php include('includes/header.php'); ?>

<section class="page-header listing_page">
  <div class="container">
    <div class="page-header_wrap">
      <div class="page-heading"><h1>My Wishlist</h1></div>
    </div>
  </div>
  <div class="dark-overlay"></div>
</section>

<section class="listing-page">
  <div class="container">
    <div class="row">
      <div class="col-md-3 col-sm-3">
        <?php include('includes/sidebar.php'); ?>
      </div>
      <div class="col-md-9 col-sm-9">
        <div class="row">
<?php
$useremail = $_SESSION['login'];
$sql = "SELECT tblvehicles.*, tblbrands.BrandName FROM tblwishlist
        JOIN tblvehicles ON tblvehicles.id = tblwishlist.VehicleId
        JOIN tblbrands ON tblbrands.id = tblvehicles.VehiclesBrand
        WHERE tblwishlist.userEmail = :email ORDER BY tblwishlist.id DESC";
$query = $dbh->prepare($sql);
$query->bindParam(':email', $useremail, PDO::PARAM_STR);
$query->execute();
$results = $query->fetchAll(PDO::FETCH_OBJ);

if ($query->rowCount() > 0):
    foreach ($results as $result): ?>
        <div class="col-md-4 grid_listing">
          <div class="product-listing-m gray-bg">
            <div class="product-listing-img">
              <a href="vehical-details.php?vhid=<?php echo htmlentities($result->id); ?>">
                <img src="admin/img/vehicleimages/<?php echo htmlentities($result->Vimage1); ?>" class="img-responsive" alt="image" />
              </a>
            </div>
            <div class="product-listing-content">
              <h5><a href="vehical-details.php?vhid=<?php echo htmlentities($result->id); ?>"><?php echo htmlentities($result->BrandName); ?>, <?php echo htmlentities($result->VehiclesTitle); ?></a></h5>
              <p class="list-price">$<?php echo htmlentities($result->PricePerDay); ?> Per Day</p>
              <button class="btn btn-xs" style="background:#c62828;color:#fff;border:none;" onclick="removeWish(<?php echo htmlentities($result->id); ?>, this)">
                <i class="fa fa-heart" aria-hidden="true"></i> Remove from Wishlist
              </button>
            </div>
          </div>
        </div>
    <?php endforeach;
else: ?>
        <div class="col-md-12"><h5 style="color:red;">Your wishlist is empty. Browse <a href="car-listing.php">Car Listing</a> and tap the heart icon to save cars here.</h5></div>
<?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</section>

<?php include('includes/footer.php'); ?>

<script src="assets/js/jquery.min.js"></script>
<script src="assets/js/bootstrap.min.js"></script>
<script>
function removeWish(vhid, btn) {
    $.post('wishlist-action.php', { vhid: vhid }, function(data) {
        var res = JSON.parse(data);
        if (res.status === 'removed') {
            $(btn).closest('.grid_listing').fadeOut(300, function() { $(this).remove(); });
        }
    });
}
</script>
</body>
</html>
