<?php

return [
    /*
     * Maps the ticket type string (from user input) to the database connection name
     * defined in config/database.php.
     */
    'connection_map' => [
        'Technical Issues' => 'technical_issues_department',
        'Account & Billing' => 'account_billing_department',
        'Product & Service' => 'product_service_department',
        'General Inquiry' => 'general_inquiry_department',
        'Feedback & Suggestions' => 'feedback_suggestions_department',
    ],
];
