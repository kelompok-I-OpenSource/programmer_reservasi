<?php
// Menentukan variabelnya
$iconClass = 'fa-check-circle'; // Nilai ini menunjukkan keberhasilan
$cardClass = 'alert-success';   // Nilai ini menunjukkan kartu pesan sukses
$bgColor = "#D4F4DD";
?>
<!DOCTYPE html>
<html>
<head>
    <link href="https://fonts.googleapis.com/css?family=Nunito+Sans:400,400i,700,900&display=swap" rel="stylesheet">
    <style>
        /*menyertakan font khusus, dalam hal ini Nunito Sans, dari Google Fonts ke dalam halaman web Anda. */
        /* Your custom CSS styles for the success message card here */
        body {
            text-align: center;
            padding: 40px 0;
            background: #EBF0F5;
        }
        h1 {
            color: #88B04B;
            font-family: "Nunito Sans", "Helvetica Neue", sans-serif;
            font-weight: 900;
            font-size: 40px;
            margin-bottom: 10px;
        }
        p {
            color: #404F5E;
            font-family: "Nunito Sans", "Helvetica Neue", sans-serif;
            font-size: 20px;
            margin: 0;
        }
        i.checkmark {
            color: #9ABC66;
            font-size: 100px;
            line-height: 200px;
            margin-left: -15px;
        }
        .card {
            background: white;
            padding: 60px;
            border-radius: 4px;
            box-shadow: 0 2px 3px #C8D0D8;
            display: inline-block;
            margin: 0 auto;
        }
        /* Gaya CSS tambahan berdasarkan pesan sukses/kesalahan*/
        .alert-success {
            /* Sesuaikan gaya untuk kartu pesan sukses */
            background-color: <?php echo $bgColor; ?>;
        }
        .alert-success i {
            color: #5DBE6F; /* Sesuaikan warna ikon tanda centang untuk keberhasilan */
        }
        .alert-danger {
            /* Sesuaikan gaya untuk kartu pesan kesalahan */
            background-color: #FFA7A7; /* Warna latar belakang khusus untuk kesalahan */
        }
        .alert-danger i {
            color: #F25454; /* Sesuaikan warna ikon tanda centang untuk kesalahan */
        }
        .custom-x {
            color: #F25454; /* Sesuaikan warna simbol "X" untuk kesalahan */
            font-size: 100px;
            line-height: 200px;
        }
    </style>
</head>
<body>
    <div class="card <?php echo $cardClass; ?>" style="display: none;">
        <div style="border-radius: 200px; height: 200px; width: 200px; background: #F8FAF5; margin: 0 auto;">
            <?php if ($iconClass === 'fa-check-circle'): ?>
                <i class="checkmark">✓</i>
            <?php else: ?>
                <i class="custom-x" style="font-size: 100px; line-height: 200px;">✘</i>
            <?php endif; ?>
        </div>
        <h1><?php echo ($cardClass === 'alert-success') ? 'Success' : 'Error'; ?></h1>
        <p>Reservasi Berhasil Dibuat!</p>
    </div>
    <div style="text-align: center; margin-top: 20px;">Kembali <span id="countdown">3</span></div>
    <script>
        // Fungsi untuk menampilkan kartu pesan sebagai pop-up dan memulai hitungan mundur
        function showPopup() {
            var messageCard = document.querySelector(".card");
            messageCard.style.display = "block";
            var i = 3;
            var countdownElement = document.getElementById("countdown");
            var countdownInterval = setInterval(function() {
                i--;
                countdownElement.textContent = i;
                if (i <= 0) {
                    clearInterval(countdownInterval);
                    window.location.href = "../CustomerReservation/reservePage.php";
                }
            }, 1000); // 1000 milliseconds = 1 second
        }
        // Tampilkan kartu pesan dan mulai hitung mundur saat halaman dimuat
        window.onload = showPopup;
        // Fungsi untuk menyembunyikan kartu pesan setelah penundaan
        function hidePopup() {
            var messageCard = document.querySelector(".card");
            messageCard.style.display = "none";
            // Diarahkan ke halaman lain setelah menyembunyikan pop-up
            setTimeout(function () {
                window.location.href = "../panel/reservation-panel.php";
            }, 3000); // 3000 milliseconds = 3 seconds
        }
        // Sembunyikan kartu pesan setelah 3 detik
        setTimeout(hidePopup, 3000);
    </script>
</body>
</html>