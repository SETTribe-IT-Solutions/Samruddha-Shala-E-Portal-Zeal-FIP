<?php
session_start();

require_once __DIR__ . '/../../db.php';

header('Content-Type: application/json');

if (!isset($pdo) || !$pdo) {
    echo json_encode([
        'success' => false,
        'message' => 'Database connection is not available.'
    ]);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
    $input = $_POST;
}

function sendJson($success, $message, $data = null, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

function normalizeName($value) {
    return trim((string)$value);
}

if ($method === 'GET') {
    try {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id > 0) {
            $stmt = $pdo->prepare('SELECT * FROM work_types WHERE id = :id');
            $stmt->execute([':id' => $id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$row) {
                sendJson(false, 'Work type not found.', null, 404);
            }
            sendJson(true, 'Work type fetched successfully.', $row);
        }

        $stmt = $pdo->query('SELECT * FROM work_types ORDER BY id DESC');
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        sendJson(true, 'Work types fetched successfully.', $rows);
    } catch (PDOException $e) {
        sendJson(false, 'Unable to fetch work types.', null, 500);
    }
}

if ($method === 'POST') {
    $action = isset($input['action']) ? $input['action'] : '';

    if ($action === 'add') {
        $name = normalizeName($input['name'] ?? '');
        if ($name === '') {
            sendJson(false, 'Work type name is required.', null, 422);
        }

        try {
            $check = $pdo->prepare('SELECT id FROM work_types WHERE LOWER(name) = LOWER(:name)');
            $check->execute([':name' => $name]);
            if ($check->fetch()) {
                sendJson(false, 'This work type already exists.', null, 409);
            }

            $stmt = $pdo->prepare('INSERT INTO work_types (name, status) VALUES (:name, :status)');
            $stmt->execute([
                ':name' => $name,
                ':status' => 'Active'
            ]);

            $id = (int)$pdo->lastInsertId();
            $created = $pdo->prepare('SELECT * FROM work_types WHERE id = :id');
            $created->execute([':id' => $id]);
            sendJson(true, 'Work type added successfully.', $created->fetch(PDO::FETCH_ASSOC));
        } catch (PDOException $e) {
            sendJson(false, 'Unable to add work type.', null, 500);
        }
    }

    if ($action === 'update') {
        $id = isset($input['id']) ? (int)$input['id'] : 0;
        $name = normalizeName($input['name'] ?? '');
        $status = isset($input['status']) ? trim((string)$input['status']) : 'Active';

        if ($id <= 0 || $name === '') {
            sendJson(false, 'Invalid work type data.', null, 422);
        }

        try {
            $check = $pdo->prepare('SELECT id FROM work_types WHERE LOWER(name) = LOWER(:name) AND id != :id');
            $check->execute([
                ':name' => $name,
                ':id' => $id
            ]);
            if ($check->fetch()) {
                sendJson(false, 'Another work type with the same name already exists.', null, 409);
            }

            $stmt = $pdo->prepare('UPDATE work_types SET name = :name, status = :status WHERE id = :id');
            $stmt->execute([
                ':name' => $name,
                ':status' => $status,
                ':id' => $id
            ]);

            if ($stmt->rowCount() === 0) {
                sendJson(false, 'Work type not found.', null, 404);
            }

            $fetch = $pdo->prepare('SELECT * FROM work_types WHERE id = :id');
            $fetch->execute([':id' => $id]);
            sendJson(true, 'Work type updated successfully.', $fetch->fetch(PDO::FETCH_ASSOC));
        } catch (PDOException $e) {
            sendJson(false, 'Unable to update work type.', null, 500);
        }
    }

    if ($action === 'delete') {
        $id = isset($input['id']) ? (int)$input['id'] : 0;
        if ($id <= 0) {
            sendJson(false, 'Invalid work type id.', null, 422);
        }

        try {
            $stmt = $pdo->prepare('DELETE FROM work_types WHERE id = :id');
            $stmt->execute([':id' => $id]);
            if ($stmt->rowCount() === 0) {
                sendJson(false, 'Work type not found.', null, 404);
            }
            sendJson(true, 'Work type deleted successfully.');
        } catch (PDOException $e) {
            sendJson(false, 'Unable to delete work type.', null, 500);
        }
    }
}

if ($method === 'PUT') {
    $id = isset($input['id']) ? (int)$input['id'] : 0;
    $name = normalizeName($input['name'] ?? '');
    $status = isset($input['status']) ? trim((string)$input['status']) : 'Active';

    if ($id <= 0 || $name === '') {
        sendJson(false, 'Invalid work type data.', null, 422);
    }

    try {
        $check = $pdo->prepare('SELECT id FROM work_types WHERE LOWER(name) = LOWER(:name) AND id != :id');
        $check->execute([
            ':name' => $name,
            ':id' => $id
        ]);
        if ($check->fetch()) {
            sendJson(false, 'Another work type with the same name already exists.', null, 409);
        }

        $stmt = $pdo->prepare('UPDATE work_types SET name = :name, status = :status WHERE id = :id');
        $stmt->execute([
            ':name' => $name,
            ':status' => $status,
            ':id' => $id
        ]);

        if ($stmt->rowCount() === 0) {
            sendJson(false, 'Work type not found.', null, 404);
        }

        $fetch = $pdo->prepare('SELECT * FROM work_types WHERE id = :id');
        $fetch->execute([':id' => $id]);
        sendJson(true, 'Work type updated successfully.', $fetch->fetch(PDO::FETCH_ASSOC));
    } catch (PDOException $e) {
        sendJson(false, 'Unable to update work type.', null, 500);
    }
}

if ($method === 'DELETE') {
    $id = isset($input['id']) ? (int)$input['id'] : 0;
    if ($id <= 0) {
        sendJson(false, 'Invalid work type id.', null, 422);
    }

    try {
        $stmt = $pdo->prepare('DELETE FROM work_types WHERE id = :id');
        $stmt->execute([':id' => $id]);
        if ($stmt->rowCount() === 0) {
            sendJson(false, 'Work type not found.', null, 404);
        }
        sendJson(true, 'Work type deleted successfully.');
    } catch (PDOException $e) {
        sendJson(false, 'Unable to delete work type.', null, 500);
    }
}

sendJson(false, 'Method not allowed.', null, 405);
