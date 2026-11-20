<?php 
session_start();
include('includes/config.php');
error_reporting(0);
?>

<!DOCTYPE HTML>
<html lang="en">
<head>

<title>Car Rental Portal | Car Listing</title>
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
<!--Leaflet Map (free, no API key needed) -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<!-- SWITCHER -->
		<link rel="stylesheet" id="switcher-css" type="text/css" href="assets/switcher/css/switcher.css" media="all" />
		<link rel="alternate stylesheet" type="text/css" href="assets/switcher/css/red.css" title="red" media="all" data-default-color="true" />
		<link rel="alternate stylesheet" type="text/css" href="assets/switcher/css/orange.css" title="orange" media="all" />
		<link rel="alternate stylesheet" type="text/css" href="assets/switcher/css/blue.css" title="blue" media="all" />
		<link rel="alternate stylesheet" type="text/css" href="assets/switcher/css/pink.css" title="pink" media="all" />
		<link rel="alternate stylesheet" type="text/css" href="assets/switcher/css/green.css" title="green" media="all" />
		<link rel="alternate stylesheet" type="text/css" href="assets/switcher/css/purple.css" title="purple" media="all" />
        
<!-- Fav and touch icons -->
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

<!--Page Header-->
<section class="page-header listing_page">
  <div class="container">
    <div class="page-header_wrap">
      <div class="page-heading">
        <h1>Car Listing</h1>
      </div>
      <ul class="coustom-breadcrumb">
        <li><a href="#">Home</a></li>
        <li>Car Listing</li>
      </ul>
    </div>
  </div>
  <!-- Dark Overlay-->
  <div class="dark-overlay"></div>
</section>
<!-- /Page Header--> 

<!--Listing-->
<section class="listing-page">
  <div class="container">
    <div class="row">
      <div class="col-md-9 col-md-push-3">
        <div class="result-sorting-wrapper">
          <div class="sorting-count">
<?php
//Location filter (GET)
$selectedLocation = isset($_GET['location']) ? trim($_GET['location']) : '';
$locWhere = '';
if ($selectedLocation !== '') {
    $locWhere = " WHERE Location = :loc";
}

//Query for Listing count
$sql = "SELECT id from tblvehicles" . $locWhere;
$query = $dbh -> prepare($sql);
if ($selectedLocation !== '') { $query->bindParam(':loc', $selectedLocation, PDO::PARAM_STR); }
$query->execute();
$results=$query->fetchAll(PDO::FETCH_OBJ);
$cnt=$query->rowCount();
?>
<p><span><?php echo htmlentities($cnt);?> Listings</span></p>
</div>
<div class="sorting-form" style="padding:10px 15px;">
  <form method="get" action="" id="locationFilterForm" style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
    <label style="margin:0; font-weight:600;"><i class="fa fa-map-marker" aria-hidden="true"></i> Pickup Location:</label>
    <select name="location" id="locationSelect" class="form-control" onchange="this.form.submit()" style="width:auto; display:inline-block;">
      <option value="">All Locations</option>
      <?php
      $locSql = "SELECT DISTINCT Location FROM tblvehicles WHERE Location IS NOT NULL AND Location <> '' ORDER BY Location ASC";
      $locQuery = $dbh->prepare($locSql);
      $locQuery->execute();
      $locList = $locQuery->fetchAll(PDO::FETCH_OBJ);
      foreach ($locList as $loc) {
          $sel = ($selectedLocation === $loc->Location) ? 'selected' : '';
          echo '<option value="'.htmlentities($loc->Location).'" '.$sel.'>'.htmlentities($loc->Location).'</option>';
      }
      ?>
    </select>
    <button type="button" class="btn btn-primary btn-sm" onclick="toggleMap()"><i class="fa fa-map" aria-hidden="true"></i> Pin My Location on Map</button>
  </form>
  <div id="mapPickerWrap" style="display:none; margin-top:12px;">
    <p style="margin:0 0 8px; color:#666;">Click anywhere on the map to pin your location — we'll show cars available near you.</p>
    <div id="pickerMap" style="height:300px; border-radius:6px;"></div>
    <p id="pickedCityText" style="margin-top:8px; font-weight:600;"></p>
  </div>
</div>
</div>

<script>
// Known pickup cities with approximate coordinates (used to match nearest city to the pinned point)
var knownCities = [
  {name: "Coimbatore", lat: 11.0168, lng: 76.9558},
  {name: "Chennai",    lat: 13.0827, lng: 80.2707},
  {name: "Bangalore",  lat: 12.9716, lng: 77.5946},
  {name: "Madurai",    lat: 9.9252,  lng: 78.1198},
  {name: "Salem",      lat: 11.6643, lng: 78.1460},
  {name: "Trichy",     lat: 10.7905, lng: 78.7047}
];

var pickerMap, pickerMarker;

function toggleMap() {
  var wrap = document.getElementById('mapPickerWrap');
  if (wrap.style.display === 'none') {
    wrap.style.display = 'block';
    if (!pickerMap) {
      initPickerMap();
    } else {
      setTimeout(function(){ pickerMap.invalidateSize(); }, 100);
    }
  } else {
    wrap.style.display = 'none';
  }
}

function initPickerMap() {
  pickerMap = L.map('pickerMap').setView([11.0168, 76.9558], 6); // default center: Coimbatore
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap contributors'
  }).addTo(pickerMap);

  pickerMap.on('click', function(e) {
    placePin(e.latlng.lat, e.latlng.lng);
  });

  // Try to use the browser's current location automatically
  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(function(pos) {
      var lat = pos.coords.latitude, lng = pos.coords.longitude;
      pickerMap.setView([lat, lng], 8);
      placePin(lat, lng);
    }, function() { /* user denied or unavailable - do nothing, they can click manually */ });
  }
}

function placePin(lat, lng) {
  if (pickerMarker) {
    pickerMarker.setLatLng([lat, lng]);
  } else {
    pickerMarker = L.marker([lat, lng]).addTo(pickerMap);
  }

  // Find nearest known city (simple straight-line distance)
  var nearest = null, nearestDist = Infinity;
  knownCities.forEach(function(city) {
    var d = Math.pow(city.lat - lat, 2) + Math.pow(city.lng - lng, 2);
    if (d < nearestDist) { nearestDist = d; nearest = city; }
  });

  document.getElementById('pickedCityText').innerHTML =
    '<i class="fa fa-check-circle" style="color:green;"></i> Nearest pickup location: <strong>' + nearest.name + '</strong> — showing cars there.';

  // Auto-select in dropdown and submit after a short pause so user sees the match
  document.getElementById('locationSelect').value = nearest.name;
  setTimeout(function() {
    document.getElementById('locationFilterForm').submit();
  }, 900);
}
</script>

<?php $sql = "SELECT tblvehicles.*,tblbrands.BrandName,tblbrands.id as bid  from tblvehicles join tblbrands on tblbrands.id=tblvehicles.VehiclesBrand" . $locWhere;
$query = $dbh -> prepare($sql);
if ($selectedLocation !== '') { $query->bindParam(':loc', $selectedLocation, PDO::PARAM_STR); }
$query->execute();
$results=$query->fetchAll(PDO::FETCH_OBJ);
$cnt=1;
if($query->rowCount() > 0)
{
foreach($results as $result)
{
    $ratingQ = $dbh->prepare("SELECT AVG(Rating) as avgrating, COUNT(*) as cnt FROM tblreviews WHERE VehicleId = :vhid");
    $ratingQ->bindParam(':vhid', $result->id, PDO::PARAM_INT);
    $ratingQ->execute();
    $ratingInfo = $ratingQ->fetch(PDO::FETCH_OBJ);
    $avgRating = $ratingInfo->avgrating ? round($ratingInfo->avgrating, 1) : 0;

    $wished = false;
    if (isset($_SESSION['login'])) {
        $wQ = $dbh->prepare("SELECT id FROM tblwishlist WHERE userEmail=:email AND VehicleId=:vhid");
        $wQ->bindParam(':email', $_SESSION['login'], PDO::PARAM_STR);
        $wQ->bindParam(':vhid', $result->id, PDO::PARAM_INT);
        $wQ->execute();
        $wished = $wQ->rowCount() > 0;
    }
?>
        <div class="product-listing-m gray-bg">
          <div class="product-listing-img" style="position:relative;">
            <img src="admin/img/vehicleimages/<?php echo htmlentities($result->Vimage1);?>" class="img-responsive" alt="Image" />
            <button onclick="toggleWishlist(<?php echo htmlentities($result->id); ?>, this)" style="position:absolute; top:8px; right:8px; border:none; background:rgba(255,255,255,.9); border-radius:50%; width:32px; height:32px; cursor:pointer; color:<?php echo $wished ? '#c62828' : '#bbb'; ?>;">
              <i class="fa fa-heart" aria-hidden="true"></i>
            </button>
          </div>
          <div class="product-listing-content">
            <h5><a href="vehical-details.php?vhid=<?php echo htmlentities($result->id);?>"><?php echo htmlentities($result->BrandName);?> , <?php echo htmlentities($result->VehiclesTitle);?></a></h5>
            <p class="list-price">$<?php echo htmlentities($result->PricePerDay);?> Per Day</p>
            <div style="color:#f5a623; font-size:13px; margin-bottom:6px;">
              <?php for ($i = 1; $i <= 5; $i++): ?><i class="fa fa-star<?php echo $i <= round($avgRating) ? '' : '-o'; ?>" aria-hidden="true"></i><?php endfor; ?>
              <span style="color:#888;"><?php echo $avgRating; ?></span>
            </div>
            <?php if (!empty($result->Location)): ?>
            <p style="color:#888; font-size:13px; margin:0 0 6px;"><i class="fa fa-map-marker" aria-hidden="true"></i> <?php echo htmlentities($result->Location);?></p>
            <?php endif; ?>
            <ul>
              <li><i class="fa fa-user" aria-hidden="true"></i><?php echo htmlentities($result->SeatingCapacity);?> seats</li>
              <li><i class="fa fa-calendar" aria-hidden="true"></i><?php echo htmlentities($result->ModelYear);?> model</li>
              <li><i class="fa fa-car" aria-hidden="true"></i><?php echo htmlentities($result->FuelType);?></li>
            </ul>
            <a href="vehical-details.php?vhid=<?php echo htmlentities($result->id);?>" class="btn">View Details <span class="angle_arrow"><i class="fa fa-angle-right" aria-hidden="true"></i></span></a>
          </div>
        </div>
      <?php }} ?>
         </div>
      
      <!--Side-Bar-->
      <aside class="col-md-3 col-md-pull-9">
        <div class="sidebar_widget">
          <div class="widget_heading">
            <h5><i class="fa fa-filter" aria-hidden="true"></i> Find Your  Car </h5>
          </div>
          <div class="sidebar_filter">
            <form action="search-carresult.php" method="post">
              <div class="form-group select">
                <select class="form-control" name="brand">
                  <option>Select Brand</option>

                  <?php $sql = "SELECT * from  tblbrands ";
$query = $dbh -> prepare($sql);
$query->execute();
$results=$query->fetchAll(PDO::FETCH_OBJ);
$cnt=1;
if($query->rowCount() > 0)
{
foreach($results as $result)
{       ?>  
<option value="<?php echo htmlentities($result->id);?>"><?php echo htmlentities($result->BrandName);?></option>
<?php }} ?>
                 
                </select>
              </div>
              <div class="form-group select">
                <select class="form-control" name="fueltype">
                  <option>Select Fuel Type</option>
<option value="Petrol">Petrol</option>
<option value="Diesel">Diesel</option>
<option value="CNG">CNG</option>
                </select>
              </div>
             
              <div class="form-group">
                <button type="submit" class="btn btn-block"><i class="fa fa-search" aria-hidden="true"></i> Search Car</button>
              </div>
            </form>
          </div>
        </div>

        <div class="sidebar_widget">
          <div class="widget_heading">
            <h5><i class="fa fa-car" aria-hidden="true"></i> Recently Listed Cars</h5>
          </div>
          <div class="recent_addedcars">
            <ul>
<?php $sql = "SELECT tblvehicles.*,tblbrands.BrandName,tblbrands.id as bid  from tblvehicles join tblbrands on tblbrands.id=tblvehicles.VehiclesBrand order by id desc limit 4";
$query = $dbh -> prepare($sql);
$query->execute();
$results=$query->fetchAll(PDO::FETCH_OBJ);
$cnt=1;
if($query->rowCount() > 0)
{
foreach($results as $result)
{  ?>

              <li class="gray-bg">
                <div class="recent_post_img"> <a href="vehical-details.php?vhid=<?php echo htmlentities($result->id);?>"><img src="admin/img/vehicleimages/<?php echo htmlentities($result->Vimage1);?>" alt="image"></a> </div>
                <div class="recent_post_title"> <a href="vehical-details.php?vhid=<?php echo htmlentities($result->id);?>"><?php echo htmlentities($result->BrandName);?> , <?php echo htmlentities($result->VehiclesTitle);?></a>
                  <p class="widget_price">$<?php echo htmlentities($result->PricePerDay);?> Per Day</p>
                </div>
              </li>
              <?php }} ?>
              
            </ul>
          </div>
        </div>
      </aside>
      <!--/Side-Bar--> 
    </div>
  </div>
</section>
<!-- /Listing--> 

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

<!-- Scripts --> 
<script src="assets/js/jquery.min.js"></script>
<script src="assets/js/bootstrap.min.js"></script> 
<script src="assets/js/interface.js"></script> 
<!--Switcher-->
<script src="assets/switcher/js/switcher.js"></script>
<!--bootstrap-slider-JS--> 
<script src="assets/js/bootstrap-slider.min.js"></script> 
<!--Slider-JS--> 
<script src="assets/js/slick.min.js"></script> 
<script src="assets/js/owl.carousel.min.js"></script>

<script>
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
</script>

</body>
</html>
