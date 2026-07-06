<?php
/**
 * Campus Connect - Event Management API
 * Actions: fetch, add, edit, status, delete
 */
header('Content-Type: application/json');
require_once '../config.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'organizer')) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

/**
 * Check if the user has permission to manage the specific event.
 * Admins can manage any event, Organizers only their own.
 */
function checkEventOwnership($eventId, $userId, $userRole, $conn)
{
    if ($userRole === 'admin')
        return true;

    $stmt = $conn->prepare("SELECT organizer_id FROM events WHERE event_id = ?");
    $stmt->bind_param("i", $eventId);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    return $result && $result['organizer_id'] == $userId;
}

$action = $_GET['action'] ?? ($_POST['action'] ?? '');

try {
    if ($action === 'fetch' && isset($_GET['id'])) {
        $event_id = intval($_GET['id']);
        if (!checkEventOwnership($event_id, $_SESSION['user_id'], $_SESSION['role'], $conn)) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized: You do not own this event']);
            exit();
        }
        $stmt = $conn->prepare("SELECT e.*, u.username as organizer_name FROM events e LEFT JOIN users u ON e.organizer_id = u.id WHERE e.event_id = ?");
        $stmt->bind_param("i", $event_id);
        $stmt->execute();
        $event = $stmt->get_result()->fetch_assoc();

        if ($event) {
            echo json_encode(['success' => true, 'event' => $event]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Event not found']);
        }
    } elseif ($action === 'add' || $action === 'edit') {
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $rules = trim($_POST['rules'] ?? '');
        $category = $_POST['category'] ?? 'Other';
        $venue = trim($_POST['venue'] ?? '');
        $start_date = $_POST['start_date'] ?? '';
        $end_date = $_POST['end_date'] ?? '';
        $max_attendees = intval($_POST['max_attendees'] ?? 50);
        $cover_image = trim($_POST['cover_image'] ?? '');

        // Competition logic
        $isTeam = isset($_POST['participation_type']) && $_POST['participation_type'] === 'team';
        $minTeamSize = $isTeam ? intval($_POST['min_team_size'] ?? 1) : 1;
        $maxTeamSize = $isTeam ? intval($_POST['max_team_size'] ?? 1) : 1;

        $prizes = null;
        if ($category === 'Competition' && isset($_POST['prize_titles']) && is_array($_POST['prize_titles'])) {
            $prizesArray = [];
            foreach ($_POST['prize_titles'] as $index => $pTitle) {
                $pTitle = trim($pTitle);
                if (!empty($pTitle)) {
                    $winner = trim($_POST['prize_winners'][$index] ?? '');
                    $prizesArray[] = ['title' => $pTitle, 'winner' => $winner];
                }
            }
            if (!empty($prizesArray))
                $prizes = json_encode($prizesArray);
        }

        if (empty($title) || empty($start_date) || empty($venue)) {
            throw new Exception("Title, Start Date, and Venue are required.");
        }

        if ($action === 'add') {
            // Allow admins to assign organizer, otherwise use current user
            if ($_SESSION['role'] === 'admin' && isset($_POST['organizer_id']) && !empty($_POST['organizer_id'])) {
                $organizer_id = intval($_POST['organizer_id']);
            } else {
                $organizer_id = $_SESSION['user_id'];
            }
            $sql = "INSERT INTO events (title, description, rules, category, start_date, end_date, venue, max_attendees, cover_image, organizer_id, status, prizes, min_team_size, max_team_size, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', ?, ?, ?, NOW())";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssssisissi", $title, $description, $rules, $category, $start_date, $end_date, $venue, $max_attendees, $cover_image, $organizer_id, $prizes, $minTeamSize, $maxTeamSize);
        } else {
            $event_id = intval($_POST['event_id']);
            if (!checkEventOwnership($event_id, $_SESSION['user_id'], $_SESSION['role'], $conn)) {
                echo json_encode(['success' => false, 'message' => 'Unauthorized: You do not own this event']);
                exit();
            }
            // Allow admins to change organizer
            if ($_SESSION['role'] === 'admin' && isset($_POST['organizer_id']) && !empty($_POST['organizer_id'])) {
                $new_organizer_id = intval($_POST['organizer_id']);
                $sql = "UPDATE events SET title = ?, description = ?, rules = ?, category = ?, start_date = ?, end_date = ?, venue = ?, max_attendees = ?, cover_image = ?, organizer_id = ?, prizes = ?, min_team_size = ?, max_team_size = ? WHERE event_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sssssssisissii", $title, $description, $rules, $category, $start_date, $end_date, $venue, $max_attendees, $cover_image, $new_organizer_id, $prizes, $minTeamSize, $maxTeamSize, $event_id);
            } else {
                $sql = "UPDATE events SET title = ?, description = ?, rules = ?, category = ?, start_date = ?, end_date = ?, venue = ?, max_attendees = ?, cover_image = ?, prizes = ?, min_team_size = ?, max_team_size = ? WHERE event_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sssssssisssii", $title, $description, $rules, $category, $start_date, $end_date, $venue, $max_attendees, $cover_image, $prizes, $minTeamSize, $maxTeamSize, $event_id);
            }
        }

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => "Event " . ($action === 'add' ? 'created' : 'updated') . " successfully."]);
        } else {
            throw new Exception("Database error: " . $conn->error);
        }
    } elseif ($action === 'status' && isset($_POST['event_id']) && isset($_POST['status'])) {
        $event_id = intval($_POST['event_id']);
        $new_status = $_POST['status'];
        $upd = $conn->prepare("UPDATE events SET status = ? WHERE event_id = ?");
        $upd->bind_param("si", $new_status, $event_id);
        if ($upd->execute()) {
            echo json_encode(['success' => true, 'message' => "Event status updated to $new_status."]);
        } else {
            throw new Exception("Error updating status.");
        }
    } elseif ($action === 'delete' && isset($_POST['event_id'])) {
        $event_id = intval($_POST['event_id']);
        if (!checkEventOwnership($event_id, $_SESSION['user_id'], $_SESSION['role'], $conn)) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized: You do not own this event']);
            exit();
        }
        $del = $conn->prepare("DELETE FROM events WHERE event_id = ?");
        $del->bind_param("i", $event_id);
        if ($del->execute()) {
            echo json_encode(['success' => true, 'message' => "Event deleted successfully."]);
        } else {
            throw new Exception("Error deleting event.");
        }
    } else {
        throw new Exception("Invalid action.");
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>