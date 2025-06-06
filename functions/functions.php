<?php
require_once 'config.php';

// matikan error reporting
mysqli_report(MYSQLI_REPORT_OFF);

// koneksi ke database
if (!$mysqli = mysqli_connect(DBHOST, DBUSER, DBPASS, DBNAME)) {
    if (MODE == 'development') {
        // jika mode development
        exit('Gagal Koneksi: ' . mysqli_connect_error());
    } else {
        // show http 500 error
        http_response_code(500);
        // simpan error di log file
        exit();
    }
}

function dbquery($sql)
{
    global $mysqli;
    // eksekusi query
    if (!$result = mysqli_query($mysqli, $sql)) {
        if (MODE == 'development') {
            // jika mode development
            exit('Gagal Query: ' . mysqli_error($mysqli));
        } else {
            // show http 500 error
            http_response_code(500);
            // simpan error di log file
            exit();
        }
    }

    // kembalikan nilai hasil query
    return $result;
}

// fungsi untuk menampilkan pesan flash
function setFlash($key, $message, $type = 'success')
{
    $_SESSION['flash'][$key] = [
        'message' => $message,
        'type' => $type
    ];
}

function getFlash($key)
{
    if (isset($_SESSION['flash'][$key])) {
        $flash = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]); // Hanya tampil sekali
        return "<div class='flash-message {$flash['type']}'>" . htmlspecialchars($flash['message']) . "</div>";
    }
    return '';
}

function cek_session()
{
    if (!isset($_SESSION['user'])) {
        setFlash('auth', 'Silakan login terlebih dahulu!', 'info');
        header("Location: login_page.php");
        exit();
    }
}

// Fungsi untuk menambah todo 
function tambah_todo($user_id, $title, $description, $due_date, $status = 'Belum')
{
    global $mysqli;
    $user_id = intval($user_id);
    $title = mysqli_real_escape_string($mysqli, $title);
    $description = mysqli_real_escape_string($mysqli, $description);
    $due_date = mysqli_real_escape_string($mysqli, $due_date);
    $status = mysqli_real_escape_string($mysqli, $status);

    $sql = "INSERT INTO todos (user_id, title, description, due_date, status) 
                        VALUES ($user_id, '$title', '$description', '$due_date', '$status')";
    return dbquery($sql);
}

// Fungsi untuk mengedit todo
function edit_todo($id, $user_id, $title, $description, $due_date, $status)
{
    global $mysqli;
    $id = intval($id);
    $user_id = intval($user_id);
    $title = mysqli_real_escape_string($mysqli, $title);
    $description = mysqli_real_escape_string($mysqli, $description);
    $due_date = mysqli_real_escape_string($mysqli, $due_date);
    $status = mysqli_real_escape_string($mysqli, $status);

    $sql = "UPDATE todos SET 
                            title = '$title', 
                            description = '$description', 
                            due_date = '$due_date', 
                            status = '$status'
                        WHERE id = $id AND user_id = $user_id";
    return dbquery($sql);
}

// Fungsi untuk menangani submit form tambah dan edit todo
function handle_todo_form_submit()
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $user_id = $_SESSION['user']['id'];
        // Tambah todo
        if (empty($_POST['id']) && !empty($_POST['title']) && !empty($_POST['description']) && !empty($_POST['due_date'])) {
            $title = trim($_POST['title']);
            $description = trim($_POST['description']);
            $due_date = $_POST['due_date'];
            $status = isset($_POST['status']) ? $_POST['status'] : 'Belum';
            if (tambah_todo($user_id, $title, $description, $due_date, $status)) {
                echo "<script>window.location='todo.php';</script>";
                exit;
            } else {
                echo "<div class='text-red-500 mb-2'>Gagal menambah todo.</div>";
            }
        }
        // Edit todo
        if (
            !empty($_POST['id'])
            && !empty($_POST['title'])
            && !empty($_POST['description'])
            && !empty($_POST['due_date'])
            && isset($_POST['status'])
        ) {
            $id = intval($_POST['id']);
            $title = trim($_POST['title']);
            $description = trim($_POST['description']);
            $due_date = $_POST['due_date'];
            $status = $_POST['status'];
            if (edit_todo($id, $user_id, $title, $description, $due_date, $status)) {
                echo "<script>window.location='todo.php';</script>";
                exit;
            } else {
                echo "<div class='text-red-500 mb-2'>Gagal mengedit todo.</div>";
            }
        }
    }
}

// Fungsi untuk menghapus todo berdasarkan ID dan user_id
function hapus_todo($id, $user_id)
{
    $id = intval($id);
    $user_id = intval($user_id);
    $sql = "DELETE FROM todos WHERE id = $id AND user_id = $user_id";
    return dbquery($sql);
}

// Handler proses hapus todo 
function handle_hapus_todo()
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['delete_id'])) {
        $delete_id = intval($_POST['delete_id']);
        $user_id = $_SESSION['user']['id'];
        if (hapus_todo($delete_id, $user_id)) {
            echo "<script>window.location='todo.php';</script>";
            exit;
        } else {
            echo "<div class='text-red-500 mb-2'>Gagal menghapus todo.</div>";
        }
    }
}
