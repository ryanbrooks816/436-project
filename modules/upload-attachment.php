<?php
function handle_ticket_attachments($pdo, $ticket_id, $feedback_id, $ticket_pub_id): array
{
    $upload_dir = "../images/tickets/attachments/" . $ticket_pub_id . "/";
    $db_path_prefix = "images/tickets/attachments/" . $ticket_pub_id . "/";
    $messages = [];

    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    foreach ($_FILES['attachments']['tmp_name'] as $key => $tmp_name) {
        $file_name = time() . "_" . basename($_FILES['attachments']['name'][$key]);
        $file_path = $upload_dir . $file_name;
        $db_file_path = $db_path_prefix . $file_name;
        $file_size = $_FILES['attachments']['size'][$key];
        $file_type = mime_content_type($tmp_name);

        if ($file_size > 2 * 1024 * 1024) {
            $messages[] = "File '{$file_name}' exceeds 2MB size limit.";
            continue;
        }

        if (!in_array($file_type, ['image/jpeg', 'image/png', 'image/gif', 'image/webp'])) {
            $messages[] = "Invalid file type for '{$file_name}'. Only JPEG, PNG, GIF, and WEBP are allowed.";
            continue;
        }

        if (!move_uploaded_file($tmp_name, $file_path)) {
            $messages[] = "Failed to upload '{$file_name}'.";
            continue;
        }

        $sql = "INSERT INTO Ticket_Attachments (ticket_id, feedback_id, file_path) VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        if (!$stmt->execute([$ticket_id, $feedback_id, $db_file_path])) {
            $messages[] = "Failed to save attachment '{$file_name}' to database.";
        }
    }

    return $messages;
}
