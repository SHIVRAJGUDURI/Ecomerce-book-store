<?php

include 'config.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'PHPMailer/src/Exception.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if (!isset($admin_id)) {
   header('location:login.php');
   exit();
}

// Deleting a message securely
if (isset($_GET['delete'])) {
   $delete_id = $_GET['delete'];

   // Use a prepared statement to prevent SQL injection
   $stmt = $conn->prepare("DELETE FROM messages WHERE id = ?");
   $stmt->bind_param("i", $delete_id);

   if ($stmt->execute()) {
      header('location:admin_contacts.php');
      exit();
   } else {
      die('Query failed: ' . $stmt->error);
   }
   $stmt->close();
}

// Sending email notification to admin
if (isset($_POST['send_email'])) {
   $email = $_POST['email'];
   $name = $_POST['name'];
   $msg = $_POST['message'];

   $mail = new PHPMailer(true);

   try {
      $mail->isSMTP();
      $mail->Host = 'smtp.gmail.com';
      $mail->SMTPAuth = true;
      $mail->Username = 'your-email@gmail.com'; // Replace with your email
      $mail->Password = 'your-app-password'; // Replace with your app password
      $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
      $mail->Port = 587;

      $mail->setFrom('your-email@gmail.com', 'Admin Support');
      $mail->addAddress($email, $name); // Send response email to user

      // Email Content
      $mail->isHTML(true);
      $mail->Subject = 'Admin Response to Your Message';
      $mail->Body = "<h3>Hello $name,</h3><p>Thank you for reaching out. Our team has received your message:</p><p><strong>Message:</strong> $msg</p><p>We will get back to you soon.</p><br><p>Best Regards,<br>Admin Team</p>";

      $mail->send();
      $message[] = 'Email sent successfully!';
   } catch (Exception $e) {
      $message[] = "Email could not be sent. Error: {$mail->ErrorInfo}";
   }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Admin Messages</title>

   <!-- Font Awesome CDN link -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

   <!-- Custom admin CSS file link -->
   <link rel="stylesheet" href="css/admin_style.css">

</head>
<body>
   
<?php include 'admin_header.php'; ?>

<section class="messages">

   <h1 class="title">Admin Messages</h1>

   <div class="box-container">
   <?php
      // Ensure table name is correct in the query
      $select_message = mysqli_query($conn, "SELECT * FROM messages") or die('Query failed: ' . mysqli_error($conn));
      
      if (mysqli_num_rows($select_message) > 0) {
         while ($fetch_message = mysqli_fetch_assoc($select_message)) {
   ?>
   <div class="box">
      <p> User ID: <span><?php echo htmlspecialchars($fetch_message['user_id']); ?></span> </p>
      <p> Name: <span><?php echo htmlspecialchars($fetch_message['name']); ?></span> </p>
      <p> Number: <span><?php echo htmlspecialchars($fetch_message['number']); ?></span> </p>
      <p> Email: <span><?php echo htmlspecialchars($fetch_message['email']); ?></span> </p>
      <p> Message: <span><?php echo htmlspecialchars($fetch_message['message']); ?></span> </p>
      
      <a href="admin_contacts.php?delete=<?php echo $fetch_message['id']; ?>" onclick="return confirm('Delete this message?');" class="delete-btn">Delete Message</a>
      
      <!-- Form to send email to the user -->
      <form action="" method="post">
         <input type="hidden" name="email" value="<?php echo htmlspecialchars($fetch_message['email']); ?>">
         <input type="hidden" name="name" value="<?php echo htmlspecialchars($fetch_message['name']); ?>">
         <input type="hidden" name="message" value="<?php echo htmlspecialchars($fetch_message['message']); ?>">
         <input type="submit" name="send_email" value="Send Email Response" class="btn">
      </form>
   </div>
   <?php
         }
      } else {
         echo '<p class="empty">No messages found!</p>';
      }
   ?>
   </div>

</section>

<!-- Custom admin JS file link -->
<script src="js/admin_script.js"></script>

</body>
</html>
