<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = strip_tags(trim($_POST["name"]));
    $email = filter_var(trim($_POST["email"]), FILTER_SANITIZE_EMAIL);
    $phone = strip_tags(trim($_POST["phone"]));
    $program = strip_tags(trim($_POST["program"]));
    $message = strip_tags(trim($_POST["message"]));

    // DESTINATION EMAIL
    $recipient = "privacy.chamber@gmail.com";
    $subject = "New Journey Enquiry from $name - HimYog";

    $email_content = "Name: $name\n";
    $email_content .= "Email: $email\n";
    $email_content .= "WhatsApp: $phone\n";
    $email_content .= "Program: $program\n\n";
    $email_content .= "Message:\n$message\n";

    $email_headers = "From: HimYog Website <noreply@himyog.com>\r\n";
    $email_headers .= "Reply-To: $email\r\n";

    if (mail($recipient, $subject, $email_content, $email_headers)) {
        // Send confirmation email to client if a valid email was provided
        if (filter_var($email, FILTER_VALIDATE_EMAIL) && $email !== "no-email-provided@himyog.com") {
            $client_subject = "Enquiry Received - HimYog Yoga Kendra";
            
            $client_message = "
            <html>
            <head>
              <title>Thank you for your enquiry</title>
            </head>
            <body style=\"font-family: Arial, sans-serif; line-height: 1.6; color: #333;\">
              <div style=\"max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e2e8f0; border-radius: 8px;\">
                <h2 style=\"color: #D4AF37; margin-bottom: 20px;\">ॐ Namaste $name,</h2>
                <p>Thank you for reaching out to <strong>HimYog Yoga Kendra</strong> in Dharamsala!</p>
                <p>We have received your enquiry for the <strong>$program</strong> and our team is already reviewing it. We will get back to you with all the details via WhatsApp or Email within 24 hours.</p>
                
                <hr style=\"border: none; border-top: 1px solid #e2e8f0; margin: 20px 0;\" />
                
                <h3 style=\"color: #D4AF37;\">Summary of your enquiry:</h3>
                <ul style=\"list-style: none; padding: 0;\">
                  <li><strong>Program:</strong> $program</li>
                  <li><strong>Name:</strong> $name</li>
                  <li><strong>WhatsApp/Phone:</strong> $phone</li>
                </ul>
                
                <p>If you have any immediate questions, feel free to reply directly to this email or chat with us on WhatsApp at <a href=\"https://wa.me/919418100803\" style=\"color: #D4AF37; text-decoration: none;\">+91 9418100803</a>.</p>
                
                <p style=\"margin-top: 30px;\">Warm regards,<br/><strong>Yogi Shivam & The HimYog Team</strong><br/><a href=\"https://himyog.com\" style=\"color: #D4AF37; text-decoration: none;\">himyog.com</a></p>
              </div>
            </body>
            </html>
            ";
            
            $client_headers = "MIME-Version: 1.0\r\n";
            $client_headers .= "Content-Type: text/html; charset=UTF-8\r\n";
            $client_headers .= "From: HimYog <noreply@himyog.com>\r\n";
            $client_headers .= "Reply-To: privacy.chamber@gmail.com\r\n";
            
            mail($email, $client_subject, $client_message, $client_headers);
        }

        http_response_code(200);
        echo "Thank you! Your message has been sent.";
    } else {
        http_response_code(500);
        echo "Oops! Something went wrong and we couldn't send your message.";
    }
} else {
    http_response_code(403);
    echo "There was a problem with your submission, please try again.";
}
?>
