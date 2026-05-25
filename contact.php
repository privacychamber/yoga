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
        // Save to local CSV backup file (securely protected)
        $log_file = "enquiries_backup_9418.csv";
        $file_exists = file_exists($log_file);
        $fp = fopen($log_file, 'a');
        if ($fp) {
            if (!$file_exists) {
                fputcsv($fp, ['Date', 'Name', 'Email', 'Phone', 'Program', 'Message']);
            }
            fputcsv($fp, [date('Y-m-d H:i:s'), $name, $email, $phone, $program, $message]);
            fclose($fp);
        }

        // Send confirmation email to client if a valid email was provided
        if (filter_var($email, FILTER_VALIDATE_EMAIL) && $email !== "no-email-provided@himyog.com") {
            $client_subject = "Enquiry Received - HimYog Yoga Kendra";
            
            $client_content = "ॐ Namaste $name,\n\n";
            $client_content .= "Thank you for reaching out to HimYog Yoga Kendra in Dharamsala!\n\n";
            $client_content .= "We have received your enquiry for the $program and our team is already reviewing it. We will get back to you with all the details via WhatsApp or Email within 24 hours.\n\n";
            $client_content .= "Summary of your enquiry:\n";
            $client_content .= "------------------------\n";
            $client_content .= "Program: $program\n";
            $client_content .= "Name: $name\n";
            $client_content .= "WhatsApp/Phone: $phone\n\n";
            $client_content .= "If you have any immediate questions, feel free to reply directly to this email or chat with us on WhatsApp at +91 9418100803 (https://wa.me/919418100803).\n\n";
            $client_content .= "Warm regards,\n";
            $client_content .= "Yogi Shivam & The HimYog Team\n";
            $client_content .= "https://himyog.com\n";
            
            $client_headers = "From: HimYog Website <noreply@himyog.com>\r\n";
            $client_headers .= "Reply-To: privacy.chamber@gmail.com\r\n";
            
            mail($email, $client_subject, $client_content, $client_headers);
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
