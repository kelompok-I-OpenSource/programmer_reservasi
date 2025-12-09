<?php
require_once '../config.php';
session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/edit.css">
    <title>Form Reservasi</title>
</head>

<body>
    <?php
    $reservationStatus = $_GET['reservation'] ?? null;
    $message = '';
    if ($reservationStatus === 'success') {
        $message = "Reservasi Berhasil!";
        $reservation_id = $_GET['reservation_id'] ?? null;
        echo '<a class="nav-link" href="../home/home.php#hero">' .
            '<h1 class="text-center" style="font-family: Copperplate; color: whitesmoke;"></h1>' .
            '<span class="sr-only"></span></a>';
        echo '<script>alert("Reservasi Berhasil"); </script>';
    }
    $head_count = $_GET['head_count'] ?? 1;
    ?>
    <div class="member-info"></div>
    <div class="reserve-container">
        <a class="nav-link" href="../home/home.php#hero">
            <h1 class="text-center">ANGELATO</h1>
            <span class="sr-only"></span>
        </a>
        <div class="row">
            <div class="column">
                <div id="Search Table">
                    <h2 class="elegant-heading">Waktu Kedatangan</h2>
                    <form id="reservation-form" method="GET" action="availability.php">
                        <div class="form-group">
                            <label for="reservation_date">Tanggal</label><br>
                            <input class="form-control" type="date" id="reservation_date" name="reservation_date" required>
                        </div>
                        <div class="form-group">
                            <label for="reservation_time">Ketersediaan Waktu</label>
                            <div id="availability-table">
                                <?php
                                $availableTimes = array();
                                for ($hour = 12; $hour <= 24; $hour++) {
                                    for ($minute = 0; $minute < 30; $minute += 30) {
                                        $time = sprintf('%02d:%02d:00', $hour, $minute);
                                        $availableTimes[] = $time;
                                    }
                                }
                                echo '<select name="reservation_time" id="reservation_time" 
                                style="width:10em;" class="form-control">';
                                echo '<option value="" selected disabled>Pilih Waktu</option>';
                                foreach ($availableTimes as $time) {
                                    echo "<option value='$time'>$time</option>";
                                }
                                echo '</select>';
                                if (isset($_GET['message'])) {
                                    $message = $_GET['message'];
                                    echo "<p>$message</p>";
                                }
                                ?>
                            </div>
                        </div>
                        <input type="number" id="head_count" name="head_count" value=1 hidden required>
                        <button class="btn-custom" type="submit" name="submit" value="Login">Setuju</button>
                    </form>
                </div>
            </div>
            <div id="insert-reservation-into-table" style="display: none;">
                <h2 class="elegant-heading">Buat Reservasi</h2>
                <form id="reservation-form" method="POST" action="insertReservation.php">
                    <div class="form-group">
                        <label for="customer_name">Nama Anda</label><br>
                        <input class="form-control" type="text" id="customer_name" name="customer_name" required>
                    </div>
                    <div class="form-group">
                        <label for="customer_name">Nomor Telpon</label><br>
                        <input class="form-control" type="text" id="customer_phone" name="customer_phone" required>
                    </div>
                    <?php
                    $defaultReservationDate = $_GET['reservation_date'] ?? date("Y-m-d");
                    $defaultReservationTime = $_GET['reservation_time'] ?? "13:00:00";
                    ?>
                    <div class="form-group">
                        <label for="reservation_date">Tanggal Reservasi</label><br>
                        <input type="date" id="reservation_date" name="reservation_date" value="<?= $defaultReservationDate ?>" readonly required>
                        <input type="time" id="reservation_time" name="reservation_time" value="<?= $defaultReservationTime ?>" readonly required>
                    </div>
                    <div class="form-group">
                        <label for="table_id_reserve">Ketersediaan Meja</label>
                        <select class="form-control" name="table_id" id="table_id_reserve" style="width:10em;" required>
                            <option value="" selected disabled>Pilih Meja</option>
                            <?php
                            $table_id_list = $_GET['reserved_table_id'];
                            $head_count = $_GET['head_count'] ?? 1;
                            $reserved_table_ids = explode(',', $table_id_list);
                            $select_query_tables = "SELECT * FROM restaurant_tables WHERE capacity >= '$head_count'";
                            if (!empty($reserved_table_ids)) {
                                $reserved_table_ids_string = implode(',', $reserved_table_ids);
                                $select_query_tables .= " AND table_id NOT IN ($reserved_table_ids_string)";
                            }
                            $result_tables = mysqli_query($link, $select_query_tables);
                            $resultCheckTables = mysqli_num_rows($result_tables);
                            if ($resultCheckTables > 0) {
                                while ($row = mysqli_fetch_assoc($result_tables)) {
                                    echo '<option value="' . $row['table_id'] . '">For ' . $row['capacity'] . ' people. (Table Id: ' . $row['table_id'] . ')</option>';
                                }
                            } else {
                                echo '<option disabled>Semua Meja telah direservasi, silahkan mengganti waktu kedatangan anda.</option>';
                                echo '<script>alert("Semua Meja telah direservasi, silahkan mengganti waktu kedatangan anda.");</script>';
                            }
                            ?>
                        </select>
                        <input type="number" id="head_count" name="head_count" value="<?= $head_count ?>" required hidden>
                    </div>
                    <div class="form-group mb-3">
                        <label for="special_request">Spesial Permohonan</label><br>
                        <textarea class="form-control" id="special_request" name="special_request"></textarea><br>
                    </div>
                    <!-- akhir menu -->
                    <div class="form-group mb-3">
                        <button class="btn-custom" type="submit" name="submit" value="Login">Reservasi</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    </div>
    <script>
        // Check if reserved_table_id exists in URL
        document.addEventListener('DOMContentLoaded', function() {
            var urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('reserved_table_id')) {
                document.getElementById('insert-reservation-into-table').style.display = 'block';
            }
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // On reservePage: Save and restore data
            if (window.location.href.includes('reservePage.php')) {
                const fields = ['customer_name', 'customer_phone', 'special_request'];
                fields.forEach(field => {
                    const element = document.getElementById(field);
                    if (element) {
                        element.value = localStorage.getItem(field) || '';
                        element.addEventListener('input', function() {
                            localStorage.setItem(field, this.value);
                        });
                    }
                });
            }
        });
    </script>
</body>

</html>