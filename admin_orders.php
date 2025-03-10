<?php
include 'config.php';
session_start();

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

$admin_id = $_SESSION['admin_id'] ?? null;

if (!$admin_id) {
    header('location:login.php');
    exit();
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

// Handle Order Update
if (isset($_POST['update_order'])) {
    $order_update_id = $_POST['order_id'] ?? '';
    $update_payment = $_POST['update_payment'] ?? '';

    if (!empty($order_update_id) && !empty($update_payment)) {
        $update_query = "UPDATE `orders` SET payment_status = '$update_payment' WHERE id = '$order_update_id'";
        
        if (mysqli_query($conn, $update_query)) {
            $order_query = mysqli_query($conn, "SELECT * FROM `orders` WHERE id = '$order_update_id'");
            
            if ($order_data = mysqli_fetch_assoc($order_query)) {
                $user_email = $order_data['email'];
                $user_name = $order_data['name'];
                $total_products = $order_data['total_products'];
                $total_price = $order_data['total_price'];
                $payment_method = $order_data['method'];
                $order_status = $update_payment;

                // Send Email Notification
                $mail = new PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'shivrajguduri98@gmail.com';
                    $mail->Password = 'ufcw tdxe oqbw eudy';
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port = 587;

                    $mail->setFrom('shivrajguduri98@gmail.com', 'Shop Admin');
                    $mail->addAddress($user_email, $user_name);
                    $mail->Subject = 'Order Status Update';
                    $mail->Body = "Dear $user_name,\n\nYour order status has been updated.\n\nOrder Details:\nTotal Products: $total_products\nTotal Price: $$total_price\nPayment Method: $payment_method\nNew Payment Status: $order_status\n\nThank you for shopping with us!\n\nBest regards,\nShop Admin";

                    $mail->send();
                } catch (Exception $e) {
                    error_log('Mail could not be sent. Error: ' . $mail->ErrorInfo);
                }
            }

            header("Location: admin_orders.php");
            exit();
        } else {
            echo "Error updating order: " . mysqli_error($conn);
        }
    }
}

// Handle Order Deletion
if (isset($_GET['delete'])) {
    $delete_id = $_GET['delete'];
    if (!empty($delete_id)) {
        mysqli_query($conn, "DELETE FROM `orders` WHERE id = '$delete_id'") or die('Query failed');
        header('location:admin_orders.php');
        exit();
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders</title>

    <!-- Font Awesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Custom Admin CSS -->
    <link rel="stylesheet" href="css/admin_style.css">
</head>
<body>

<?php include 'admin_header.php'; ?>

<section class="orders">
    <h1 class="title">Placed Orders</h1>

    <div class="box-container">
        <?php
        $select_orders = mysqli_query($conn, "SELECT * FROM `orders`");
        
        if ($select_orders && mysqli_num_rows($select_orders) > 0) {
            while ($fetch_orders = mysqli_fetch_assoc($select_orders)) {
        ?>
        <div class="box">
            <p> User ID: <span><?php echo htmlspecialchars($fetch_orders['user_id']); ?></span> </p>
            <p> Placed On: <span><?php echo htmlspecialchars($fetch_orders['placed_on']); ?></span> </p>
            <p> Name: <span><?php echo htmlspecialchars($fetch_orders['name']); ?></span> </p>
            <p> Number: <span><?php echo htmlspecialchars($fetch_orders['number']); ?></span> </p>
            <p> Email: <span><?php echo htmlspecialchars($fetch_orders['email']); ?></span> </p>
            <p> Address: <span><?php echo htmlspecialchars($fetch_orders['address']); ?></span> </p>
            <p> Total Products: <span><?php echo htmlspecialchars($fetch_orders['total_products']); ?></span> </p>
            <p> Total Price: <span>$<?php echo htmlspecialchars($fetch_orders['total_price']); ?>/-</span> </p>
            <p> Payment Method: <span><?php echo htmlspecialchars($fetch_orders['method']); ?></span> </p>

            <form action="" method="post">
                <input type="hidden" name="order_id" value="<?php echo $fetch_orders['id']; ?>">
                <select name="update_payment">
                    <option value="" selected disabled><?php echo htmlspecialchars($fetch_orders['payment_status']); ?></option>
                    <option value="pending">Pending</option>
                    <option value="completed">Completed</option>
                </select>
                <input type="submit" value="Update" name="update_order" class="option-btn">
                <a href="admin_orders.php?delete=<?php echo $fetch_orders['id']; ?>" onclick="return confirm('Delete this order?');" class="delete-btn">Delete</a>
            </form>
        </div>
        <?php
            }
        } else {
            echo '<p class="empty">No orders placed yet!</p>';
        }
        ?>
    </div>
</section>

<!-- Custom Admin JS -->
<script src="js/admin_script.js"></script>

</body>
</html>
