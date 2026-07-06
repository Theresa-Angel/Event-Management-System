<?php
// Handle form submission
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_event'])) {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $rules = trim($_POST['rules']);
    $category = trim($_POST['category']);
    $startDate = $_POST['start_date'];
    $endDate = $_POST['end_date'];
    $venue = trim($_POST['venue']);
    $maxAttendees = intval($_POST['max_attendees']);
    $organizerId = trim($_POST['organizer_id']);

    // Handle cover image: file upload takes priority over URL
    $coverImage = trim($_POST['cover_image'] ?? '');
    if (!empty($_FILES['cover_image_file']['name'])) {
        $uploadDir = '../assets/event_images/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
        $ext = strtolower(pathinfo($_FILES['cover_image_file']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','webp','gif'];
        if (in_array($ext, $allowed) && $_FILES['cover_image_file']['size'] <= 5 * 1024 * 1024) {
            $filename = 'event_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            if (move_uploaded_file($_FILES['cover_image_file']['tmp_name'], $uploadDir . $filename)) {
                $coverImage = 'assets/event_images/' . $filename;
            }
        } else {
            $error = "Invalid image file. Use JPG, PNG, or WEBP under 5MB.";
        }
    }

    // Team details
    $isTeam = isset($_POST['participation_type']) && $_POST['participation_type'] === 'team';
    $minTeamSize = $isTeam ? intval($_POST['min_team_size'] ?? 1) : 1;
    $maxTeamSize = $isTeam ? intval($_POST['max_team_size'] ?? 1) : 1;


    // Process prizes if it's a competition
    $prizes = null;
    if ($category === 'Competition' && isset($_POST['prize_titles']) && is_array($_POST['prize_titles'])) {
        $prizesArray = [];
        foreach ($_POST['prize_titles'] as $index => $title) {
            $title = trim($title);
            if (!empty($title)) {
                $winner = trim($_POST['prize_winners'][$index] ?? '');
                $prizesArray[] = [
                    'title' => $title,
                    'winner' => $winner
                ];
            }
        }
        if (!empty($prizesArray)) {
            $prizes = json_encode($prizesArray);
        }
    }

    // Basic validation
    if (empty($title) || empty($startDate) || empty($venue) || empty($organizerId)) {
        $error = "Please fill in all required fields.";
    } else {
        // Insert into database
        $stmt = $conn->prepare("INSERT INTO events (title, description, rules, category, start_date, end_date, venue, max_attendees, cover_image, organizer_id, status, prizes, min_team_size, max_team_size) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', ?, ?, ?)");
        $stmt->bind_param("sssssssisissi", $title, $description, $rules, $category, $startDate, $endDate, $venue, $maxAttendees, $coverImage, $organizerId, $prizes, $minTeamSize, $maxTeamSize);

        if ($stmt->execute()) {
            $success = "Event created successfully!";
            // Redirect or clear form? Let's show success message.
            echo "<script>setTimeout(() => window.location.href = '?action=my-events', 1500);</script>";
        } else {
            $error = "Error creating event: " . $stmt->error;
        }
    }
}
?>

<div class="space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">Create New Event</h2>
            <p class="text-slate-500">Host a new event for the community</p>
        </div>
        <div class="flex gap-3">
            <a href="?page=dashboard" class="text-slate-500 hover:text-slate-700 font-medium">
                <i class="fas fa-home mr-1"></i> Dashboard
            </a>
            <a href="?page=manage_events" class="text-slate-500 hover:text-slate-700 font-medium">
                <i class="fas fa-arrow-left mr-1"></i> Back to Events
            </a>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <?php if ($error): ?>
            <div class="p-4 bg-red-50 text-red-700 border-b border-red-100 flex items-center">
                <i class="fas fa-exclamation-circle mr-2"></i>
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="p-4 bg-green-50 text-green-700 border-b border-green-100 flex items-center">
                <i class="fas fa-check-circle mr-2"></i>
                <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" class="p-6 sm:p-8 space-y-6" id="createEventForm">
            <input type="hidden" name="create_event" value="1">

            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-slate-700">Event Title <span
                            class="text-red-500">*</span></label>
                    <input type="text" name="title" required
                        class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border"
                        placeholder="e.g. Annual Tech Symposium">
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-slate-700">Event Organizer <span
                            class="text-red-500">*</span></label>
                    <select name="organizer_id" required
                        class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border">
                        <option value="">Select Organizer</option>
                        <?php
                        $organizers = $conn->query("SELECT id, username, email FROM users WHERE role = 'organizer' AND status = 'active' ORDER BY username LIMIT 100");
                        while ($org = $organizers->fetch_assoc()) {
                            $selected = ($org['id'] == $_SESSION['user_id']) ? 'selected' : '';
                            echo "<option value='{$org['id']}' {$selected}>{$org['username']} ({$org['email']})</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-slate-700">Description</label>
                    <textarea name="description" rows="4"
                        class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border"
                        placeholder="Describe your event..."></textarea>
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-slate-700">Rules & Regulations</label>
                    <textarea name="rules" rows="4"
                        class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border"
                        placeholder="List any rules, guidelines, or requirements for participants..."></textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">Category</label>
                    <select name="category" id="eventCategory"
                        class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border">
                        <option value="Workshop">Workshop</option>
                        <option value="Seminar">Seminar</option>
                        <option value="Competition">Competition</option>
                        <option value="Cultural">Cultural</option>
                        <option value="Sports">Sports</option>
                        <option value="Arts">Arts</option>
                        <option value="Symposium">Symposium</option>
                        <option value="Hackathon">Hackathon</option>
                        <option value="Webinar">Webinar</option>
                        <option value="Other">Other</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">Venue / Location <span
                            class="text-red-500">*</span></label>
                    <input type="text" name="venue" required
                        class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border"
                        placeholder="e.g. Auditorium A">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">Start Date & Time <span
                            class="text-red-500">*</span></label>
                    <input type="datetime-local" name="start_date" required
                        class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">End Date & Time</label>
                    <input type="datetime-local" name="end_date"
                        class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">Max Capacity</label>
                    <input type="number" name="max_attendees" min="1" value="50"
                        class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border">
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-slate-700">Cover Image</label>
                    <div class="mt-1 space-y-3">
                        <div class="flex rounded-lg border border-slate-200 overflow-hidden w-fit text-xs font-semibold">
                            <button type="button" id="tabUpload" onclick="switchImageTab('upload')"
                                class="px-4 py-2 bg-indigo-600 text-white transition">Upload File</button>
                            <button type="button" id="tabUrl" onclick="switchImageTab('url')"
                                class="px-4 py-2 bg-white text-slate-600 hover:bg-slate-50 transition">Image URL</button>
                        </div>
                        <div id="panelUpload">
                            <label id="dropZone"
                                class="flex flex-col items-center justify-center w-full h-36 border-2 border-dashed border-slate-300 rounded-lg cursor-pointer hover:border-indigo-400 hover:bg-indigo-50 transition-all relative overflow-hidden">
                                <div id="dropZoneContent" class="flex flex-col items-center gap-2 text-slate-400">
                                    <i class="fas fa-cloud-upload-alt text-3xl"></i>
                                    <span class="text-sm font-medium">Click or drag & drop an image</span>
                                    <span class="text-xs">PNG, JPG, WEBP up to 5MB</span>
                                </div>
                                <img id="imagePreview" src="" alt="Preview"
                                    class="absolute inset-0 w-full h-full object-cover hidden rounded-lg">
                                <input type="file" id="coverImageFile" name="cover_image_file"
                                    accept="image/*" class="hidden" onchange="handleImageFile(this)">
                            </label>
                            <button type="button" id="clearImageBtn" onclick="clearImagePreview()"
                                class="hidden mt-2 text-xs text-red-500 hover:text-red-700 font-medium">
                                <i class="fas fa-times mr-1"></i>Remove image
                            </button>
                        </div>
                        <div id="panelUrl" class="hidden">
                            <input type="url" name="cover_image" id="coverImageUrl"
                                class="block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border"
                                placeholder="https://example.com/image.jpg"
                                oninput="previewUrlImage(this.value)">
                            <div id="urlPreviewWrap" class="hidden mt-2">
                                <img id="urlPreview" src="" alt="URL Preview"
                                    class="h-28 w-full object-cover rounded-lg border border-slate-200">
                            </div>
                        </div>
                    </div>
                    <p class="mt-1 text-xs text-slate-400">This image will appear as the event banner.</p>
                </div>

                <!-- Prize List for Competitions -->
                <div id="prizeSection" class="sm:col-span-2 hidden bg-slate-50 p-6 rounded-lg border border-slate-200">
                    <div class="flex flex-col gap-6">
                        <!-- Participation Type -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-bold text-slate-700">Participation Type</label>
                                <select name="participation_type" id="participationType"
                                    class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border">
                                    <option value="individual">Individual</option>
                                    <option value="team">Team</option>
                                </select>
                            </div>

                            <div id="teamSizeSection" class="hidden grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-bold text-slate-700">Min per Team</label>
                                    <input type="number" name="min_team_size" min="1" value="1"
                                        class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border">
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-slate-700">Max per Team</label>
                                    <input type="number" name="max_team_size" min="1" value="4"
                                        class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border">
                                </div>
                            </div>
                        </div>

                        <!-- Prizes -->
                        <div>
                            <div class="flex justify-between items-center mb-4">
                                <label class="block text-sm font-bold text-slate-700">Prize List</label>
                                <button type="button" id="addPrizeBtn"
                                    class="text-xs bg-indigo-100 text-indigo-700 px-3 py-1 rounded hover:bg-indigo-200 transition">
                                    <i class="fas fa-plus mr-1"></i> Add Prize
                                </button>
                            </div>
                            <div id="prizeList" class="space-y-3">
                                <div
                                    class="grid grid-cols-1 sm:grid-cols-2 gap-3 p-3 bg-white rounded border border-slate-200 relative group">
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Prize
                                            Title</label>
                                        <input type="text" name="prize_titles[]" placeholder="e.g. 1st Prize: $500"
                                            class="w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Winner
                                            (Optional)</label>
                                        <input type="text" name="prize_winners[]" placeholder="e.g. John Doe"
                                            class="w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border">
                                    </div>
                                    <button type="button"
                                        class="remove-prize absolute -top-2 -right-2 bg-white text-red-500 hover:text-red-700 h-6 w-6 rounded-full border border-slate-200 shadow-sm hidden group-hover:flex items-center justify-center transition">
                                        <i class="fas fa-times text-xs"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end pt-6 border-t border-slate-100 mt-6">
                <button type="submit"
                    class="bg-indigo-600 text-white px-6 py-2.5 rounded-lg hover:bg-indigo-700 transition font-medium shadow-sm flex items-center">
                    <i class="fas fa-plus-circle mr-2"></i> Create Event
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const categorySelect = document.getElementById('eventCategory');
        const prizeSection = document.getElementById('prizeSection');
        const prizeList = document.getElementById('prizeList');
        const addPrizeBtn = document.getElementById('addPrizeBtn');

        const participationType = document.getElementById('participationType');
        const teamSizeSection = document.getElementById('teamSizeSection');

        function togglePrizeSection() {
            if (categorySelect.value === 'Competition') {
                prizeSection.classList.remove('hidden');
            } else {
                prizeSection.classList.add('hidden');
            }
        }

        function toggleTeamSize() {
            if (participationType.value === 'team') {
                teamSizeSection.classList.remove('hidden');
            } else {
                teamSizeSection.classList.add('hidden');
            }
        }

        categorySelect.addEventListener('change', togglePrizeSection);
        participationType.addEventListener('change', toggleTeamSize);

        togglePrizeSection(); // Initial check
        toggleTeamSize(); // Initial check


        addPrizeBtn.addEventListener('click', function () {
            const div = document.createElement('div');
            div.className = 'grid grid-cols-1 sm:grid-cols-2 gap-3 p-3 bg-white rounded border border-slate-200 relative group';
            div.innerHTML = `
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Prize Title</label>
                    <input type="text" name="prize_titles[]" placeholder="e.g. Next Prize" 
                           class="w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Winner (Optional)</label>
                    <input type="text" name="prize_winners[]" placeholder="e.g. Winner Name" 
                           class="w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border">
                </div>
                <button type="button" class="remove-prize absolute -top-2 -right-2 bg-white text-red-500 hover:text-red-700 h-6 w-6 rounded-full border border-slate-200 shadow-sm flex items-center justify-center transition">
                    <i class="fas fa-times text-xs"></i>
                </button>
            `;
            prizeList.appendChild(div);
        });

        prizeList.addEventListener('click', function (e) {
            if (e.target.closest('.remove-prize')) {
                const row = e.target.closest('.group');
                if (prizeList.children.length > 1) {
                    row.remove();
                } else {
                    row.querySelectorAll('input').forEach(input => input.value = '');
                }
            }
        });
    });

    // Image upload helpers
    function switchImageTab(tab) {
        const isUpload = tab === 'upload';
        document.getElementById('panelUpload').classList.toggle('hidden', !isUpload);
        document.getElementById('panelUrl').classList.toggle('hidden', isUpload);
        document.getElementById('tabUpload').className = isUpload
            ? 'px-4 py-2 bg-indigo-600 text-white transition'
            : 'px-4 py-2 bg-white text-slate-600 hover:bg-slate-50 transition';
        document.getElementById('tabUrl').className = !isUpload
            ? 'px-4 py-2 bg-indigo-600 text-white transition'
            : 'px-4 py-2 bg-white text-slate-600 hover:bg-slate-50 transition';
    }

    function handleImageFile(input) {
        const file = input.files[0];
        if (!file) return;
        if (file.size > 5 * 1024 * 1024) {
            alert('Image must be under 5MB.');
            input.value = '';
            return;
        }
        const reader = new FileReader();
        reader.onload = e => {
            const preview = document.getElementById('imagePreview');
            preview.src = e.target.result;
            preview.classList.remove('hidden');
            document.getElementById('dropZoneContent').classList.add('hidden');
            document.getElementById('clearImageBtn').classList.remove('hidden');
        };
        reader.readAsDataURL(file);
    }

    function clearImagePreview() {
        document.getElementById('coverImageFile').value = '';
        document.getElementById('imagePreview').classList.add('hidden');
        document.getElementById('imagePreview').src = '';
        document.getElementById('dropZoneContent').classList.remove('hidden');
        document.getElementById('clearImageBtn').classList.add('hidden');
    }

    function previewUrlImage(url) {
        const wrap = document.getElementById('urlPreviewWrap');
        const img = document.getElementById('urlPreview');
        if (url.startsWith('http')) {
            img.src = url;
            img.onerror = () => wrap.classList.add('hidden');
            img.onload = () => wrap.classList.remove('hidden');
        } else {
            wrap.classList.add('hidden');
        }
    }

    // Drag & drop
    const dropZone = document.getElementById('dropZone');
    if (dropZone) {
        dropZone.addEventListener('dragover', e => { e.preventDefault(); dropZone.classList.add('border-indigo-500', 'bg-indigo-50'); });
        dropZone.addEventListener('dragleave', () => dropZone.classList.remove('border-indigo-500', 'bg-indigo-50'));
        dropZone.addEventListener('drop', e => {
            e.preventDefault();
            dropZone.classList.remove('border-indigo-500', 'bg-indigo-50');
            const file = e.dataTransfer.files[0];
            if (file && file.type.startsWith('image/')) {
                const input = document.getElementById('coverImageFile');
                const dt = new DataTransfer();
                dt.items.add(file);
                input.files = dt.files;
                handleImageFile(input);
            }
        });
    }
</script>