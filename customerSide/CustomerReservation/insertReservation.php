<?php
require_once '../config.php';
session_start();
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // mendapatkan data nilai dari
    $customer_name = $_POST["customer_name"];
    $customer_phone = $_POST["customer_phone"];
    $table_id = intval($_POST["table_id"]);
    $reservation_time = $_POST["reservation_time"];
    $reservation_date = $_POST["reservation_date"];
    $special_request = $_POST["special_request"];
    $currentTime = date('Y-m-d H:i:s');
    // Ambil kapasitas dari tabel restaurant_tables
    $capacity_query = "SELECT capacity FROM restaurant_tables WHERE table_id = '$table_id'";
    $capacity_result = mysqli_query($link, $capacity_query);
    if ($capacity_result && mysqli_num_rows($capacity_result) > 0) {
        $row = mysqli_fetch_assoc($capacity_result);
        $head_count = $row['capacity'];
        // Siapkan kueri SQL untuk dimasukkan ke dalam tabel Reservasi
        $insert_query1 = "INSERT INTO Reservations (customer_name, customer_phone, table_id, reservation_time, reservation_date, head_count, special_request) 
        VALUES ('$customer_name', '$customer_phone', '$table_id', '$reservation_time', '$reservation_date', '$head_count', '$special_request')";
        if (mysqli_query($link, $insert_query1)) {
            // Dapatkan reservation_id terakhir yang dimasukkan
            $reservation_id = mysqli_insert_id($link);
            // Masukkan reservasi ke Table_Availability
            $insert_query2 = "INSERT INTO Table_Availability (availability_id, table_id, reservation_date, reservation_time, status) 
            VALUES ('$reservation_id', '$table_id', '$reservation_date', '$reservation_time', 'no')";
            mysqli_query($link, $insert_query2);
            // Pengalihan dengan pesan sukses
            header("Location: success_create_reserve.php?reservation=success");
            exit();
        } else {
            echo '<script>alert("Error: ' . mysqli_error($link) . '")</script>';
        }
    } else {
        echo '<script>alert("Table not found or capacity not available")</script>';
    }
}
