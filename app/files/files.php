<?php
/**
 * Files: personal/team file manager with versioning, folders, trash
 */

class Ctl_index {
    public function run(): void {
        $u = require_login();
        $uid = (int)$u['id'];
        $parent = (int)($_GET['folder'] ?? 0);
        $trash = isset($_GET['trash']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            if (isset($_POST['new_folder'])) {
                require_perm('files.manage');
                $name = trim($_POST['new_folder']);
                // Reject path separators and traversal before handing to the C backend.
                if (preg_match('#[/\\\\\.]{2,}#', $name) || str_contains($name, '..') || !preg_match('/^[^\/\\\\]{1,80}$/', $name)) {
                    flash('danger', 'Invalid folder name.');
                    redirect('files' . ($parent ? '&folder=' . $parent : ''));
                }
                if ($name !== '') {
                    // Native C backend creates the directory (sandboxed, path-traversal safe).
                    $fsRoot = STORAGE_PATH . '/files/' . $uid;
                    if (!is_dir($fsRoot)) @mkdir($fsRoot, 0775, true);
                    $realPath = null;
                    if (CWorker::available()) {
                        $realPath = CWorker::mkdirSafe(STORAGE_PATH . '/files', $name, (string)$uid);
                    }
                    Database::insert('files', [
                        'school_id' => my_school_id(), 'user_id' => $uid, 'name' => $name,
                        'original_name' => '', 'path' => $realPath ? 'files/' . $uid . '/' . $name : '', 'is_folder' => 1, 'parent_id' => $parent ?: null,
                    ]);
                    log_activity('files.folder', 'Created folder ' . $name);
                    flash('success', 'Folder created.' . ($realPath && $fsRoot ? '' : ' (C backend unavailable)'));
                } else flash('danger', 'Folder name required.');
                redirect('files' . ($parent ? '&folder=' . $parent : ''));
            }
            if (($fid = (int)($_POST['rename'] ?? 0))) {
                require_perm('files.manage');
                $name = trim($_POST['rename_to'] ?? '');
                $f = Database::one("SELECT * FROM files WHERE id = ? AND user_id = ? AND deleted_at IS NULL", [$fid, $uid]);
                if ($f && $name !== '') {
                    Database::update('files', ['name' => $name], 'id = ?', [$fid]);
                    flash('success', 'Renamed.');
                } else flash('danger', 'Cannot rename.');
                redirect('files' . ($parent ? '&folder=' . $parent : ''));
            }
            if (($fid = (int)($_POST['move'] ?? 0)) && isset($_POST['to_folder'])) {
                $to = (int)$_POST['to_folder'];
                $f = Database::one("SELECT * FROM files WHERE id = ? AND user_id = ? AND deleted_at IS NULL", [$fid, $uid]);
                if ($f) {
                    $target = $to ? Database::one("SELECT * FROM files WHERE id = ? AND user_id = ? AND is_folder = 1 AND deleted_at IS NULL", [$to, $uid]) : null;
                    if (!$to || $target) {
                        if ($to === $parent) { redirect('files' . ($parent ? '&folder=' . $parent : '')); }
                        Database::update('files', ['parent_id' => $to ?: null], 'id = ?', [$fid]);
                        flash('success', 'Moved.');
                    } else flash('danger', 'Target folder not found.');
                } else flash('danger', 'Item not found.');
                redirect('files' . ($parent ? '&folder=' . $parent : ''));
            }
            if (isset($_POST['upload']) && !empty($_FILES['file'])) {
                require_perm('files.upload');
                $file = $_FILES['file'];
                $safeExts = ['jpg','jpeg','png','gif','webp','pdf','doc','docx','xls','xlsx','ppt','pptx','txt','csv','md','zip','rar','7z','mp3','wav','mp4','webm','ogg'];
                $res = upload_file($file, 'files', $safeExts);
                if ($res['error']) { flash('danger', $res['error']); }
                else {
                    // Sanitize filename to prevent XSS
                    $safeName = preg_replace('/[^\w\-\.]/', '_', $file['name']);
                    $safeName = preg_replace('/_{2,}/', '_', $safeName);
                    Database::insert('files', [
                        'school_id' => my_school_id(), 'user_id' => $uid,
                        'name' => $safeName, 'original_name' => $safeName,
                        'path' => $res['path'], 'mime' => $file['type'] ?? '', 'size' => $res['size'],
                        'version' => 1, 'parent_id' => $parent ?: null,
                    ]);
                    $fid = Database::insertId();
                    Database::insert('file_versions', ['file_id' => $fid, 'version' => 1, 'path' => $res['path'], 'size' => $res['size'], 'created_by' => $uid]);
                    log_activity('files.upload', 'Uploaded ' . $file['name']);
                    flash('success', 'File uploaded.');
                }
                redirect('files' . ($parent ? '&folder=' . $parent : ''));
            }
            if (($fid = (int)($_POST['new_version'] ?? 0))) {
                $f = Database::one("SELECT * FROM files WHERE id = ? AND user_id = ? AND deleted_at IS NULL", [$fid, $uid]);
                if ($f && !$f['is_folder'] && !empty($_FILES['file'])) {
                    $safeExts = ['jpg','jpeg','png','gif','webp','pdf','doc','docx','xls','xlsx','ppt','pptx','txt','csv','md','zip','rar','7z','mp3','wav','mp4','webm','ogg'];
                    $res = upload_file($_FILES['file'], 'files', $safeExts);
                    if (!$res['error']) {
                        $nv = (int)$f['version'] + 1;
                        Database::insert('file_versions', ['file_id' => $fid, 'version' => $nv, 'path' => $res['path'], 'size' => $res['size'], 'created_by' => $uid]);
                        Database::update('files', ['path' => $res['path'], 'size' => $res['size'], 'version' => $nv, 'mime' => $f['mime']], 'id = ?', [$fid]);
                        flash('success', 'New version uploaded (v' . $nv . ').');
                    } else flash('danger', $res['error']);
                }
                redirect('files&folder=' . $parent);
            }
            if (($fid = (int)($_POST['delete'] ?? 0))) {
                Database::run("UPDATE files SET deleted_at = NOW() WHERE id = ? AND user_id = ?", [$fid, $uid]);
                Database::run("UPDATE files SET deleted_at = NOW() WHERE user_id = ? AND deleted_at IS NULL AND parent_id = ?", [$uid, $fid]);
                flash('success', 'Moved to trash.');
                redirect('files' . ($parent ? '&folder=' . $parent : ''));
            }
            if (($fid = (int)($_POST['restore'] ?? 0))) {
                Database::run("UPDATE files SET deleted_at = NULL WHERE id = ? AND user_id = ?", [$fid, $uid]);
                flash('success', 'Restored.');
                redirect('files&trash=1');
            }
            if (($fid = (int)($_POST['purge'] ?? 0))) {
                $f = Database::one("SELECT * FROM files WHERE id = ? AND user_id = ? AND deleted_at IS NOT NULL", [$fid, $uid]);
                if ($f) {
                    Database::delete('files', 'id = ?', [$fid]);
                    Database::delete('file_versions', 'file_id = ?', [$fid]);
                    if ($f['path']) { $abs = safe_storage_path($f['path']); if ($abs && is_file($abs)) @unlink($abs); }
                    flash('success', 'Deleted permanently.');
                }
                redirect('files&trash=1');
            }
        }

        $crumbs = [];
        if (!$trash) {
            $cur = $parent ? Database::one("SELECT * FROM files WHERE id = ? AND user_id = ? AND is_folder = 1", [$parent, $uid]) : null;
            if ($parent && !$cur) { flash('danger', 'Folder not found.'); redirect('files'); }
            $tmp = $cur;
            while ($tmp) { array_unshift($crumbs, $tmp); $tmp = $tmp['parent_id'] ? Database::one("SELECT * FROM files WHERE id = ?", [$tmp['parent_id']]) : null; }
        }

        if ($trash) {
            $items = Database::all("SELECT * FROM files WHERE user_id = ? AND deleted_at IS NOT NULL ORDER BY deleted_at DESC", [$uid]);
        } else {
            $items = Database::all(
                "SELECT * FROM files WHERE user_id = ? AND parent_id " . ($parent ? "= ?" : "IS NULL") . " AND deleted_at IS NULL ORDER BY is_folder DESC, name",
                $parent ? [$uid, $parent] : [$uid]);
        }
        $folders = Database::all("SELECT id, name FROM files WHERE user_id = ? AND is_folder = 1 AND deleted_at IS NULL AND id != ? ORDER BY name", [$uid, $parent]);
        $quota = (int)Database::scalar("SELECT COALESCE(SUM(size),0) FROM files WHERE user_id = ? AND deleted_at IS NULL AND is_folder = 0", [$uid], 0);
        Router::render('app/files/index', [
            'title' => $trash ? 'Trash' : 'Files', 'items' => $items, 'parent' => $parent, 'crumbs' => $crumbs,
            'quota' => $quota, 'folders' => $folders, 'trash' => $trash,
        ]);
    }
}

class Ctl_view {
    public function run(): void {
        $u = require_login();
        $fid = (int)($_GET['id'] ?? 0);
        $f = Database::one("SELECT * FROM files WHERE id = ? AND user_id = ? AND deleted_at IS NULL", [$fid, $u['id']]);
        if (!$f) { flash('danger', 'File not found.'); redirect('files'); }
        $versions = Database::all("SELECT * FROM file_versions WHERE file_id = ? ORDER BY version DESC", [$fid]);
        Router::render('app/files/view', ['title' => 'File: ' . $f['original_name'], 'f' => $f, 'versions' => $versions]);
    }
}
