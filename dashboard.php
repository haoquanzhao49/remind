<?php
session_start();
require 'config.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch user-defined categories
$stmt = $pdo->prepare("SELECT * FROM categories WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$categories = $stmt->fetchAll();

// Fetch notes with category and public/private status
$stmt = $pdo->prepare("SELECT n.*, c.name AS category_name FROM notes n LEFT JOIN categories c ON n.category_id = c.id WHERE n.user_id = ? ORDER BY n.created_at DESC");
$stmt->execute([$user_id]);
$notes = $stmt->fetchAll();

// Handle saving a new category
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['category_name'])) {
    $category_name = trim($_POST['category_name']);
    if (!empty($category_name)) {
        $stmt = $pdo->prepare("INSERT INTO categories (user_id, name) VALUES (?, ?)");
        $stmt->execute([$user_id, $category_name]);
        header("Location: dashboard.php");
        exit;
    }
}

// Handle deleting a category
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_category_id'])) {
    $category_id = $_POST['delete_category_id'];
    $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ? AND user_id = ?");
    $stmt->execute([$category_id, $user_id]);
    // Update notes with this category_id to NULL
    $stmt = $pdo->prepare("UPDATE notes SET category_id = NULL WHERE category_id = ? AND user_id = ?");
    $stmt->execute([$category_id, $user_id]);
    header("Location: dashboard.php");
    exit;
}

// Handle saving a new note or updating public/private status
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['title']) && isset($_POST['content'])) {
        $title = $_POST['title'];
        $content = $_POST['content'];
        $reminder = $_POST['reminder'] ? $_POST['reminder'] : null;
        $tags = $_POST['tags'];
        $category_id = $_POST['category_id'] ?? null; // Use selected category or null
        $is_public = isset($_POST['is_public']) ? 1 : 0; // 1 for public, 0 for private

        // Simulated AI logic (replace with OpenAI API for real AI)
        $ai_suggestion = "Based on your note, consider breaking this into smaller tasks.";
        $ai_summary = substr($content, 0, 50) . "..."; // Simple summary

        $stmt = $pdo->prepare("INSERT INTO notes (user_id, title, content, reminder, tags, ai_suggestion, ai_summary, is_public, category_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $title, $content, $reminder, $tags, $ai_suggestion, $ai_summary, $is_public, $category_id]);

        header("Location: dashboard.php");
        exit;
    } elseif (isset($_POST['note_id']) && isset($_POST['is_public'])) {
        $note_id = $_POST['note_id'];
        $is_public = $_POST['is_public'] ? 1 : 0;
        $stmt = $pdo->prepare("UPDATE notes SET is_public = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([$is_public, $note_id, $user_id]);
        header("Location: dashboard.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>ReMind - Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar (Categories) -->
        <aside class="sidebar">
            <h2>Categories</h2>
            <form method="POST" class="category-form">
                <input type="text" name="category_name" placeholder="New Category" required>
                <button type="submit">Add</button>
            </form>
            <ul>
                <?php foreach ($categories as $category): ?>
                    <li>
                        <span><?php echo htmlspecialchars($category['name']); ?></span>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="delete_category_id" value="<?php echo $category['id']; ?>">
                            <button type="submit" onclick="return confirm('Are you sure you want to delete this category?')">Delete</button>
                        </form>
                    </li>
                <?php endforeach; ?>
            </ul>
        </aside>

        <!-- Main Content Area -->
        <main class="main-content">
            <div class="toolbar">
                <button>Save</button>
                <button>Export</button>
                <button>View</button>
            </div>

            <h1>Your Notes</h1>

            <!-- Note Creation Form -->
            <form method="POST" class="note-form">
                <input type="text" name="title" placeholder="Note Title" required><br>
                <textarea name="content" placeholder="Your note..." required></textarea><br>
                <input type="datetime-local" name="reminder" placeholder="Set Reminder"><br>
                <input type="text" name="tags" placeholder="Tags (comma-separated)"><br>
                <select name="category_id">
                    <option value="">No Category</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo $category['id']; ?>">
                            <?php echo htmlspecialchars($category['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select><br>
                <label>
                    <div class="switch">
                        <input type="checkbox" name="is_public" id="is_public_new" <?php echo isset($_POST['is_public']) ? 'checked' : ''; ?> onchange="togglePublic(this, null)">
                        <span class="slider round"></span>
                    </div>
                    <span>Make this note public</span>
                </label><br>
                <button type="submit">Save Note</button>
            </form>

            <!-- Notes List (Grouped by Category) -->
            <div class="notes-list">
                <?php
                $notes_by_category = [];
                foreach ($notes as $note) {
                    $category_name = $note['category_name'] ?: 'No Category';
                    if (!isset($notes_by_category[$category_name])) {
                        $notes_by_category[$category_name] = [];
                    }
                    $notes_by_category[$category_name][] = $note;
                }
                foreach ($notes_by_category as $category_name => $category_notes): ?>
                    <h2><?php echo htmlspecialchars($category_name); ?></h2>
                    <?php foreach ($category_notes as $note): ?>
                        <div class="note">
                            <h3><?php echo htmlspecialchars($note['title']); ?></h3>
                            <img src="https://via.placeholder.com/300x200?text=Heart+Diagram" alt="Note Image" class="note-image">
                            <p><?php echo htmlspecialchars($note['content']); ?></p>
                            <?php if ($note['reminder']) echo "<p><strong>Reminder:</strong> " . $note['reminder'] . "</p>"; ?>
                            <?php if ($note['tags']) echo "<p><strong>Tags:</strong> " . htmlspecialchars($note['tags']) . "</p>"; ?>
                            <?php if ($note['ai_suggestion']) echo "<p><strong>AI Suggestion:</strong> " . htmlspecialchars($note['ai_suggestion']) . "</p>"; ?>
                            <?php if ($note['ai_summary']) echo "<p><strong>AI Summary:</strong> " . htmlspecialchars($note['ai_summary']) . "</p>"; ?>
                            <label>
                                <div class="switch">
                                    <input type="checkbox" id="is_public_<?php echo $note['id']; ?>" <?php echo $note['is_public'] ? 'checked' : ''; ?> onchange="togglePublic(this, <?php echo $note['id']; ?>)">
                                    <span class="slider round"></span>
                                </div>
                                <span>Make this note public</span>
                            </label>
                        </div>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </div>
        </main>

        <!-- Tablet Preview (Placeholder) -->
        <aside class="tablet-preview">
            <h2>Tablet View</h2>
            <div class="note-preview">
                <?php if (!empty($notes)): ?>
                    <h3><?php echo htmlspecialchars($notes[0]['title']); ?></h3>
                    <img src="https://via.placeholder.com/200x150?text=Heart+Diagram" alt="Preview Image">
                    <p><?php echo htmlspecialchars(substr($notes[0]['content'], 0, 100)) . "..."; ?></p>
                <?php else: ?>
                    <p>No notes yet.</p>
                <?php endif; ?>
            </div>
        </aside>
    </div>

    <script>
        function togglePublic(checkbox, noteId) {
            const isPublic = checkbox.checked ? 1 : 0;
            if (noteId) { // Update existing note
                fetch('dashboard.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'note_id=' + encodeURIComponent(noteId) + '&is_public=' + encodeURIComponent(isPublic)
                });
            } else { // Update new note (form)
                document.querySelector('input[name="is_public"]').value = isPublic;
            }
        }
    </script>
</body>
</html>