<?php
session_start();
include('includes/config.php');
error_reporting(0);
header('Content-Type: application/json');

if (!isset($_SESSION['login'])) {
    echo json_encode(['status' => 'error', 'message' => 'Please login first']);
    exit();
}

$useremail = $_SESSION['login'];
$vhid = isset($_POST['vhid']) ? intval($_POST['vhid']) : 0;

if (!$vhid) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid vehicle']);
    exit();
}

// Check if already in wishlist
$check = $dbh->prepare("SELECT id FROM tblwishlist WHERE userEmail=:email AND VehicleId=:vhid");
$check->bindParam(':email', $useremail, PDO::PARAM_STR);
$check->bindParam(':vhid', $vhid, PDO::PARAM_INT);
$check->execute();

if ($check->rowCount() > 0) {
    // Already saved -> remove it
    $del = $dbh->prepare("DELETE FROM tblwishlist WHERE userEmail=:email AND VehicleId=:vhid");
    $del->bindParam(':email', $useremail, PDO::PARAM_STR);
    $del->bindParam(':vhid', $vhid, PDO::PARAM_INT);
    $del->execute();
    echo json_encode(['status' => 'removed']);
} else {
    // Not saved yet -> add it
    $ins = $dbh->prepare("INSERT INTO tblwishlist (userEmail, VehicleId) VALUES (:email, :vhid)");
    $ins->bindParam(':email', $useremail, PDO::PARAM_STR);
    $ins->bindParam(':vhid', $vhid, PDO::PARAM_INT);
    $ins->execute();
    echo json_encode(['status' => 'added']);
}
