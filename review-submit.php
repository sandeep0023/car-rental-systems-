<?php
session_start();
include('includes/config.php');
error_reporting(0);

if (!isset($_SESSION['login'])) {
    header('Location: car-listing.php');
    exit();
}

if (isset($_POST['submit_review'])) {
    $vhid = intval($_POST['vhid']);
    $rating = intval($_POST['rating']);
    $reviewtext = trim($_POST['reviewtext']);
    $useremail = $_SESSION['login'];

    if ($rating < 1) $rating = 1;
    if ($rating > 5) $rating = 5;

    $sql = "INSERT INTO tblreviews (VehicleId, userEmail, Rating, ReviewText) VALUES (:vhid, :email, :rating, :text)";
    $query = $dbh->prepare($sql);
    $query->bindParam(':vhid', $vhid, PDO::PARAM_INT);
    $query->bindParam(':email', $useremail, PDO::PARAM_STR);
    $query->bindParam(':rating', $rating, PDO::PARAM_INT);
    $query->bindParam(':text', $reviewtext, PDO::PARAM_STR);
    $query->execute();

    header('Location: vehical-details.php?vhid=' . $vhid . '#reviews');
    exit();
}
header('Location: car-listing.php');
exit();
