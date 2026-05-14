<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = strip_tags(trim($_POST["name"]));
    $email = filter_var(trim($_POST["email"]), FILTER_SANITIZE_EMAIL);
    $phone = strip_tags(trim($_POST["phone"]));
    $program = strip_tags(trim($_POST["program"]));
    $message = strip_tags(trim($_POST["message"]));

    // DESTINATION EMAIL
    $recipient = "shivam@shivamneelkantyoga.com";
    $subject = "New Journey Enquiry from $name - HimYog";

    $email_content = "Name: $name\n";
    $email_content .= "Email: $email\n";
    $email_content .= "WhatsApp: $phone\n";
    $email_content .= "Program: $program\n\n";
    $email_content .= "Message:\n$message\n";

    $email_headers = "From: HimYog Website <noreply@himyog.com>\r\n";
    $email_headers .= "Reply-To: $email\r\n";

    if (mail($recipient, $subject, $email_content, $email_headers)) {
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
