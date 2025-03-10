<?php

include 'config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'PHPMailer/src/Exception.php';

session_start();

$user_id = $_SESSION['user_id'];

if(!isset($user_id)){
   header('location:login.php');
   exit();
}

if(isset($_POST['send'])){

   $name = mysqli_real_escape_string($conn, $_POST['name']);
   $email = mysqli_real_escape_string($conn, $_POST['email']);
   $number = $_POST['number'];
   $msg = mysqli_real_escape_string($conn, $_POST['message']);

   $select_message = mysqli_query($conn, "SELECT * FROM `message` WHERE name = '$name' AND email = '$email' AND number = '$number' AND message = '$msg'") or die('Query failed');

   if(mysqli_num_rows($select_message) > 0){
      $message[] = 'Message already sent!';
   }else{
      mysqli_query($conn, "INSERT INTO `message`(user_id, name, email, number, message) VALUES('$user_id', '$name', '$email', '$number', '$msg')") or die('Query failed');

      // Send Email Using PHPMailer
      $mail = new PHPMailer(true);

      try {
         $mail->isSMTP();
         $mail->Host = 'smtp.gmail.com';
         $mail->SMTPAuth = true;
         $mail->Username = 'shivrajguduri98@gmail.com'; // Your email
         $mail->Password = 'ufcw tdxe oqbw eudy'; // Your app password (Not recommended to expose directly)
         $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
         $mail->Port = 587;

         $mail->setFrom('shivrajguduri98@gmail.com', 'Your Website');
         $mail->addAddress($email, $name); // Send email to user

         // Email Content
         $mail->isHTML(true);
         $mail->Subject = 'Thank you for contacting us!';
         $mail->Body = "<h3>Hello $name,</h3><p>Thank you for reaching out! We have received your message:</p><p><strong>Message:</strong> $msg</p><p>We will get back to you soon.</p><br><p>Best Regards,<br>Bookly Team</p>";

         $mail->send();
         $message[] = 'Message sent successfully!';
      } catch (Exception $e) {
         $message[] = "Email could not be sent. Error: {$mail->ErrorInfo}";
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
   <title>Contact</title>

   <!-- Font Awesome CDN Link -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

   <!-- Custom CSS File Link -->
   <link rel="stylesheet" href="css/style.css">
</head>
<body>
   
<?php include 'header.php'; ?>

<div class="heading">
   <h3>Contact Us</h3>
   <p> <a href="home.php">Home</a> / Contact </p>
</div>

<section class="contact">

   <form action="" method="post">
      <h3>Say Something!</h3>
      <input type="text" name="name" required placeholder="Enter your name" class="box">
      <input type="email" name="email" required placeholder="Enter your email" class="box">
      <input type="number" name="number" required placeholder="Enter your number" class="box">
      <textarea name="message" class="box" placeholder="Enter your message" cols="30" rows="10"></textarea>
      <input type="submit" value="Send Message" name="send" class="btn">
   </form>

   <?php
   if(isset($message)){
      foreach($message as $msg){
         echo '<p class="message">'.$msg.'</p>';
      }
   }
   ?>

</section>

<?php include 'footer.php'; ?>

<!-- Custom JS File Link -->
<script src="js/script.js"></script>

</body>
</html>
