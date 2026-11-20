<?php 
session_start();
include('includes/config.php');
error_reporting(0);
if(isset($_POST['submit']))
{
$fromdate=$_POST['fromdate'];
$todate=$_POST['todate']; 
$message=$_POST['message'];
$useremail=$_SESSION['login'];
$status=0;
$vhid=$_GET['vhid'];
$bookingno=mt_rand(100000000, 999999999);
$ret="SELECT * FROM tblbooking where (:fromdate BETWEEN date(FromDate) and date(ToDate) || :todate BETWEEN date(FromDate) and date(ToDate) || date(FromDate) BETWEEN :fromdate and :todate) and VehicleId=:vhid";
$query1 = $dbh -> prepare($ret);
$query1->bindParam(':vhid',$vhid, PDO::PARAM_STR);
$query1->bindParam(':fromdate',$fromdate,PDO::PARAM_STR);
$query1->bindParam(':todate',$todate,PDO::PARAM_STR);
$query1->execute();
$results1=$query1->fetchAll(PDO::FETCH_OBJ);

if($query1->rowCount()==0)
{

$sql="INSERT INTO  tblbooking(BookingNumber,userEmail,VehicleId,FromDate,ToDate,message,Status) VALUES(:bookingno,:useremail,:vhid,:fromdate,:todate,:message,:status)";
$query = $dbh->prepare($sql);
$query->bindParam(':bookingno',$bookingno,PDO::PARAM_STR);
$query->bindParam(':useremail',$useremail,PDO::PARAM_STR);
$query->bindParam(':vhid',$vhid,PDO::PARAM_STR);
$query->bindParam(':fromdate',$fromdate,PDO::PARAM_STR);
$query->bindParam(':todate',$todate,PDO::PARAM_STR);
$query->bindParam(':message',$message,PDO::PARAM_STR);
$query->bindParam(':status',$status,PDO::PARAM_STR);
$query->execute();
$lastInsertId = $dbh->lastInsertId();
if($lastInsertId)
{
echo "<script>alert('Booking successful. Redirecting to payment...');</script>";
echo "<script type='text/javascript'> document.location = 'payment.php?bno=" . $bookingno . "'; </script>";
}
else 
{
echo "<script>alert('Something went wrong. Please try again');</script>";
 echo "<script type='text/javascript'> document.location = 'car-listing.php'; </script>";
} }  else{
 echo "<script>alert('Car already booked for these days');</script>"; 
 echo "<script type='text/javascript'> document.location = 'car-listing.php'; </script>";
}

}

?>


<!DOCTYPE HTML>
<html lang="en">
<head>

<title>Car Rental | Vehicle Details</title>
<!--Bootstrap -->
<link rel="stylesheet" href="assets/css/bootstrap.min.css" type="text/css">
<!--Custome Style -->
<link rel="stylesheet" href="assets/css/style.css" type="text/css">
<!--OWL Carousel slider-->
<link rel="stylesheet" href="assets/css/owl.carousel.css" type="text/css">
<link rel="stylesheet" href="assets/css/owl.transitions.css" type="text/css">
<!--slick-slider -->
<link href="assets/css/slick.css" rel="stylesheet">
<!--bootstrap-slider -->
<link href="assets/css/bootstrap-slider.min.css" rel="stylesheet">
<!--FontAwesome Font Style -->
<link href="assets/css/font-awesome.min.css" rel="stylesheet">

<!-- SWITCHER -->
		<link rel="stylesheet" id="switcher-css" type="text/css" href="assets/switcher/css/switcher.css" media="all" />
		<link rel="alternate stylesheet" type="text/css" href="assets/switcher/css/red.css" title="red" media="all" data-default-color="true" />
		<link rel="alternate stylesheet" type="text/css" href="assets/switcher/css/orange.css" title="orange" media="all" />
		<link rel="alternate stylesheet" type="text/css" href="assets/switcher/css/blue.css" title="blue" media="all" />
		<link rel="alternate stylesheet" type="text/css" href="assets/switcher/css/pink.css" title="pink" media="all" />
		<link rel="alternate stylesheet" type="text/css" href="assets/switcher/css/green.css" title="green" media="all" />
		<link rel="alternate stylesheet" type="text/css" href="assets/switcher/css/purple.css" title="purple" media="all" />
<link rel="apple-touch-icon-precomposed" sizes="144x144" href="assets/images/favicon-icon/apple-touch-icon-144-precomposed.png">
<link rel="apple-touch-icon-precomposed" sizes="114x114" href="assets/images/favicon-icon/apple-touch-icon-114-precomposed.html">
<link rel="apple-touch-icon-precomposed" sizes="72x72" href="assets/images/favicon-icon/apple-touch-icon-72-precomposed.png">
<link rel="apple-touch-icon-precomposed" href="assets/images/favicon-icon/apple-touch-icon-57-precomposed.png">
<link rel="shortcut icon" href="assets/images/favicon-icon/favicon.png">
<link href="https://fonts.googleapis.com/css?family=Lato:300,400,700,900" rel="stylesheet">
</head>
<body>

<!-- Start Switcher -->
<?php include('includes/colorswitcher.php');?>
<!-- /Switcher -->  

<!--Header-->
<?php include('includes/header.php');?>
<!-- /Header --> 

<!--Listing-Image-Slider-->

<?php 
$vhid=intval($_GET['vhid']);
$sql = "SELECT tblvehicles.*,tblbrands.BrandName,tblbrands.id as bid  from tblvehicles join tblbrands on tblbrands.id=tblvehicles.VehiclesBrand where tblvehicles.id=:vhid";
$query = $dbh -> prepare($sql);
$query->bindParam(':vhid',$vhid, PDO::PARAM_STR);
$query->execute();
$results=$query->fetchAll(PDO::FETCH_OBJ);
$cnt=1;
if($query->rowCount() > 0)
{
foreach($results as $result)
{  
$_SESSION['brndid']=$result->bid;

// Average rating + review count for this vehicle
$ratingSql = "SELECT COUNT(*) as cnt, AVG(Rating) as avgrating FROM tblreviews WHERE VehicleId = :vhid";
$ratingQuery = $dbh->prepare($ratingSql);
$ratingQuery->bindParam(':vhid', $vhid, PDO::PARAM_INT);
$ratingQuery->execute();
$ratingInfo = $ratingQuery->fetch(PDO::FETCH_OBJ);
$avgRating = $ratingInfo->avgrating ? round($ratingInfo->avgrating, 1) : 0;
$reviewCount = $ratingInfo->cnt;

// Is this vehicle already in the logged-in user's wishlist?
$isWishlisted = false;
if (isset($_SESSION['login'])) {
    $wSql = "SELECT id FROM tblwishlist WHERE userEmail=:email AND VehicleId=:vhid";
    $wQuery = $dbh->prepare($wSql);
    $wQuery->bindParam(':email', $_SESSION['login'], PDO::PARAM_STR);
    $wQuery->bindParam(':vhid', $vhid, PDO::PARAM_INT);
    $wQuery->execute();
    $isWishlisted = $wQuery->rowCount() > 0;
}

// All non-cancelled bookings for this vehicle, to mark unavailable dates on the calendar
$bookedSql = "SELECT FromDate, ToDate FROM tblbooking WHERE VehicleId = :vhid AND Status != 2";
$bookedQuery = $dbh->prepare($bookedSql);
$bookedQuery->bindParam(':vhid', $vhid, PDO::PARAM_INT);
$bookedQuery->execute();
$bookedRanges = $bookedQuery->fetchAll(PDO::FETCH_ASSOC);
?>  

<section id="listing_img_slider">
  <div><img src="admin/img/vehicleimages/<?php echo htmlentities($result->Vimage1);?>" class="img-responsive" alt="image" width="900" height="560"></div>
  <div><img src="admin/img/vehicleimages/<?php echo htmlentities($result->Vimage2);?>" class="img-responsive" alt="image" width="900" height="560"></div>
  <div><img src="admin/img/vehicleimages/<?php echo htmlentities($result->Vimage3);?>" class="img-responsive" alt="image" width="900" height="560"></div>
  <div><img src="admin/img/vehicleimages/<?php echo htmlentities($result->Vimage4);?>" class="img-responsive"  alt="image" width="900" height="560"></div>
  <?php if($result->Vimage5=="")
{

} else {
  ?>
  <div><img src="admin/img/vehicleimages/<?php echo htmlentities($result->Vimage5);?>" class="img-responsive" alt="image" width="900" height="560"></div>
  <?php } ?>
</section>
<!--/Listing-Image-Slider-->


<!--Listing-detail-->
<section class="listing-detail">
  <div class="container">
    <div class="listing_detail_head row">
      <div class="col-md-9">
        <h2><?php echo htmlentities($result->BrandName);?> , <?php echo htmlentities($result->VehiclesTitle);?>
        <button id="wishBtn" onclick="toggleWishlist(<?php echo htmlentities($vhid); ?>, this)" style="border:none;background:none;cursor:pointer;font-size:22px;color:<?php echo $isWishlisted ? '#c62828' : '#bbb'; ?>;" title="Save to wishlist">
          <i class="fa fa-heart" aria-hidden="true"></i>
        </button>
        </h2>
        <div style="color:#f5a623;">
          <?php for ($i = 1; $i <= 5; $i++): ?>
            <i class="fa fa-star<?php echo $i <= round($avgRating) ? '' : '-o'; ?>" aria-hidden="true"></i>
          <?php endfor; ?>
          <span style="color:#666; font-size:13px;"> <?php echo $avgRating; ?> / 5 (<?php echo $reviewCount; ?> review<?php echo $reviewCount == 1 ? '' : 's'; ?>)</span>
        </div>
      </div>
      <div class="col-md-3">
        <div class="price_info">
          <p>$<?php echo htmlentities($result->PricePerDay);?> </p>Per Day
         
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-md-9">
        <div class="main_features">
          <ul>
          
            <li> <i class="fa fa-calendar" aria-hidden="true"></i>
              <h5><?php echo htmlentities($result->ModelYear);?></h5>
              <p>Reg.Year</p>
            </li>
            <li> <i class="fa fa-cogs" aria-hidden="true"></i>
              <h5><?php echo htmlentities($result->FuelType);?></h5>
              <p>Fuel Type</p>
            </li>
       
            <li> <i class="fa fa-user-plus" aria-hidden="true"></i>
              <h5><?php echo htmlentities($result->SeatingCapacity);?></h5>
              <p>Seats</p>
            </li>
            <?php if (!empty($result->Location)): ?>
            <li> <i class="fa fa-map-marker" aria-hidden="true"></i>
              <h5><?php echo htmlentities($result->Location);?></h5>
              <p>Pickup Location</p>
            </li>
            <?php endif; ?>
          </ul>
        </div>
        <div class="listing_more_info">
          <div class="listing_detail_wrap"> 
            <!-- Nav tabs -->
            <ul class="nav nav-tabs gray-bg" role="tablist">
              <li role="presentation" class="active"><a href="#vehicle-overview " aria-controls="vehicle-overview" role="tab" data-toggle="tab">Vehicle Overview </a></li>
          
              <li role="presentation"><a href="#accessories" aria-controls="accessories" role="tab" data-toggle="tab">Accessories</a></li>
            </ul>
            
            <!-- Tab panes -->
            <div class="tab-content"> 
              <!-- vehicle-overview -->
              <div role="tabpanel" class="tab-pane active" id="vehicle-overview">
                
                <p><?php echo htmlentities($result->VehiclesOverview);?></p>
              </div>
              
              
              <!-- Accessories -->
              <div role="tabpanel" class="tab-pane" id="accessories"> 
                <!--Accessories-->
                <table>
                  <thead>
                    <tr>
                      <th colspan="2">Accessories</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <td>Air Conditioner</td>
<?php if($result->AirConditioner==1)
{
?>
                      <td><i class="fa fa-check" aria-hidden="true"></i></td>
<?php } else { ?> 
   <td><i class="fa fa-close" aria-hidden="true"></i></td>
   <?php } ?> </tr>

<tr>
<td>AntiLock Braking System</td>
<?php if($result->AntiLockBrakingSystem==1)
{
?>
<td><i class="fa fa-check" aria-hidden="true"></i></td>
<?php } else {?>
<td><i class="fa fa-close" aria-hidden="true"></i></td>
<?php } ?>
                    </tr>

<tr>
<td>Power Steering</td>
<?php if($result->PowerSteering==1)
{
?>
<td><i class="fa fa-check" aria-hidden="true"></i></td>
<?php } else { ?>
<td><i class="fa fa-close" aria-hidden="true"></i></td>
<?php } ?>
</tr>
                   

<tr>

<td>Power Windows</td>

<?php if($result->PowerWindows==1)
{
?>
<td><i class="fa fa-check" aria-hidden="true"></i></td>
<?php } else { ?>
<td><i class="fa fa-close" aria-hidden="true"></i></td>
<?php } ?>
</tr>
                   
 <tr>
<td>CD Player</td>
<?php if($result->CDPlayer==1)
{
?>
<td><i class="fa fa-check" aria-hidden="true"></i></td>
<?php } else { ?>
<td><i class="fa fa-close" aria-hidden="true"></i></td>
<?php } ?>
</tr>

<tr>
<td>Leather Seats</td>
<?php if($result->LeatherSeats==1)
{
?>
<td><i class="fa fa-check" aria-hidden="true"></i></td>
<?php } else { ?>
<td><i class="fa fa-close" aria-hidden="true"></i></td>
<?php } ?>
</tr>

<tr>
<td>Central Locking</td>
<?php if($result->CentralLocking==1)
{
?>
<td><i class="fa fa-check" aria-hidden="true"></i></td>
<?php } else { ?>
<td><i class="fa fa-close" aria-hidden="true"></i></td>
<?php } ?>
</tr>

<tr>
<td>Power Door Locks</td>
<?php if($result->PowerDoorLocks==1)
{
?>
<td><i class="fa fa-check" aria-hidden="true"></i></td>
<?php } else { ?>
<td><i class="fa fa-close" aria-hidden="true"></i></td>
<?php } ?>
                    </tr>
                    <tr>
<td>Brake Assist</td>
<?php if($result->BrakeAssist==1)
{
?>
<td><i class="fa fa-check" aria-hidden="true"></i></td>
<?php  } else { ?>
<td><i class="fa fa-close" aria-hidden="true"></i></td>
<?php } ?>
</tr>

<tr>
<td>Driver Airbag</td>
<?php if($result->DriverAirbag==1)
{
?>
<td><i class="fa fa-check" aria-hidden="true"></i></td>
<?php } else { ?>
<td><i class="fa fa-close" aria-hidden="true"></i></td>
<?php } ?>
 </tr>
 
 <tr>
 <td>Passenger Airbag</td>
 <?php if($result->PassengerAirbag==1)
{
?>
<td><i class="fa fa-check" aria-hidden="true"></i></td>
<?php } else {?>
<td><i class="fa fa-close" aria-hidden="true"></i></td>
<?php } ?>
</tr>

<tr>
<td>Crash Sensor</td>
<?php if($result->CrashSensor==1)
{
?>
<td><i class="fa fa-check" aria-hidden="true"></i></td>
<?php } else { ?>
<td><i class="fa fa-close" aria-hidden="true"></i></td>
<?php } ?>
</tr>

                  </tbody>
                </table>
              </div>
            </div>
          </div>
          
        </div>
<?php }} ?>
   
      </div>
      
      <!--Side-Bar-->
      <aside class="col-md-3">
      
        <div class="share_vehicle">
          <p>Share: <a href="#"><i class="fa fa-facebook-square" aria-hidden="true"></i></a> <a href="#"><i class="fa fa-twitter-square" aria-hidden="true"></i></a> <a href="#"><i class="fa fa-linkedin-square" aria-hidden="true"></i></a> <a href="#"><i class="fa fa-google-plus-square" aria-hidden="true"></i></a> </p>
        </div>
        <div class="sidebar_widget">
          <div class="widget_heading">
            <h5><i class="fa fa-calendar-check-o" aria-hidden="true"></i>Availability</h5>
          </div>
          <div id="availCalendar" style="padding:10px; background:#fff;"></div>
          <div style="font-size:12px; padding:8px 10px;">
            <span style="color:#c62828;">&#9632;</span> Booked &nbsp;
            <span style="color:#2e7d32;">&#9632;</span> Available
          </div>
        </div>
        <div class="sidebar_widget">
          <div class="widget_heading">
            <h5><i class="fa fa-envelope" aria-hidden="true"></i>Book Now</h5>
          </div>
          <form method="post">
            <div class="form-group">
              <label>From Date:</label>
              <input type="date" class="form-control" name="fromdate" placeholder="From Date" required>
            </div>
            <div class="form-group">
              <label>To Date:</label>
              <input type="date" class="form-control" name="todate" placeholder="To Date" required>
            </div>
            <div class="form-group">
              <textarea rows="4" class="form-control" name="message" placeholder="Message" required></textarea>
            </div>
          <?php if($_SESSION['login'])
              {?>
              <div class="form-group">
                <input type="submit" class="btn"  name="submit" value="Book Now">
              </div>
              <?php } else { ?>
<a href="#loginform" class="btn btn-xs uppercase" data-toggle="modal" data-dismiss="modal">Login For Book</a>

              <?php } ?>
          </form>
        </div>
      </aside>
      <!--/Side-Bar--> 
    </div>
    
    <div class="space-20"></div>
    <div class="divider"></div>
    
    <!--Similar-Cars-->
    <div class="similar_cars">
      <h3>Similar Cars</h3>
      <div class="row">
<?php 
$bid=$_SESSION['brndid'];
$sql="SELECT tblvehicles.VehiclesTitle,tblbrands.BrandName,tblvehicles.PricePerDay,tblvehicles.FuelType,tblvehicles.ModelYear,tblvehicles.id,tblvehicles.SeatingCapacity,tblvehicles.VehiclesOverview,tblvehicles.Vimage1 from tblvehicles join tblbrands on tblbrands.id=tblvehicles.VehiclesBrand where tblvehicles.VehiclesBrand=:bid";
$query = $dbh -> prepare($sql);
$query->bindParam(':bid',$bid, PDO::PARAM_STR);
$query->execute();
$results=$query->fetchAll(PDO::FETCH_OBJ);
$cnt=1;
if($query->rowCount() > 0)
{
foreach($results as $result)
{ ?>      
        <div class="col-md-3 grid_listing">
          <div class="product-listing-m gray-bg">
            <div class="product-listing-img"> <a href="vehical-details.php?vhid=<?php echo htmlentities($result->id);?>"><img src="admin/img/vehicleimages/<?php echo htmlentities($result->Vimage1);?>" class="img-responsive" alt="image" /> </a>
            </div>
            <div class="product-listing-content">
              <h5><a href="vehical-details.php?vhid=<?php echo htmlentities($result->id);?>"><?php echo htmlentities($result->BrandName);?> , <?php echo htmlentities($result->VehiclesTitle);?></a></h5>
              <p class="list-price">$<?php echo htmlentities($result->PricePerDay);?></p>
          
              <ul class="features_list">
                
             <li><i class="fa fa-user" aria-hidden="true"></i><?php echo htmlentities($result->SeatingCapacity);?> seats</li>
                <li><i class="fa fa-calendar" aria-hidden="true"></i><?php echo htmlentities($result->ModelYear);?> model</li>
                <li><i class="fa fa-car" aria-hidden="true"></i><?php echo htmlentities($result->FuelType);?></li>
              </ul>
            </div>
          </div>
        </div>
 <?php }} ?>       

      </div>
    </div>
    <!--/Similar-Cars--> 

    <div class="space-20"></div>
    <div class="divider"></div>

    <!--Reviews-->
    <div class="reviews_section" id="reviews">
      <h3>Customer Reviews (<?php echo $reviewCount; ?>)</h3>
      <?php
      $revSql = "SELECT tblreviews.*, tblusers.FullName FROM tblreviews
                 LEFT JOIN tblusers ON tblusers.EmailId = tblreviews.userEmail
                 WHERE tblreviews.VehicleId = :vhid ORDER BY tblreviews.PostingDate DESC";
      $revQuery = $dbh->prepare($revSql);
      $revQuery->bindParam(':vhid', $vhid, PDO::PARAM_INT);
      $revQuery->execute();
      $reviews = $revQuery->fetchAll(PDO::FETCH_OBJ);
      if (count($reviews) > 0):
          foreach ($reviews as $rev): ?>
        <div style="border-bottom:1px solid #eee; padding:12px 0;">
          <strong><?php echo htmlentities($rev->FullName ? $rev->FullName : $rev->userEmail); ?></strong>
          <span style="color:#f5a623; margin-left:8px;">
            <?php for ($i = 1; $i <= 5; $i++): ?>
              <i class="fa fa-star<?php echo $i <= $rev->Rating ? '' : '-o'; ?>" aria-hidden="true"></i>
            <?php endfor; ?>
          </span>
          <span style="color:#999; font-size:12px; margin-left:8px;"><?php echo date('d M Y', strtotime($rev->PostingDate)); ?></span>
          <p style="margin-top:6px;"><?php echo htmlentities($rev->ReviewText); ?></p>
        </div>
      <?php endforeach;
      else: ?>
        <p style="color:#777;">No reviews yet. Be the first to review this car.</p>
      <?php endif; ?>

      <?php if (isset($_SESSION['login'])): ?>
      <div style="margin-top:20px; background:#f9f9f9; padding:16px; border-radius:6px;">
        <h5>Write a review</h5>
        <form method="post" action="review-submit.php">
          <input type="hidden" name="vhid" value="<?php echo htmlentities($vhid); ?>">
          <div class="form-group">
            <label>Rating:</label><br>
            <select name="rating" class="form-control" style="width:120px; display:inline-block;">
              <option value="5">5 - Excellent</option>
              <option value="4">4 - Good</option>
              <option value="3">3 - Average</option>
              <option value="2">2 - Poor</option>
              <option value="1">1 - Terrible</option>
            </select>
          </div>
          <div class="form-group">
            <textarea name="reviewtext" class="form-control" rows="3" placeholder="Share your experience with this car" required></textarea>
          </div>
          <button type="submit" name="submit_review" class="btn">Submit Review</button>
        </form>
      </div>
      <?php else: ?>
        <p><a href="#loginform" data-toggle="modal">Login</a> to write a review.</p>
      <?php endif; ?>
    </div>
    <!--/Reviews-->

  </div>
</section>
<!--/Listing-detail--> 

<!--Footer -->
<?php include('includes/footer.php');?>
<!-- /Footer--> 

<!--Back to top-->
<div id="back-top" class="back-top"> <a href="#top"><i class="fa fa-angle-up" aria-hidden="true"></i> </a> </div>
<!--/Back to top--> 

<!--Login-Form -->
<?php include('includes/login.php');?>
<!--/Login-Form --> 

<!--Register-Form -->
<?php include('includes/registration.php');?>

<!--/Register-Form --> 

<!--Forgot-password-Form -->
<?php include('includes/forgotpassword.php');?>

<script src="assets/js/jquery.min.js"></script>
<script src="assets/js/bootstrap.min.js"></script> 
<script src="assets/js/interface.js"></script> 
<script src="assets/switcher/js/switcher.js"></script>
<script src="assets/js/bootstrap-slider.min.js"></script> 
<script src="assets/js/slick.min.js"></script> 
<script src="assets/js/owl.carousel.min.js"></script>

<script>
// ---- Wishlist toggle ----
function toggleWishlist(vhid, btn) {
    $.post('wishlist-action.php', { vhid: vhid }, function(data) {
        var res = JSON.parse(data);
        if (res.status === 'added') {
            $(btn).css('color', '#c62828');
        } else if (res.status === 'removed') {
            $(btn).css('color', '#bbb');
        } else if (res.status === 'error') {
            alert(res.message);
        }
    });
}

// ---- Simple availability calendar (current month) ----
(function() {
    var bookedRanges = <?php echo json_encode($bookedRanges); ?>;

    function toDateOnly(d) { return new Date(d.getFullYear(), d.getMonth(), d.getDate()); }

    function isBooked(date) {
        for (var i = 0; i < bookedRanges.length; i++) {
            var from = toDateOnly(new Date(bookedRanges[i].FromDate));
            var to = toDateOnly(new Date(bookedRanges[i].ToDate));
            if (date >= from && date <= to) return true;
        }
        return false;
    }

    function renderCalendar() {
        var today = new Date();
        var year = today.getFullYear();
        var month = today.getMonth();
        var firstDay = new Date(year, month, 1);
        var daysInMonth = new Date(year, month + 1, 0).getDate();
        var startOffset = firstDay.getDay();

        var html = '<div style="font-weight:bold; text-align:center; margin-bottom:6px;">' +
            firstDay.toLocaleString('default', { month: 'long' }) + ' ' + year + '</div>';
        html += '<div style="display:grid; grid-template-columns:repeat(7,1fr); gap:3px; font-size:11px;">';
        ['S','M','T','W','T','F','S'].forEach(function(d){ html += '<div style="text-align:center;color:#999;">' + d + '</div>'; });

        for (var i = 0; i < startOffset; i++) html += '<div></div>';

        for (var day = 1; day <= daysInMonth; day++) {
            var date = new Date(year, month, day);
            var booked = isBooked(date);
            var bg = booked ? '#f8d7da' : '#d4edda';
            var color = booked ? '#c62828' : '#2e7d32';
            html += '<div style="text-align:center; padding:4px 0; border-radius:3px; background:' + bg + '; color:' + color + ';">' + day + '</div>';
        }
        html += '</div>';
        document.getElementById('availCalendar').innerHTML = html;
    }

    renderCalendar();
})();
</script>

</body>
</html>