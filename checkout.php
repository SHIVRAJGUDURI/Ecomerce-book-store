<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

include 'config.php';

session_start();

$user_id = $_SESSION['user_id'];

if (!isset($user_id)) {
    header('location:login.php');
    exit();
}

if (isset($_POST['order_btn'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $number = $_POST['number'];
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $method = mysqli_real_escape_string($conn, $_POST['method']);
    $address = mysqli_real_escape_string($conn, 'Flat No. ' . $_POST['flat'] . ', ' . $_POST['street'] . ', ' . $_POST['city'] . ', ' . $_POST['country'] . ' - ' . $_POST['pin_code']);
    $placed_on = date('d-M-Y');

    $cart_total = 0;
    $cart_products = [];

    $cart_query = mysqli_query($conn, "SELECT * FROM `cart` WHERE user_id = '$user_id'") or die(mysqli_error($conn));
    if (mysqli_num_rows($cart_query) > 0) {
        while ($cart_item = mysqli_fetch_assoc($cart_query)) {
            $cart_products[] = $cart_item['name'] . ' (' . $cart_item['quantity'] . ') ';
            $sub_total = ($cart_item['price'] * $cart_item['quantity']);
            $cart_total += $sub_total;
        }
    }

    $total_products = implode(', ', $cart_products);

    if ($cart_total == 0) {
        $message[] = 'Your cart is empty';
    } else {
        $insert_order = mysqli_query($conn, "INSERT INTO `orders`(user_id, name, number, email, method, address, total_products, total_price, placed_on) VALUES('$user_id', '$name', '$number', '$email', '$method', '$address', '$total_products', '$cart_total', '$placed_on')") or die(mysqli_error($conn));

        if ($insert_order) {
            mysqli_query($conn, "DELETE FROM `cart` WHERE user_id = '$user_id'") or die(mysqli_error($conn));

            // Sending order confirmation email
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'shivrajguduri98@gmail.com'; // Your email
                $mail->Password = 'ufcw tdxe oqbw eudy'; // Your app password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                $mail->setFrom('shivrajguduri98@gmail.com', 'Bookly');
                $mail->addAddress($email, $name);
                $mail->Subject = 'Order Confirmation';
                $mail->Body = "Dear $name,\n\nThank you for your order! Your order details are as follows:\n\nTotal Products: $total_products\nTotal Price: $$cart_total\nPayment Method: $method\nDelivery Address: $address\n\nWe will update you once your order is shipped.\n\nBest regards,\nShop Name";

                $mail->send();
                $message[] = 'Order placed successfully! A confirmation email has been sent.';
            } catch (Exception $e) {
                error_log('Mail could not be sent. Error: ' . $mail->ErrorInfo);
                $message[] = 'Order placed, but email could not be sent.';
            }
        } else {
            $message[] = 'Order could not be placed.';
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php include 'header.php'; ?>

<div class="heading">
    <h3>Checkout</h3>
    <p> <a href="home.php">Home</a> / Checkout </p>
</div>

<section class="display-order">
    <?php  
        $grand_total = 0;
        $select_cart = mysqli_query($conn, "SELECT * FROM `cart` WHERE user_id = '$user_id'") or die(mysqli_error($conn));
        if (mysqli_num_rows($select_cart) > 0) {
            while ($fetch_cart = mysqli_fetch_assoc($select_cart)) {
                $total_price = ($fetch_cart['price'] * $fetch_cart['quantity']);
                $grand_total += $total_price;
    ?>
    <p> <?php echo $fetch_cart['name']; ?> <span>(<?php echo '$' . $fetch_cart['price'] . '/- x ' . $fetch_cart['quantity']; ?>)</span> </p>
    <?php
        }
    } else {
        echo '<p class="empty">Your cart is empty</p>';
    }
    ?>
    <div class="grand-total"> Grand Total: <span>$<?php echo $grand_total; ?>/-</span> </div>
</section>

<section class="checkout">
    <form action="" method="post">
        <h3>Place Your Order</h3>
        <div class="flex">
            <div class="inputBox"><span>Your Name :</span><input type="text" name="name" required placeholder="Enter your name"></div>
            <div class="inputBox"><span>Your Number :</span><input type="number" name="number" required placeholder="Enter your number"></div>
            <div class="inputBox"><span>Your Email :</span><input type="email" name="email" required placeholder="Enter your email"></div>
            <div class="inputBox"><span>Payment Method :</span><select name="method"><option value="cash on delivery">Cash on Delivery</option><option value="credit card">Credit Card</option><option value="paypal">PayPal</option><option value="paytm">Paytm</option></select></div>
            <div class="inputBox"><span>Address :</span><input type="text" name="flat" required placeholder="Flat No."><input type="text" name="street" required placeholder="Street Name"><input type="text" name="city" required placeholder="City"><input type="text" name="country" required placeholder="Country"><input type="number" name="pin_code" required placeholder="PIN Code"></div>
        </div>
        <input type="submit" value="Order Now" class="btn" name="order_btn">
    </form>
</section>

<?php include 'footer.php'; ?>
<script src="js/script.js"></script>
</body>
</html>
