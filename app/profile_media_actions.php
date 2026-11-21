<?php
function profile_media_actions(string $action, int $cid, array $stamp): never {
    $user = requireUser();
    if ($action==='profile_save') {
        // Identity, email and role are determined by the authenticated session.
        foreach (['user_id','role','email','auth_version','avatar_path'] as $forbidden) if (array_key_exists($forbidden,$_POST)) reject(t('Không được thay đổi định danh hoặc quyền tài khoản.'));
        $fields=['name'=>profileText('name',2,80),'birth_date'=>profileBirthDate(),'bio'=>profileText('bio',0,2000)];
        if ($user['role']==='instructor') $fields['qualifications']=profileText('qualifications',0,1000);
        elseif (array_key_exists('qualifications',$_POST)) reject(t('Chỉ giảng viên được cập nhật bằng cấp.'),403);
        $image=prepareProfileImage();
        saveProfileMedia('users',(int)$user['id'],'avatar_path',$fields,$image);
        flash(t('Đã cập nhật hồ sơ.'));go('profile');
    }
    $kind=$_POST['media_kind']??null;
    if ($kind==='course') {
        requireUser('instructor');$id=postId('course_id');ownerCourse($id);$table='courses';
    } elseif ($kind==='category') {
        requireUser('admin');$id=postId('category_id');$table='categories';
        if (!one('SELECT id FROM categories WHERE id=?',[$id])) reject(t('Không tìm thấy danh mục.'),404);
    } else reject(t('Loại ảnh không hợp lệ.'));
    $image=prepareProfileImage();
    if ($image===false) reject(t('Chọn ảnh mới hoặc đánh dấu xóa ảnh hiện tại.'));
    saveProfileMedia($table,$id,'image_path',[],$image);
    flash(t('Đã cập nhật hình ảnh.'));
    if ($kind==='course') go('edit',['id'=>$id]);
    go('admin',['tab'=>'categories']);
}
