<?php
session_start();
require_once '../config.php';
include '../inc/dashHeader.php';

$bill_id  = isset($_GET['bill_id']) ? (int)$_GET['bill_id'] : 0;
$table_id = isset($_GET['table_id']) ? (int)$_GET['table_id'] : 0;

function createNewBillRecord($table_id)
{
    global $link;

    $bill_time = date('Y-m-d H:i:s');
    $insert_query = "INSERT INTO Bills (table_id, bill_time) VALUES ($table_id, '$bill_time')";
    if ($link->query($insert_query) === TRUE) {
        return $link->insert_id;
    }
    return false;
}

// Pastikan bill_id valid: kalau tidak ada di Bills, buat baru
if ($bill_id <= 0) {
    $bill_id = createNewBillRecord($table_id);
} else {
    $checkBill = mysqli_query($link, "SELECT bill_id FROM Bills WHERE bill_id = $bill_id");
    if (!$checkBill || mysqli_num_rows($checkBill) === 0) {
        $bill_id = createNewBillRecord($table_id);
    }
}

// ambil informasi payment_time sekali saja, dipakai berkali-kali
$payment_time_query = "SELECT payment_time FROM Bills WHERE bill_id = $bill_id";
$payment_time_result = mysqli_query($link, $payment_time_query);
$has_payment_time = false;
if ($payment_time_result && mysqli_num_rows($payment_time_result) > 0) {
    $payment_time_row = mysqli_fetch_assoc($payment_time_result);
    if (!empty($payment_time_row['payment_time'])) {
        $has_payment_time = true;
    }
}
?>
<!DOCTYPE html>
<html>

<head>
    <link href="../css/pos.css" rel="stylesheet" />
</head>

<body>
    <div class="container-fluid">
        <div class="row">
            <!-- LEFT: MENU -->
            <div class="col-md-6 order-md-1 m-1" id="item-select-section">
                <div class="container-fluid pt-4 row" style="margin-left: 10rem; width: 81%;">
                    <div class="mt-5 mb-2">
                        <h3 class="pull-left">Food & Drinks</h3>
                    </div>

                    <!-- SEARCH -->
                    <div class="mb-3">
                        <form method="POST" action="">
                            <div class="row">
                                <div class="col-md-6">
                                    <input type="text" id="search" name="search" class="form-control"
                                        placeholder="Search Food & Drinks">
                                </div>
                                <div class="col-md-3">
                                    <button type="submit" class="btn btn-dark">Search</button>
                                </div>
                                <div class="col" style="text-align: right;">
                                    <a href="orderItem.php?bill_id=<?php echo $bill_id; ?>&table_id=<?php echo $table_id; ?>"
                                        class="btn btn-light">Show All</a>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- MENU LIST -->
                    <div style="max-height: 45rem; overflow-y: auto;">
                        <?php
                        if (isset($_POST['search']) && $_POST['search'] !== '') {
                            $search = mysqli_real_escape_string($link, $_POST['search']);
                            $query = "SELECT * FROM Menu 
                                  WHERE item_type LIKE '%$search%'
                                     OR item_category LIKE '%$search%'
                                     OR item_name LIKE '%$search%'
                                     OR item_id LIKE '%$search%'
                                  ORDER BY item_id";
                        } else {
                            $query = "SELECT * FROM Menu ORDER BY item_id";
                        }
                        $result = mysqli_query($link, $query);

                        if ($result && mysqli_num_rows($result) > 0) {
                            echo '<table class="table table-bordered table-striped w-auto mx-auto">';
                            echo '<thead>';
                            echo '<tr>';
                            echo '<th>ID</th>';
                            echo '<th>Item Name</th>';
                            echo '<th>Category</th>';
                            echo '<th>Price</th>';
                            echo '<th>Add</th>';
                            echo '</tr>';
                            echo '</thead>';
                            echo '<tbody>';

                            while ($row = mysqli_fetch_assoc($result)) {
                                echo '<tr>';
                                echo '<td>' . htmlspecialchars($row['item_id']) . '</td>';
                                echo '<td>' . htmlspecialchars($row['item_name']) . '</td>';
                                echo '<td>' . htmlspecialchars($row['item_category']) . '</td>';
                                echo '<td>' . number_format($row['item_price'], 2) . '</td>';

                                if (!$has_payment_time) {
                                    // SATU INPUT SAJA UNTUK QUANTITY
                                    echo '<td>
                                        <form method="get" action="addItem.php" class="form-inline">
                                            <input type="hidden" name="table_id" value="' . $table_id . '">
                                            <input type="hidden" name="item_id" value="' . htmlspecialchars($row['item_id']) . '">
                                            <input type="hidden" name="bill_id" value="' . $bill_id . '">
                                            <input type="number" name="quantity" class="form-control mr-1"
                                                   style="width: 120px"
                                                   placeholder="1 to 1000" required min="1" max="1000">
                                            <input type="hidden" name="addToCart" value="1">
                                            <button type="submit" class="btn btn-primary mt-1">Add to Cart</button>
                                        </form>
                                      </td>';
                                } else {
                                    echo '<td>Bill Paid</td>';
                                }

                                echo '</tr>';
                            }

                            echo '</tbody>';
                            echo '</table>';
                        } else {
                            echo '<div class="alert alert-danger"><em>No menu items were found.</em></div>';
                        }
                        ?>
                    </div>
                </div>
            </div>

            <!-- RIGHT: CART -->
            <div class="col-md-4 order-md-2 m-1" id="cart-section">
                <div class="container-fluid pt-5 row mt-3" style="max-width: 200%; width:150%;">
                    <div class="cart-section">
                        <h3>Cart</h3>

                        <div style="max-height: 40rem; overflow-y: auto;">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Item ID</th>
                                        <th>Item Name</th>
                                        <th>Price</th>
                                        <th>Quantity</th>
                                        <th>Total</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $cart_query = "SELECT bi.*, m.item_name, m.item_price
                                           FROM bill_items bi
                                           JOIN Menu m ON bi.item_id = m.item_id
                                           WHERE bi.bill_id = $bill_id";
                                    $cart_result = mysqli_query($link, $cart_query);
                                    $cart_total = 0;
                                    $tax = 0.1;

                                    if ($cart_result && mysqli_num_rows($cart_result) > 0) {
                                        while ($cart_row = mysqli_fetch_assoc($cart_result)) {
                                            $item_id      = $cart_row['item_id'];
                                            $item_name    = $cart_row['item_name'];
                                            $item_price   = $cart_row['item_price'];
                                            $quantity     = $cart_row['quantity'];
                                            $total        = $item_price * $quantity;
                                            $bill_item_id = $cart_row['bill_item_id'];
                                            $cart_total  += $total;

                                            echo '<tr>';
                                            echo '<td>' . htmlspecialchars($item_id) . '</td>';
                                            echo '<td>' . htmlspecialchars($item_name) . '</td>';
                                            echo '<td>Rp ' . number_format($item_price, 2) . '</td>';
                                            echo '<td>' . $quantity . '</td>';
                                            echo '<td>Rp ' . number_format($total, 2) . '</td>';

                                            if (!$has_payment_time) {
                                                echo '<td><a class="btn btn-dark"
                                                     href="deleteItem.php?bill_id=' . $bill_id .
                                                    '&table_id=' . $table_id .
                                                    '&bill_item_id=' . $bill_item_id .
                                                    '&item_id=' . urlencode($item_id) . '">
                                                Delete</a></td>';
                                            } else {
                                                echo '<td>Bill Paid</td>';
                                            }

                                            echo '</tr>';
                                        }
                                    } else {
                                        echo '<tr><td colspan="6">No Items in Cart.</td></tr>';
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- TOTALS -->
                        <hr>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <tbody>
                                    <tr>
                                        <td><strong>Cart Total</strong></td>
                                        <td>Rp <?php echo number_format($cart_total, 2); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Cart Taxed</strong></td>
                                        <td>Rp <?php echo number_format($cart_total * $tax, 2); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Grand Total</strong></td>
                                        <td>Rp <?php echo number_format(($tax * $cart_total) + $cart_total, 2); ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <?php
                        $grandTotal = ($tax * $cart_total) + $cart_total;

                        if ($has_payment_time) {
                            echo '<div class="alert alert-success" role="alert">
                                Bill has already been paid.
                            </div>';
                            echo '<a href="receipt.php?bill_id=' . $bill_id . '" class="btn btn-light">
                                Print Receipt <span class="fa fa-receipt text-black"></span></a>';
                        } elseif ($grandTotal > 0) {
                            echo '<a href="idValidity.php?bill_id=' . $bill_id . '" class="btn btn-success">
                                Pay Bill</a>';
                        } else {
                            echo '<h3>Add Item To Cart to Proceed</h3>';
                        }
                        ?>

                    </div>

                    <!-- NEW CUSTOMER BUTTON -->
                    <form class="mt-3" action="newCustomer.php" method="get">
                        <input type="hidden" name="table_id" value="<?php echo $table_id; ?>">
                        <button type="submit" name="new_customer" value="true" class="btn btn-warning">
                            New Customer
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php include '../inc/dashFooter.php'; ?>
</body>

</html>