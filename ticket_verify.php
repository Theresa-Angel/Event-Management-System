<?php
require_once 'config.php';
require_once 'includes/functions.php';

// Debug: Show what we received
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

// Get registration ID from QR code
$reg_id = isset($_GET['reg_id']) ? intval($_GET['reg_id']) : 0;
$ticket_id = isset($_GET['ticket']) ? trim($_GET['ticket']) : '';

$error = null;
$ticket = null;

// Debug output (comment out in production)
// echo "<!-- Debug: reg_id=$reg_id, ticket_id=$ticket_id -->";

if (!$reg_id && !$ticket_id) {
    $error = "No ticket information provided.";
} else {
    // Fetch registration details
    if ($reg_id) {
        $sql = "SELECT r.id, r.ticket_id, r.status, r.registration_date, 
                e.title as event_name, e.start_date, e.venue, e.cover_image, 
                u.username as student_name, u.email, u.department
                FROM registrations r
                JOIN events e ON r.event_id = e.event_id
                JOIN users u ON r.user_id = u.id
                WHERE r.id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $reg_id);
    } else {
        $sql = "SELECT r.id, r.ticket_id, r.status, r.registration_date,
                e.title as event_name, e.start_date, e.venue, e.cover_image,
                u.username as student_name, u.email, u.department
                FROM registrations r
                JOIN events e ON r.event_id = e.event_id
                JOIN users u ON r.user_id = u.id
                WHERE r.ticket_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $ticket_id);
    }

    if ($stmt) {
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            $error = "Ticket not found. Please check if the ticket ID is correct.";
        } else {
            $ticket = $result->fetch_assoc();
        }
        $stmt->close();
    } else {
        $error = "Database error: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket Verification - Campus Connect</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .ticket-card {
            animation: slideUp 0.5s ease-out;
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .status-badge {
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: 0.7;
            }
        }
    </style>
</head>
<body>
    <?php if ($error): ?>
        <div class="ticket-card bg-white rounded-2xl shadow-2xl p-8 max-w-md w-full text-center">
            <div class="w-20 h-20 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-times text-4xl text-red-600"></i>
            </div>
            <h1 class="text-2xl font-bold text-gray-800 mb-2">Invalid Ticket</h1>
            <p class="text-gray-600 mb-6"><?php echo htmlspecialchars($error); ?></p>
            <a href="index.php" class="inline-block px-6 py-3 bg-indigo-600 text-white rounded-lg font-semibold hover:bg-indigo-700 transition">
                Go to Home
            </a>
        </div>
    <?php elseif ($ticket): ?>
        <div class="ticket-card bg-white rounded-2xl shadow-2xl overflow-hidden max-w-md w-full">
            <!-- Header -->
            <div class="bg-gradient-to-r from-indigo-600 to-purple-600 p-6 text-white text-center relative">
                <div class="absolute top-4 right-4">
                    <?php
                    $statusColor = 'bg-green-500';
                    $statusIcon = 'fa-check-circle';
                    $statusText = 'Confirmed';
                    
                    if ($ticket['status'] === 'waitlisted') {
                        $statusColor = 'bg-yellow-500';
                        $statusIcon = 'fa-clock';
                        $statusText = 'Waitlisted';
                    } elseif ($ticket['status'] === 'cancelled') {
                        $statusColor = 'bg-red-500';
                        $statusIcon = 'fa-times-circle';
                        $statusText = 'Cancelled';
                    }
                    ?>
                    <span class="status-badge <?php echo $statusColor; ?> text-white text-xs font-bold px-3 py-1 rounded-full flex items-center gap-1">
                        <i class="fas <?php echo $statusIcon; ?>"></i>
                        <?php echo $statusText; ?>
                    </span>
                </div>
                
                <div class="w-24 h-24 bg-white rounded-full flex items-center justify-center mx-auto mb-4 shadow-lg">
                    <i class="fas fa-ticket-alt text-5xl text-indigo-600"></i>
                </div>
                <h1 class="text-2xl font-bold">Valid Ticket</h1>
                <p class="text-indigo-100 text-sm mt-1">Campus Connect Event Pass</p>
            </div>
            
            <!-- Ticket Details -->
            <div class="p-6 space-y-4">
                <!-- Event Name -->
                <div class="bg-gradient-to-r from-indigo-50 to-purple-50 p-4 rounded-xl border-l-4 border-indigo-600">
                    <p class="text-xs text-gray-500 uppercase font-semibold mb-1">Event Name</p>
                    <h2 class="text-xl font-bold text-gray-800"><?php echo htmlspecialchars($ticket['event_name']); ?></h2>
                </div>
                
                <!-- Student Name -->
                <div class="flex items-center gap-4 p-4 bg-gray-50 rounded-xl">
                    <div class="w-12 h-12 bg-indigo-100 rounded-full flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-user text-xl text-indigo-600"></i>
                    </div>
                    <div class="flex-1">
                        <p class="text-xs text-gray-500 uppercase font-semibold">Student Name</p>
                        <p class="text-lg font-bold text-gray-800"><?php echo htmlspecialchars($ticket['student_name']); ?></p>
                        <?php if ($ticket['department']): ?>
                            <p class="text-sm text-gray-600"><?php echo htmlspecialchars($ticket['department']); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Ticket ID -->
                <div class="flex items-center gap-4 p-4 bg-gray-50 rounded-xl">
                    <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-barcode text-xl text-purple-600"></i>
                    </div>
                    <div class="flex-1">
                        <p class="text-xs text-gray-500 uppercase font-semibold">Ticket ID</p>
                        <p class="text-lg font-bold text-gray-800 font-mono"><?php echo htmlspecialchars($ticket['ticket_id']); ?></p>
                    </div>
                </div>
                
                <!-- Event Details -->
                <div class="grid grid-cols-2 gap-3 pt-4 border-t border-gray-200">
                    <div class="text-center p-3 bg-gray-50 rounded-lg">
                        <i class="fas fa-calendar text-indigo-600 mb-2"></i>
                        <p class="text-xs text-gray-500 uppercase font-semibold">Date</p>
                        <p class="text-sm font-bold text-gray-800"><?php echo date('M d, Y', strtotime($ticket['start_date'])); ?></p>
                    </div>
                    <div class="text-center p-3 bg-gray-50 rounded-lg">
                        <i class="fas fa-clock text-indigo-600 mb-2"></i>
                        <p class="text-xs text-gray-500 uppercase font-semibold">Time</p>
                        <p class="text-sm font-bold text-gray-800"><?php echo date('g:i A', strtotime($ticket['start_date'])); ?></p>
                    </div>
                </div>
                
                <div class="text-center p-3 bg-gray-50 rounded-lg">
                    <i class="fas fa-map-marker-alt text-indigo-600 mb-2"></i>
                    <p class="text-xs text-gray-500 uppercase font-semibold">Venue</p>
                    <p class="text-sm font-bold text-gray-800"><?php echo htmlspecialchars($ticket['venue']); ?></p>
                </div>
                
                <!-- Registration Date -->
                <div class="text-center pt-4 border-t border-gray-200">
                    <p class="text-xs text-gray-500">Registered on <?php echo date('M d, Y g:i A', strtotime($ticket['registration_date'])); ?></p>
                </div>
            </div>
            
            <!-- Footer -->
            <div class="bg-gray-50 p-4 text-center border-t border-gray-200">
                <p class="text-xs text-gray-600">
                    <i class="fas fa-shield-alt text-indigo-600"></i>
                    Verified by Campus Connect
                </p>
            </div>
        </div>
    <?php else: ?>
        <div class="ticket-card bg-white rounded-2xl shadow-2xl p-8 max-w-md w-full text-center">
            <div class="w-20 h-20 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-exclamation-triangle text-4xl text-yellow-600"></i>
            </div>
            <h1 class="text-2xl font-bold text-gray-800 mb-2">No Data</h1>
            <p class="text-gray-600 mb-6">Unable to load ticket information.</p>
            <a href="index.php" class="inline-block px-6 py-3 bg-indigo-600 text-white rounded-lg font-semibold hover:bg-indigo-700 transition">
                Go to Home
            </a>
        </div>
    <?php endif; ?>
</body>
</html>
