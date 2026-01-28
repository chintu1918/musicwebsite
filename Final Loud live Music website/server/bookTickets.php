<?php
// bookTickets.php

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $name = htmlspecialchars($_POST['name']);
    $email = htmlspecialchars($_POST['email']);
    $event = htmlspecialchars($_POST['event']);
    $tickets = intval($_POST['tickets']);

    // Validate data
    if (empty($name) || empty($email) || empty($event) || $tickets <= 0) {
        echo "Invalid input. Please fill out the form correctly.";
        exit;
    }

    // Save data to a file (or database)
    $data = [
        'name' => $name,
        'email' => $email,
        'event' => $event,
        'tickets' => $tickets,
        'date' => date('Y-m-d H:i:s')
    ];

    $file = 'bookings.json';
    $bookings = file_exists($file) ? json_decode(file_get_contents($file), true) : [];
    $bookings[] = $data;

    file_put_contents($file, json_encode($bookings, JSON_PRETTY_PRINT));

    echo "Thank you, $name! Your tickets for the $event event have been booked.";
} else {
    echo "Invalid request method.";
}
?>