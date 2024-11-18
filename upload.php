<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['fileToUpload'])) {
    $targetDir = "uploads/";
    $fileName = basename($_FILES['fileToUpload']['name']);
    $fileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    $response = [];

    // Create the uploads directory if it doesn't exist
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }

    // Check if there was an error with the file upload
    if ($_FILES['fileToUpload']['error'] !== UPLOAD_ERR_OK) {
        $response['error'] = "File upload error: " . $_FILES['fileToUpload']['error'];
        echo json_encode($response);
        exit();
    }

    // Check file type
    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx'];
    if (!in_array($fileType, $allowedTypes)) {
        $response['error'] = "Invalid file type. Allowed types: JPG, JPEG, PNG, GIF, PDF, DOC, DOCX.";
        echo json_encode($response);
        exit();
    }

    // Generate a unique file name to avoid collisions
    $uniqueId = uniqid('', true);
    $uniqueFileName = $uniqueId . '-' . $fileName;
    $targetFile = $targetDir . $uniqueFileName;

    // Attempt to upload the file
    if (move_uploaded_file($_FILES['fileToUpload']['tmp_name'], $targetFile)) {
        $response['success'] = "File uploaded successfully!";
        $response['uniqueUrl'] = $targetFile;
    } else {
        $response['error'] = "Error uploading the file.";
    }

    // Send response
    echo json_encode($response);
    exit();
}

// Handle file listing for fetching uploaded files
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $targetDir = 'uploads/';

    // Check if the uploads directory exists
    if (!is_dir($targetDir)) {
        echo json_encode([]); // If the directory doesn't exist, return an empty array
        exit();
    }

    // Get the files in the directory
    $files = array_diff(scandir($targetDir), ['.', '..']);
    
    // Check if the files array is empty or not
    if (empty($files)) {
        echo json_encode([]); // Return empty array if no files
        exit();
    }

    // Map the file names to their full URLs
    $fileUrls = array_map(function ($file) use ($targetDir) {
        return $targetDir . $file;
    }, $files);

    // Log the files for debugging
    error_log("Files found: " . json_encode($fileUrls));

    // Return the file URLs in a simple array, not a keyed object
    echo json_encode($fileUrls);
    exit();
}
?>
