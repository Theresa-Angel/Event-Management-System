<?php
require_once '../../config.php';
require_once '../../includes/functions.php';

// Check if user is logged in and is an organizer
if (!isLoggedIn() || (getUserRole() !== 'organizer' && !isAdmin())) {
    header("Location: ../../login.php");
    exit();
}

$eventId = $_GET['event_id'] ?? 0;
$eventTitle = "Event Scanner";

if ($eventId > 0) {
    $stmt = $conn->prepare("SELECT title FROM events WHERE event_id = ?");
    $stmt->bind_param("i", $eventId);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    if ($res)
        $eventTitle = $res['title'];
}

$pageTitle = "Ticket Scanner";
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?> - Campus Connect</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        @keyframes scan {

            0%,
            100% {
                top: 0;
            }

            50% {
                top: 100%;
            }
        }

        .animate-scan {
            animation: scan 3s linear infinite;
        }

        .animate-fade-in {
            animation: fadeIn 0.3s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes pulse-ring {
            0% {
                transform: scale(0.95);
                box-shadow: 0 0 0 0 rgba(99, 102, 241, 0.7);
            }

            70% {
                transform: scale(1);
                box-shadow: 0 0 0 10px rgba(99, 102, 241, 0);
            }

            100% {
                transform: scale(0.95);
                box-shadow: 0 0 0 0 rgba(99, 102, 241, 0);
            }
        }

        .scanner-pulse {
            animation: pulse-ring 2s infinite;
        }

        /* QR Reader Styling */
        #reader {
            border: none !important;
        }

        #reader__scan_region {
            border-radius: 24px !important;
        }

        #reader__dashboard_section {
            display: none !important;
        }

        /* Success/Error animations */
        @keyframes bounce-in {
            0% {
                transform: scale(0.3);
                opacity: 0;
            }

            50% {
                transform: scale(1.05);
            }

            70% {
                transform: scale(0.9);
            }

            100% {
                transform: scale(1);
                opacity: 1;
            }
        }

        .bounce-in {
            animation: bounce-in 0.5s ease-out;
        }
    </style>
</head>

<body>

    <div class="max-w-md mx-auto space-y-6 pb-20">
        <!-- Header Card -->
        <div class="bg-white rounded-2xl shadow-xl p-6">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <a href="organizer.php?action=participants&event_id=<?php echo $eventId; ?>"
                        class="inline-flex items-center text-sm text-indigo-600 font-semibold hover:text-indigo-700 transition mb-3">
                        <i class="fas fa-arrow-left mr-2"></i> Back to Participants
                    </a>
                    <h2 class="text-2xl font-bold text-slate-800 flex items-center">
                        <i class="fas fa-qrcode text-indigo-600 mr-3"></i>
                        QR Ticket Scanner
                    </h2>
                    <p class="text-sm text-slate-500 mt-1">
                        <?php echo htmlspecialchars($eventTitle); ?>
                    </p>
                </div>
                <div
                    class="h-16 w-16 rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white shadow-lg scanner-pulse">
                    <i class="fas fa-qrcode text-2xl"></i>
                </div>
            </div>
        </div>

        <!-- Scanner Container -->
        <div class="relative bg-black rounded-3xl overflow-hidden shadow-2xl aspect-square border-4 border-white">
            <div id="reader" class="w-full h-full"></div>

            <!-- Scanner Overlay -->
            <div class="absolute inset-0 pointer-events-none flex items-center justify-center">
                <div class="w-64 h-64 border-2 border-indigo-400/50 rounded-2xl relative">
                    <div class="absolute -top-1 -left-1 w-6 h-6 border-t-4 border-l-4 border-indigo-600 rounded-tl-lg">
                    </div>
                    <div class="absolute -top-1 -right-1 w-6 h-6 border-t-4 border-r-4 border-indigo-600 rounded-tr-lg">
                    </div>
                    <div
                        class="absolute -bottom-1 -left-1 w-6 h-6 border-b-4 border-l-4 border-indigo-600 rounded-bl-lg">
                    </div>
                    <div
                        class="absolute -bottom-1 -right-1 w-6 h-6 border-b-4 border-r-4 border-indigo-600 rounded-br-lg">
                    </div>

                    <!-- Scanning Animation Line -->
                    <div
                        class="absolute top-0 left-0 w-full h-1 bg-indigo-500/50 shadow-[0_0_15px_rgba(99,102,241,0.5)] animate-scan">
                    </div>
                </div>
            </div>
        </div>

        <!-- Status & Results -->
        <div id="status-card"
            class="bg-white p-6 rounded-2xl shadow-xl border border-slate-200 text-center animate-fade-in hidden bounce-in">
            <div id="status-icon" class="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4 text-2xl">
                <!-- Icon -->
            </div>
            <h3 id="status-title" class="text-lg font-bold text-slate-800"></h3>
            <p id="status-message" class="text-sm text-slate-500 mt-1"></p>

            <div id="student-info" class="mt-4 p-3 bg-slate-50 rounded-xl border border-slate-100 hidden">
                <p class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Student Verified</p>
                <p id="student-name" class="font-bold text-slate-800 text-lg"></p>
            </div>

            <button onclick="resetScanner()"
                class="w-full mt-6 py-3 bg-indigo-600 text-white rounded-xl font-bold hover:bg-indigo-700 transition shadow-lg">
                <i class="fas fa-redo mr-2"></i> Scan Next
            </button>
        </div>

        <div id="idle-msg"
            class="bg-white/90 backdrop-blur-sm text-center p-8 rounded-2xl border-2 border-dashed border-indigo-300 shadow-lg">
            <i class="fas fa-camera text-4xl text-indigo-400 mb-3"></i>
            <p class="text-sm text-slate-700 font-medium">Position the ticket QR code within the frame above to check in
                the student.</p>
            <p class="text-xs text-slate-500 mt-2">Make sure the QR code is clearly visible and well-lit</p>
        </div>
    </div>

    <!-- html5-qrcode library -->
    <script src="https://unpkg.com/html5-qrcode"></script>

    <script>
        const html5QrCode = new Html5Qrcode("reader");
        let isScanning = true;

        const config = { fps: 10, qrbox: { width: 250, height: 250 } };

        function startScanner() {
            html5QrCode.start(
                { facingMode: "environment" },
                config,
                onScanSuccess
            ).catch(err => {
                console.error(err);
                alert("Could not access camera. Please ensure you have given permission.");
            });
        }

        function onScanSuccess(decodedText) {
            if (!isScanning) return;
            isScanning = false;

            // Haptic feedback if available
            if (window.navigator.vibrate) window.navigator.vibrate(100);

            // Sound feedback
            const audio = new Audio('https://assets.mixkit.co/active_storage/sfx/2571/2571-preview.mp3');
            audio.play().catch(() => { });

            processCheckIn(decodedText);
        }

        function processCheckIn(ticketId) {
            const statusCard = document.getElementById('status-card');
            const idleMsg = document.getElementById('idle-msg');
            const icon = document.getElementById('status-icon');
            const title = document.getElementById('status-title');
            const msg = document.getElementById('status-message');
            const studentInfo = document.getElementById('student-info');
            const studentName = document.getElementById('student-name');

            fetch(`../../api/checkin.php?ticket_id=${ticketId}`)
                .then(res => res.json())
                .then(data => {
                    idleMsg.classList.add('hidden');
                    statusCard.classList.remove('hidden');

                    if (data.success) {
                        icon.className = "w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4 text-2xl bg-green-100 text-green-600 bounce-in";
                        icon.innerHTML = '<i class="fas fa-check"></i>';
                        title.textContent = "Check-in Successful!";
                        msg.textContent = data.event;
                        studentInfo.classList.remove('hidden');
                        studentName.textContent = data.student;
                    } else {
                        icon.className = "w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4 text-2xl bg-red-100 text-red-600 bounce-in";
                        icon.innerHTML = '<i class="fas fa-times"></i>';
                        title.textContent = "Check-in Failed";
                        msg.textContent = data.message;
                        if (data.student) {
                            studentInfo.classList.remove('hidden');
                            studentName.textContent = data.student;
                        } else {
                            studentInfo.classList.add('hidden');
                        }
                    }
                })
                .catch(err => {
                    alert("Error connecting to server.");
                    resetScanner();
                });
        }

        function resetScanner() {
            document.getElementById('status-card').classList.add('hidden');
            document.getElementById('idle-msg').classList.remove('hidden');
            isScanning = true;
        }

        // Start on load
        document.addEventListener('DOMContentLoaded', startScanner);
    </script>

</body>

</html>