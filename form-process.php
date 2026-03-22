<?php
session_start();

if (isset($_POST['email'])) {

    // REPLACE THIS 2 LINES AS YOU DESIRE
    $email_to = "contact@drjoness.uk";
    $email_subject = "You've got a new submission";

    function problem($error)
    {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => $error]);
        die();
    }

    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        problem('Security token validation failed. Please try again.');
    }

    // Validation expected data exists
    if (
        !isset($_POST['fullName']) ||
        !isset($_POST['email']) ||
        !isset($_POST['message'])
    ) {
        problem('Oh looks like there is some problem with your form data.');
    }

    $name = trim($_POST['fullName']); // required
    $email = trim($_POST['email']); // required
    $message = trim($_POST['message']); // required

    $error_message = "";

    // Use PHP's built-in email validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message .= 'Email address does not seem valid. ';
    }

    // Validate name - only letters, spaces, hyphens, and apostrophes
    if (empty($name) || !preg_match("/^[A-Za-z\s'-]+$/", $name)) {
        $error_message .= 'Name does not seem valid. ';
    }

    // Validate message length
    if (strlen($message) < 2) {
        $error_message .= 'Message should not be less than 2 characters. ';
    }

    if (strlen($error_message) > 0) {
        problem(trim($error_message));
    }

    // Build email message - sanitize by escaping, not by blacklisting
    $email_message = "Form details following:\n\n";
    $email_message .= "Name: " . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . "\n";
    $email_message .= "Email: " . htmlspecialchars($email, ENT_QUOTES, 'UTF-8') . "\n";
    $email_message .= "Message: " . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . "\n";

    // Create email headers - do NOT include user input in headers
    $headers = "From: noreply@drjoness.uk\r\n";
    $headers .= "Reply-To: noreply@drjoness.uk\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

    // Send email
    $mail_sent = @mail($email_to, $email_subject, $email_message, $headers);

    if (!$mail_sent) {
        problem('There was an error sending your message. Please try again later.');
    }

    // Success response
    http_response_code(200);
    echo json_encode(['success' => true, 'message' => 'Thanks for contacting us, we will get back to you as soon as possible.']);
}
?>
