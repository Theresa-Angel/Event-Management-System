<?php
require_once 'config.php';
require_once 'includes/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    setFlashMessage("Please login to register for events.", "warning");
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$event_id = isset($_GET['event_id']) ? intval($_GET['event_id']) : 0;

if ($event_id <= 0) {
    setFlashMessage("Invalid event selected.", "danger");
    header("Location: events.php");
    exit();
}

try {
    // 1. Fetch event details and current registration count
    $stmt = $conn->prepare("
        SELECT e.*, 
        (SELECT COUNT(*) FROM registrations WHERE event_id = e.event_id AND status = 'confirmed') as registered_count
        FROM events e 
        WHERE e.event_id = ? AND e.status = 'active'
    ");
    $stmt->bind_param("i", $event_id);
    $stmt->execute();
    $event = $stmt->get_result()->fetch_assoc();

    if (!$event) {
        throw new Exception("Event not found or is no longer active.");
    }

    // 2. Check if user is already registered
    $check_stmt = $conn->prepare("SELECT status FROM registrations WHERE user_id = ? AND event_id = ?");
    $check_stmt->bind_param("ii", $user_id, $event_id);
    $check_stmt->execute();
    $existing = $check_stmt->get_result()->fetch_assoc();

    if ($existing) {
        if ($existing['status'] === 'cancelled') {
            // Allow re-registration if cancelled
        } else {
            setFlashMessage("You are already registered for this event (Status: " . ucfirst($existing['status']) . ").", "info");
            // Redirect based on user role
            if ($_SESSION['role'] === 'student') {
                header("Location: user/student/my_registrations.php");
            } else {
                header("Location: events.php");
            }
            exit();
        }
    }

    // 3. Determine Status (Confirmed or Waitlisted)
    $status = 'confirmed';
    if ($event['max_attendees'] > 0 && $event['registered_count'] >= $event['max_attendees']) {
        $status = 'waitlisted';
    }

    // 4. Generate Ticket ID and Placeholder QR Code
    $ticket_id = strtoupper(substr(md5(uniqid($user_id . $event_id, true)), 0, 10));
    $qr_code = "TICKET-" . $ticket_id; // Simple string that would be encoded in a QR

    // 5. Insert Registration
    $registration_id = null;
    if ($existing && $existing['status'] === 'cancelled') {
        $stmt = $conn->prepare("UPDATE registrations SET status = ?, ticket_id = ?, qr_code = ?, registration_date = NOW() WHERE user_id = ? AND event_id = ?");
        $stmt->bind_param("sssii", $status, $ticket_id, $qr_code, $user_id, $event_id);
        $stmt->execute();
        
        // Get registration ID
        $stmt = $conn->prepare("SELECT id FROM registrations WHERE user_id = ? AND event_id = ?");
        $stmt->bind_param("ii", $user_id, $event_id);
        $stmt->execute();
        $registration_id = $stmt->get_result()->fetch_assoc()['id'];
    } else {
        $stmt = $conn->prepare("INSERT INTO registrations (user_id, event_id, status, ticket_id, qr_code, registration_date) VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("iisss", $user_id, $event_id, $status, $ticket_id, $qr_code);
        $stmt->execute();
        $registration_id = $conn->insert_id;
    }

    if ($registration_id) {
        $msg = ($status === 'confirmed') ? "Registration successful! Your ticket ID is $ticket_id." : "Waitlisted! You've been added to the waitlist. We'll notify you if a slot opens up.";
        setFlashMessage($msg, "success");

        // 6. Create Notification for Student
        $notif_msg = ($status === 'confirmed') ? "You have successfully registered for " . $event['title'] : "You have been added to the waitlist for " . $event['title'];
        createNotification($user_id, "Registration Update", $notif_msg, "success", $event_id);

        // 7. Notify Organizer about new registration
        $student_name = $_SESSION['username'] ?? 'A student';
        $org_msg = "$student_name has registered for your event: " . $event['title'];
        if ($status === 'waitlisted') {
            $org_msg = "$student_name has been added to the waitlist for: " . $event['title'];
        }
        notifyOrganizer($event_id, "New Registration", $org_msg, "event");

        // Redirect based on user role
        if ($_SESSION['role'] === 'student') {
            header("Location: user/student/my_registrations.php");
        } else {
            header("Location: events.php");
        }
        exit();
    } else {
        throw new Exception("Error during registration: " . $conn->error);
    }

} catch (Exception $e) {
    setFlashMessage($e->getMessage(), "danger");
    header("Location: events.php");
    exit();
}
?>