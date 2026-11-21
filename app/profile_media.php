<?php
// Images live in private storage. No request path or original filename is served.
function mediaFile(?string $key): ?string {
    global $storage;
    if (!$key || !preg_match('/\A[a-f0-9]{32}\.(jpg|png)\z/D', $key)) return null;
    $file = $storage.'/uploads/'.$key;
    return is_file($file) ? $file : null;
}
function mediaUrl(string $kind, int $id, ?string $key): ?string {
    return mediaFile($key) ? '/media.php?'.http_build_query(['kind'=>$kind,'id'=>$id,'v'=>$key]) : null;
}
function profileText(string $name, int $min, int $max): string {
    $value = $_POST[$name] ?? '';
    if (!is_string($value) || preg_match('//u', $value) !== 1 || preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', $value)) reject(t('Thông tin hồ sơ không hợp lệ.'));
    $value = trim($value);
    if (length($value)<$min || length($value)>$max) reject(t('Thông tin hồ sơ vượt giới hạn cho phép.'));
    return $value;
}
function profileBirthDate(): ?string {
    $date = profileText('birth_date',0,10);
    if ($date === '') return null;
    $parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $date, new DateTimeZone('UTC'));
    if (!$parsed || $parsed->format('Y-m-d')!==$date || $date<'1900-01-01' || $date>gmdate('Y-m-d')) reject(t('Ngày sinh phải hợp lệ, từ năm 1900 đến hôm nay.'));
    return $date;
}

/** Returns a new random file key, null for removal, or false for no change. */
function prepareProfileImage(): string|false|null {
    $remove = $_POST['remove_image'] ?? null;
    if ($remove!==null && $remove!=='1') reject(t('Lựa chọn xóa ảnh không hợp lệ.'));
    $file = $_FILES['image'] ?? null;
    if ($file !== null) {
        if (!is_array($file) || !isset($file['error']) || !is_int($file['error'])) reject(t('Tệp ảnh không hợp lệ.'));
        foreach (['name','tmp_name','type'] as $field) if (!isset($file[$field]) || !is_string($file[$field])) reject(t('Tệp ảnh không hợp lệ.'));
        if (!isset($file['size']) || !is_int($file['size'])) reject(t('Tệp ảnh không hợp lệ.'));
    }
    if ($file === null || $file['error'] === UPLOAD_ERR_NO_FILE) return $remove==='1' ? null : false;
    if ($remove==='1') reject(t('Chọn tải ảnh mới hoặc xóa ảnh, không chọn cả hai.'));
    if ($file['error']!==UPLOAD_ERR_OK || $file['size']<1 || $file['size']>2*1024*1024 || !is_uploaded_file($file['tmp_name'])) reject(t('Ảnh phải là JPEG/PNG, tối đa 2 MB.'));
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension,['jpg','jpeg','png'],true)) reject(t('Ảnh phải là JPEG/PNG, tối đa 2 MB.'));
    if (!extension_loaded('gd') || !extension_loaded('fileinfo')) reject(t('Máy chủ cần bật GD và Fileinfo để xử lý ảnh.'),503);
    $info = @getimagesize($file['tmp_name']);
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    $expected = $extension==='png' ? 'image/png' : 'image/jpeg';
    if (!$info || $mime!==$expected || ($info['mime']??null)!==$expected || $info[0]>4096 || $info[1]>4096 || $info[0]*$info[1]>8000000) reject(t('Ảnh không đọc được hoặc vượt 4096 px / 8 triệu điểm ảnh.'));
    $image = @imagecreatefromstring(file_get_contents($file['tmp_name']));
    if (!$image) reject(t('Tệp ảnh không hợp lệ.'));
    if ($expected==='image/jpeg' && function_exists('exif_read_data')) {
        $exif = @exif_read_data($file['tmp_name']);
        $orientation = (int)($exif['Orientation']??1);
        if (in_array($orientation,[2,4,5,7],true)) imageflip($image,$orientation===4 ? IMG_FLIP_VERTICAL : IMG_FLIP_HORIZONTAL);
        $angle = [3=>180,5=>90,6=>-90,7=>-90,8=>90][$orientation]??0;
        if ($angle) { $rotated = imagerotate($image,$angle,0); imagedestroy($image); $image=$rotated; }
    }
    // Re-encode pixels, stripping uploaded metadata and any trailing payload.
    $ratio = min(1,1600/max(imagesx($image),imagesy($image)));
    $clean = imagecreatetruecolor(max(1,(int)round(imagesx($image)*$ratio)),max(1,(int)round(imagesy($image)*$ratio)));
    imagealphablending($clean,false); imagesavealpha($clean,true);
    imagecopyresampled($clean,$image,0,0,0,0,imagesx($clean),imagesy($clean),imagesx($image),imagesy($image));
    global $storage;
    $directory = $storage.'/uploads';
    if (!is_dir($directory) && !mkdir($directory,0700,true) && !is_dir($directory)) { imagedestroy($clean); imagedestroy($image); throw new RuntimeException('Cannot create image storage'); }
    $key = bin2hex(random_bytes(16)).($expected==='image/png'?'.png':'.jpg');
    $path = $directory.'/'.$key;
    $stream = fopen($path,'xb');
    if (!$stream) { imagedestroy($clean); imagedestroy($image); throw new RuntimeException('Cannot save image'); }
    try {
        try { $ok = $expected==='image/png' ? imagepng($clean,$stream,6) : imagejpeg($clean,$stream,88); }
        finally { fclose($stream); imagedestroy($clean); imagedestroy($image); }
    } catch (Throwable $error) { @unlink($path); throw $error; }
    if (!$ok) { @unlink($path); throw new RuntimeException('Cannot encode image'); }
    chmod($path,0600);
    return $key;
}

/** Record and files change together; remove old bytes only after DB commit. */
function saveProfileMedia(string $table, int $id, string $column, array $fields, string|false|null $image): void {
    global $config;
    $old = null;
    try {
        transaction(function() use($table,$id,$column,$fields,$image,$config,&$old) {
            $row = one('SELECT * FROM '.$table.' WHERE id=?'.($config['db_driver']==='mysql'?' FOR UPDATE':''),[$id]);
            if (!$row) reject(t('Không tìm thấy đối tượng.'),404);
            foreach ([$column,...array_keys($fields)] as $field) if (!array_key_exists($field,$row)) reject(t('Chạy setup hoặc migration hồ sơ trước khi cập nhật.'),503);
            $old = $row[$column]??null;
            if ($image!==false) $fields[$column]=$image;
            $fields['updated_at']=now();
            $set=implode(',',array_map(fn($key)=>$key.'=?',array_keys($fields)));
            run('UPDATE '.$table.' SET '.$set.' WHERE id=?',[...array_values($fields),$id]);
        });
    } catch(Throwable $error) {
        if (is_string($image) && ($path=mediaFile($image))) @unlink($path);
        throw $error;
    }
    if ($image!==false && $old!==$image && ($path=mediaFile($old))) @unlink($path);
}

function serveProfileMedia(): never {
    $kind=$_GET['kind']??null;$id=$_GET['id']??null;
    if (!is_string($id) || !preg_match('/\A[1-9][0-9]*\z/D',$id) || filter_var($id,FILTER_VALIDATE_INT)===false) { http_response_code(404);exit; }
    $map=['avatar'=>['users','avatar_path'],'course'=>['courses','image_path'],'category'=>['categories','image_path']];
    if (!is_string($kind) || !isset($map[$kind])) { http_response_code(404);exit; }
    [$table,$column]=$map[$kind];$row=one('SELECT * FROM '.$table.' WHERE id=?',[$id]);
    if (!$row) { http_response_code(404);exit; }
    if (($kind==='course' && $row['status']!=='published') || ($kind==='avatar' && ($row['role']!=='instructor' || !(int)$row['is_active']))) {
        $user=currentUser();
        $allowed=$user && ($user['role']==='admin' || (int)$user['id']===(int)($kind==='avatar'?$row['id']:$row['instructor_id']));
        if (!$allowed && $kind==='course' && $row['status']==='archived' && $user) $allowed=(bool)one('SELECT id FROM enrollments WHERE course_id=? AND user_id=?',[$id,$user['id']]);
        if (!$allowed) { http_response_code(404);exit; }
    }
    $file=mediaFile($row[$column]??null);
    $stream=$file?@fopen($file,'rb'):false;
    if (!$stream) { http_response_code(404);exit; }
    header('Content-Type: '.(str_ends_with($file,'.png')?'image/png':'image/jpeg'));
    header('Content-Disposition: inline');header('Cache-Control: private, no-store');
    header('Content-Length: '.fstat($stream)['size']);
    fpassthru($stream);fclose($stream);exit;
}
