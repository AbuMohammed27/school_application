<?php
require_once 'config.php';

class EmailService {
    public static function send($to, $subject, $body) {
        $headers = "From: " . EMAIL_FROM . "\r\n";
        $headers .= "Reply-To: " . EMAIL_REPLY_TO . "\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
        
        return mail($to, $subject, $body, $headers);
    }

    public static function sendApplicationConfirmation($email, $name, $application_id) {
        $subject = "Application Received";
        $body = "Dear $name,\n\n";
        $body .= "Thank you for submitting your application (#$application_id).\n\n";
        $body .= "You can track your application status at: " . SITE_URL . "/status.php?id=$application_id\n\n";
        $body .= "Regards,\nAdmissions Team";
        
        return self::send($email, $subject, $body);
    }

    public static function sendStatusUpdate($email, $name, $application_id, $new_status) {
        $subject = "Application Status Update";
        $body = "Dear $name,\n\n";
        $body .= "The status of your application (#$application_id) has been updated to: " . ucfirst($new_status) . "\n\n";
        $body .= "Login to view details: " . SITE_URL . "/status.php?id=$application_id\n\n";
        $body .= "Regards,\nAdmissions Team";
        
        return self::send($email, $subject, $body);
    }
}
?>