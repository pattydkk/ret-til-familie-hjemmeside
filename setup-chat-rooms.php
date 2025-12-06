<?php
/**
 * Chat Room System - Database Setup
 * Kør denne FIL én gang for at oprette chat-rum tabeller
 */

// Standalone script - manually connect to database
$db_config = [
    'host' => 'localhost',
    'user' => 'root',
    'pass' => '',
    'name' => 'ret_til_familie'
];

// Create connection
$mysqli = new mysqli($db_config['host'], $db_config['user'], $db_config['pass'], $db_config['name']);

if ($mysqli->connect_error) {
    die("❌ Database forbindelse fejlede: " . $mysqli->connect_error);
}

$mysqli->set_charset("utf8mb4");
$prefix = 'wp_';

// Chat rooms table
$table_rooms = $prefix . 'rtf_chat_rooms';
$sql_rooms = "CREATE TABLE IF NOT EXISTS $table_rooms (
    id int(11) NOT NULL AUTO_INCREMENT,
    name varchar(255) NOT NULL,
    description text,
    room_type varchar(50) NOT NULL COMMENT 'sagstype, landsdel, support',
    category varchar(100),
    is_private tinyint(1) DEFAULT 0,
    created_by int(11) NOT NULL,
    created_at datetime DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY room_type (room_type),
    KEY category (category)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

// Chat room messages
$table_room_messages = $prefix . 'rtf_chat_room_messages';
$sql_room_messages = "CREATE TABLE IF NOT EXISTS $table_room_messages (
    id int(11) NOT NULL AUTO_INCREMENT,
    room_id int(11) NOT NULL,
    user_id int(11) NOT NULL,
    message text NOT NULL,
    is_moderated tinyint(1) DEFAULT 0,
    moderation_reason varchar(255),
    created_at datetime DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY room_id (room_id),
    KEY user_id (user_id),
    KEY created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

// Chat room members
$table_room_members = $prefix . 'rtf_chat_room_members';
$sql_room_members = "CREATE TABLE IF NOT EXISTS $table_room_members (
    id int(11) NOT NULL AUTO_INCREMENT,
    room_id int(11) NOT NULL,
    user_id int(11) NOT NULL,
    joined_at datetime DEFAULT CURRENT_TIMESTAMP,
    last_read_at datetime,
    PRIMARY KEY (id),
    UNIQUE KEY room_user (room_id, user_id),
    KEY user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

// Execute SQL
$mysqli->query($sql_rooms);
$mysqli->query($sql_room_messages);
$mysqli->query($sql_room_members);

echo "<h1>✅ Chat Room Tabeller Oprettet</h1>";

// Create default rooms
$default_rooms = [
    [
        'name' => 'Støttechatten',
        'description' => 'Chat med admins og andre forældre - få støtte og hjælp',
        'room_type' => 'support',
        'category' => 'support',
        'is_private' => 0
    ],
    [
        'name' => 'Anbringelsessager',
        'description' => 'Chat om anbringelser og børnesager',
        'room_type' => 'sagstype',
        'category' => 'anbringelse',
        'is_private' => 0
    ],
    [
        'name' => 'Samværssager',
        'description' => 'Diskuter samvær og kontakt med dit barn',
        'room_type' => 'sagstype',
        'category' => 'samvaer',
        'is_private' => 0
    ],
    [
        'name' => 'Forældremyndighed',
        'description' => 'Chat om forældremyndighed og rettigheder',
        'room_type' => 'sagstype',
        'category' => 'forældremyndighed',
        'is_private' => 0
    ],
    [
        'name' => 'Sjælland',
        'description' => 'For forældre på Sjælland',
        'room_type' => 'landsdel',
        'category' => 'sjaelland',
        'is_private' => 0
    ],
    [
        'name' => 'Jylland',
        'description' => 'For forældre i Jylland',
        'room_type' => 'landsdel',
        'category' => 'jylland',
        'is_private' => 0
    ],
    [
        'name' => 'Fyn',
        'description' => 'For forældre på Fyn',
        'room_type' => 'landsdel',
        'category' => 'fyn',
        'is_private' => 0
    ],
    [
        'name' => 'Sverige',
        'description' => 'För föräldrar i Sverige',
        'room_type' => 'landsdel',
        'category' => 'sverige',
        'is_private' => 0
    ]
];

foreach ($default_rooms as $room) {
    $stmt = $mysqli->prepare("SELECT id FROM $table_rooms WHERE name = ?");
    $stmt->bind_param('s', $room['name']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $stmt = $mysqli->prepare("INSERT INTO $table_rooms (name, description, room_type, category, is_private, created_by, created_at) VALUES (?, ?, ?, ?, ?, 1, NOW())");
        $stmt->bind_param('ssssi', $room['name'], $room['description'], $room['room_type'], $room['category'], $room['is_private']);
        $stmt->execute();
        echo "<p>✅ Oprettet rum: <strong>{$room['name']}</strong></p>";
    } else {
        echo "<p>⚠️ Rum eksisterer allerede: <strong>{$room['name']}</strong></p>";
    }
}

echo "<hr>";
echo "<h2>📊 Status</h2>";
$count = $mysqli->query("SELECT COUNT(*) as cnt FROM $table_rooms")->fetch_assoc()['cnt'];
echo "<p>Chat rooms: " . $count . "</p>";
echo "<p><a href='/platform-chatrooms'>Gå til Chat Rum</a></p>";

$mysqli->close();
