<?php
// availability.php
require_once '../config.php';
//Mengecek apakah request yang diterima adalah metode GET
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    $selectedDate = $_GET["reservation_date"]; // Tanggal yang dipilih
    $head_count = $_GET["head_count"];  // Jumlah orang
    $selectedTime = date("H:i:s", strtotime($_GET["reservation_time"]));
    // Mendapatkan Data Reservasi yang Sudah Ada
    $reservedQuery = "SELECT * FROM reservations WHERE reservation_date = '$selectedDate' AND reservation_time = '$selectedTime'";
    $reservedResult = mysqli_query($link, $reservedQuery);
    // Mengumpulkan Data Meja yang Sudah Dipesan
    $reservedTableIDs = array();
    // Menyimpan daftar table_id yang sudah dipesan.
    if ($reservedResult) {
        while ($row = mysqli_fetch_assoc($reservedResult)) {
            $reservedTableIDs[] = $row["table_id"];
            // PMengambil setiap baris hasil query dalam bentuk array asosiatif.
            echo "Reservation Time: " . $row["reservation_time"] . "<br>";
            echo "Reservation ID: " . $row["reservation_id"] . "<br>";
            echo "Table ID: " . $row["table_id"] . "<br>";
            echo "Reservation Date: " . $row["reservation_date"] . "<br>";
            echo "Head Count: " . $row["head_count"] . "<br>";
            echo "<br>"; // Add spacing between rows
        }
    } else {echo "Query failed: " . mysqli_error($link);}
    // Memeriksa Meja yang Tersedia
    if (!empty($reservedTableIDs)) {
        //Menggabungkan ID meja yang sudah dipesan menjadi string terpisah koma (1,2,3).
        $reservedTableIDsString = implode(",", $reservedTableIDs);
        //Query ini mencari meja Yang tidak ada di daftar reservedTableIDs
        $availableTables = "SELECT table_id, capacity FROM restaurant_tables WHERE capacity >= '$head_count' AND table_id NOT IN ($reservedTableIDsString)";
        $availableResult = mysqli_query($link, $availableTables);
        //Menampilkan Meja yang Tersedia
        if ($availableResult) {
            while ($row = mysqli_fetch_assoc($availableResult)) {
                //Menampilkan ID meja yang tersedia beserta kapasitasnya
                echo "Available Table ID: " . $row["table_id"] . "<br>";
                echo "Capacity: " . $row["capacity"] . "<br>";}
            // Construct the reservation link with all table IDs
            $reservedTableIDsString = implode(",", $reservedTableIDs);
            $reservationLink = "reservePage.php?reservation_date=$selectedDate&head_count=$head_count&reservation_time=$selectedTime&reserved_table_id=$reservedTableIDsString";
            // Add header link to reservationPage.php with parameters
            header("Location: $reservationLink");
            exit();
        } else {echo "Available tables query failed: " . mysqli_error($link);}
    } else {
        //Jika Tidak Ada Meja yang Dipesan
        $reservationLink = "reservePage.php?reservation_date=$selectedDate&head_count=$head_count&reservation_time=$selectedTime&reserved_table_id=0";
        header("Location: $reservationLink");
    }
}
?>
