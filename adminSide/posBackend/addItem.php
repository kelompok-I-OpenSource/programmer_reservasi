<?php
require_once '../config.php';

if (isset($_GET['addToCart'])) {

    // --- Ambil & sanitasi data ---
    $bill_id   = isset($_GET['bill_id']) ? (int)$_GET['bill_id'] : 0;
    $item_id   = isset($_GET['item_id']) ? mysqli_real_escape_string($link, $_GET['item_id']) : '';
    $quantity  = isset($_GET['quantity']) ? (int)$_GET['quantity'] : 1;
    $table_id  = isset($_GET['table_id']) ? (int)$_GET['table_id'] : 0;
    $currentTime = date('Y-m-d H:i:s');

    // Validasi dasar
    if ($table_id <= 0 || $item_id === '' || $quantity <= 0) {
        echo '<script>alert("Data tidak valid");</script>';
        exit;
    }

    // ================== PASTIKAN BILL-NYA ADA ==================
    // Cek apakah bill dengan bill_id itu memang ada dan milik table_id ini
    $checkBillSql = "
        SELECT bill_id 
        FROM Bills 
        WHERE bill_id = $bill_id 
          AND table_id = $table_id
        LIMIT 1
    ";
    $checkBillRes = mysqli_query($link, $checkBillSql);

    if (!$checkBillRes || mysqli_num_rows($checkBillRes) == 0) {
        // Kalau tidak ada, buat bill baru (abaikan bill_id yg dikirim dari GET)
        $createBillSql = "
            INSERT INTO Bills (table_id, bill_time, total_amount)
            VALUES ($table_id, '$currentTime', 0)
        ";
        if (!mysqli_query($link, $createBillSql)) {
            echo '<script>alert("Gagal membuat bill: ' . mysqli_error($link) . '");</script>';
            exit;
        }

        // pakai bill_id yang baru dibuat
        $bill_id = mysqli_insert_id($link);
    }

    // ================== LOGIC ADD / UPDATE ITEM ==================
    // Cek apakah item ini sudah ada di bill_items untuk bill_id tersebut
    $select_sql = "
        SELECT *
        FROM bill_items
        WHERE bill_id = $bill_id
          AND item_id = '$item_id'
    ";
    $result = mysqli_query($link, $select_sql);

    if ($result) {
        if (mysqli_num_rows($result) > 0) {
            // Record sudah ada → update quantity
            $update_quantity_sql = "
                UPDATE bill_items
                SET quantity = quantity + $quantity
                WHERE bill_id = $bill_id
                  AND item_id = '$item_id'
            ";

            if (mysqli_query($link, $update_quantity_sql)) {
                // Update tabel Kitchen juga
                $update_kitchen_sql = "
                    UPDATE Kitchen
                    SET quantity = quantity + $quantity,
                        time_submitted = '$currentTime'
                    WHERE table_id = $table_id
                      AND item_id = '$item_id'
                ";
                mysqli_query($link, $update_kitchen_sql);

                // redirect setelah sukses
                header("Location: orderItem.php?bill_id=" . urlencode($bill_id) . "&table_id=" . $table_id);
                exit;
            } else {
                echo '<script>alert("Error updating quantity: ' . mysqli_error($link) . '");</script>';
            }
        } else {
            // Record belum ada → insert baru
            $insert_item_sql = "
                INSERT INTO bill_items (bill_id, item_id, quantity)
                VALUES ($bill_id, '$item_id', $quantity)
            ";

            $insert_kitchen_sql = "
                INSERT INTO Kitchen (table_id, item_id, quantity, time_submitted)
                VALUES ($table_id, '$item_id', $quantity, '$currentTime')
            ";

            if (mysqli_query($link, $insert_item_sql) && mysqli_query($link, $insert_kitchen_sql)) {
                header("Location: orderItem.php?bill_id=" . urlencode($bill_id) . "&table_id=" . $table_id);
                exit;
            } else {
                echo '<script>alert("Error adding item to cart: ' . mysqli_error($link) . '");</script>';
            }
        }
    } else {
        echo '<script>alert("Error checking bill item: ' . mysqli_error($link) . '");</script>';
    }
}
