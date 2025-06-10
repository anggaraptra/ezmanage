<?php
require_once 'functions.php';

function editProfile($user_id, $fullname, $current_password, $new_password, $confirm_password)
{
    $errors = [];

    // Ambil data user lama untuk pengecekan perubahan
    $old_user = get_user_by_id($user_id);

    // Validasi nama lengkap
    if (empty($fullname)) {
        $errors['fullname'] = "Nama lengkap wajib diisi.";
        setFlash('edit_profile', $errors['fullname'], 'error');
    }

    // Validasi perubahan password
    $password_changed = false;
    if (!empty($current_password) || !empty($new_password) || !empty($confirm_password)) {
        if (empty($current_password)) {
            $errors['current_password'] = "Password saat ini wajib diisi.";
            setFlash('edit_profile', $errors['current_password'], 'error');
        }
        if (empty($new_password)) {
            $errors['new_password'] = "Password baru wajib diisi.";
            setFlash('edit_profile', $errors['new_password'], 'error');
        }
        if (empty($confirm_password)) {
            $errors['confirm_password'] = "Konfirmasi password baru wajib diisi.";
            setFlash('edit_profile', $errors['confirm_password'], 'error');
        }

        if ($new_password !== $confirm_password) {
            $errors['confirm_password'] = "Konfirmasi password baru tidak cocok.";
            setFlash('edit_profile', $errors['confirm_password'], 'error');
        }

        $sql = "SELECT password_hash FROM users WHERE id = '$user_id'";
        $result = dbquery($sql);
        $user = mysqli_fetch_assoc($result);

        if ($user && !password_verify($current_password, $user['password_hash'])) {
            $errors['current_password'] = "Password saat ini salah.";
            setFlash('edit_profile', $errors['current_password'], 'error');
        } elseif ($user && password_verify($new_password, $user['password_hash'])) {
            $errors['new_password'] = "Password baru tidak boleh sama dengan password lama.";
            setFlash('edit_profile', $errors['new_password'], 'error');
        } else {
            $password_changed = !empty($new_password) && empty($errors);
        }
    }

    // Cek perubahan nama lengkap
    $fullname_changed = isset($old_user['fullname']) && $fullname !== $old_user['fullname'];

    // Cek perubahan foto profil
    $profile_pic_changed = false;
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
        $profile_pic_changed = true;
    }

    if (!empty($errors)) {
        // Set flash messages for each error
        foreach ($errors as $field => $msg) {
            $type = (stripos($msg, 'berhasil') !== false) ? 'success' : 'error';
            setFlash('edit_profile' . $field, $msg, $type);
        }
        return false;
    }

    // Jika tidak ada perubahan apapun
    if (!$fullname_changed && !$password_changed && !$profile_pic_changed) {
        setFlash('edit_profile', "Tidak ada perubahan.", 'info');
        return true;
    }

    $update_data = [];
    $fullname_updated = false;
    if ($fullname_changed) {
        $update_data[] = "fullname = '$fullname'";
        $fullname_updated = true;
    }

    if ($password_changed) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $update_data[] = "password_hash = '$hashed_password'";
    }

    // Handle upload foto profil jika ada
    if ($profile_pic_changed) {
        $pic = $_FILES['profile_pic'];
        $target_dir = '../assets/profiles/';
        // Pastikan direktori ada
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true);
        }

        // Ekstensi gambar yang diizinkan
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $file_info = pathinfo($pic['name']);
        $ext = strtolower($file_info['extension'] ?? '');

        // Ambil username untuk nama file
        $user = get_user_by_id($user_id);
        $username = $user['username'] ?? $user_id;

        // Maksimal ukuran file: 2MB (2 * 1024 * 1024 bytes)
        $max_size = 2 * 1024 * 1024;
        if ($pic['size'] > $max_size) {
            $errors['profile_pic'] = "Ukuran file tidak boleh lebih dari 2 MB.";
            setFlash('edit_profile', $errors['profile_pic'], 'error');
        } elseif (!in_array($ext, $allowed_ext)) {
            $errors['profile_pic'] = "Format gambar tidak valid. Format yang diizinkan: jpg, jpeg, png, gif, webp.";
            setFlash('edit_profile', $errors['profile_pic'], 'error');
        } else {
            $target_file = $target_dir . $username . '.' . $ext;
            if (!move_uploaded_file($pic['tmp_name'], $target_file)) {
                $errors['profile_pic'] = "Gagal mengunggah foto profil.";
                setFlash('edit_profile', $errors['profile_pic'], 'error');
            } else {
                // Opsional, hapus foto profil lama dengan ekstensi lain
                foreach ($allowed_ext as $old_ext) {
                    $old_file = $target_dir . $username . '.' . $old_ext;
                    if ($old_ext !== $ext && file_exists($old_file)) {
                        unlink($old_file);
                    }
                }
                $update_data[] = "profile_pic = '" . $username . "." . $ext . "'";
            }
        }
        // Jika ada error upload foto profil, tampilkan error dan hentikan proses
        if (!empty($errors)) {
            foreach ($errors as $field => $msg) {
                $type = (stripos($msg, 'berhasil') !== false) ? 'success' : 'error';
                setFlash('edit_profile' . $field, $msg, $type);
            }
            return false;
        }
    }

    $success = false;

    // Update nama lengkap jika berubah
    if ($fullname_changed) {
        $sql = "UPDATE users SET fullname = '$fullname' WHERE id = '$user_id'";
        if (dbquery($sql)) {
            setFlash('edit_profile', "Nama lengkap berhasil diubah.", 'success');
            $success = true;
        } else {
            setFlash('edit_profile', "Gagal mengubah nama lengkap.", 'error');
        }
    }

    // Update password jika berubah
    if ($password_changed) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $sql = "UPDATE users SET password_hash = '$hashed_password' WHERE id = '$user_id'";
        if (dbquery($sql)) {
            setFlash('edit_profile', "Password berhasil diubah.", 'success');
            $success = true;
        } else {
            setFlash('edit_profile', "Gagal mengubah password.", 'error');
        }
    }

    // Update foto profil jika berubah
    if ($profile_pic_changed && isset($update_data)) {
        // Cari data profile_pic di $update_data
        foreach ($update_data as $data) {
            if (strpos($data, "profile_pic") !== false) {
                $sql = "UPDATE users SET $data WHERE id = '$user_id'";
                if (dbquery($sql)) {
                    setFlash('edit_profile', "Foto profil berhasil diubah.", 'success');
                    $success = true;
                } else {
                    setFlash('edit_profile', "Gagal mengubah foto profil.", 'error');
                }
                break;
            }
        }
    }

    // Refresh session user jika ada perubahan
    if ($success) {
        $updated_user = get_user_by_id($user_id);
        $_SESSION['user'] = $updated_user;
        return true;
    } else {
        setFlash('edit_profile', "Tidak ada perubahan.", 'info');
        return true;
    }
}

// Main execution block
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_POST['user_id'];
    $fullname = $_POST['fullname'];
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if (editProfile($user_id, $fullname, $current_password, $new_password, $confirm_password)) {
        header("Location: ../profile.php");
        exit();
    } else {
        header("Location: ../profile.php");
        exit();
    }
}
