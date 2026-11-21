<?php
function mediaFormStart(string $action, array $fields=[]): string {
    return str_replace('<form ', '<form enctype="multipart/form-data" ', formStart($action,$fields));
}
function profileAvatar(array $user): void {
    $src=mediaUrl('avatar',(int)$user['id'],$user['avatar_path']??null);
    if($user['role']==='instructor' && !(int)($user['is_active']??1)) $src=null;
    if ($src) echo '<img class="profile-avatar" src="'.h($src).'" alt="" width="96" height="96">';
    else echo '<span class="profile-avatar avatar-placeholder" aria-hidden="true">'.h(preg_match('/\A./us',$user['name'],$match)?$match[0]:'SM').'</span>';
}
function courseInstructorAvatar(int $id): void {
    static $users;
    if ($users===null) { $users=[];foreach(all("SELECT * FROM users WHERE role='instructor'") as $u)$users[(int)$u['id']]=$u; }
    if (isset($users[$id])) profileAvatar($users[$id]);
}
function categoryPhoto(array $category): string {
    return mediaUrl('category',(int)$category['id'],$category['image_path']??null)
        ?? glassPhoto(['Lập trình web'=>'web','Lập trình'=>'programming','Cơ sở dữ liệu'=>'database'][$category['name']]??'default');
}
function imageUploadControl(string $label, ?string $current, ?string $fallback, bool $hasImage, bool $avatar=false): void {
    static $counter=0;$id='media-image-'.(++$counter);
    ?><div class="image-editor <?=$avatar?'avatar-editor':''?>" data-image-editor data-fallback="<?=h($fallback??'')?>" data-invalid-message="<?=h(t('Ảnh phải là JPEG/PNG, tối đa 2 MB.'))?>">
        <div class="image-preview"><img data-image-preview <?=$current?'src="'.h($current).'"':'hidden'?> alt="<?=h(t('Xem trước ảnh'))?>" width="320" height="180"><span data-image-placeholder <?=$current?'hidden':''?>><?=h(t('Chưa có ảnh'))?></span></div>
        <div class="image-controls"><label for="<?=$id?>"><?=h(t($label))?><input class="form-control" type="file" id="<?=$id?>" name="image" accept=".jpg,.jpeg,.png,image/jpeg,image/png" aria-describedby="<?=$id?>-help"></label>
        <p id="<?=$id?>-help" class="small muted"><?=h(t('JPEG/PNG tối đa 2 MB, 4096 px và 8 triệu điểm ảnh. Ảnh được xử lý lại khi lưu.'))?></p>
        <?php if($hasImage):?><label class="image-remove"><input type="checkbox" name="remove_image" value="1"> <?=h(t('Xóa ảnh hiện tại'))?></label><?php endif;?>
        <p class="small muted" data-image-status role="status"></p></div>
    </div><?php
}
function profilePosted(string $name, string $value): string {
    return ($_POST['action']??null)==='profile_save' && isset($_POST[$name]) && is_string($_POST[$name]) ? $_POST[$name] : $value;
}
function renderEditableProfile(): void {
    $user=requireUser();
    ?><main class="container profile-page"><?php heading(t('TÀI KHOẢN'),t('Hồ sơ của bạn'),t('Cập nhật thông tin để mọi người biết thêm về bạn.'));?>
    <div class="profile-layout"><aside class="panel profile-summary"><?php profileAvatar($user);?><h2><?=h($user['name'])?></h2><p><?=h($user['email'])?></p><span class="badge text-bg-light"><?=h(t(['student'=>'Học viên','instructor'=>'Giảng viên','admin'=>'Quản trị viên'][$user['role']]))?></span></aside>
    <section class="panel profile-panel"><h2><?=h(t('Thông tin cá nhân'))?></h2><?=mediaFormStart('profile_save',['return_page'=>'profile'])?>
    <div class="form-grid"><?php field('name',t('Họ và tên'),profilePosted('name',$user['name']),'text','required minlength="2" maxlength="80" autocomplete="name"');field('birth_date',t('Ngày sinh'),profilePosted('birth_date',$user['birth_date']??''),'date','min="1900-01-01" max="'.gmdate('Y-m-d').'" autocomplete="bday"');?></div>
    <p class="small muted"><?=h(t('Ngày sinh là tùy chọn và chỉ hiển thị trong hồ sơ của bạn.'))?></p>
    <?php area('bio',t('Giới thiệu bản thân'),profilePosted('bio',$user['bio']??''),'maxlength="2000" rows="5"');if($user['role']==='instructor'):area('qualifications',t('Bằng cấp và chuyên môn'),profilePosted('qualifications',$user['qualifications']??''),'maxlength="1000" rows="3"');?><p class="small muted"><?=h(t('Giới thiệu và bằng cấp của giảng viên hiển thị trên trang khóa học công khai.'))?></p><?php endif;?>
    <h3><?=h(t('Ảnh đại diện'))?></h3><?php imageUploadControl('Chọn ảnh đại diện',mediaUrl('avatar',(int)$user['id'],$user['avatar_path']??null),null,!empty($user['avatar_path']),true);?>
    <button class="btn btn-primary"><?=h(t('Lưu hồ sơ'))?></button></form></section></div></main><?php
}
function renderInstructorProfile(int $id): void {
    $user=one('SELECT * FROM users WHERE id=?',[$id]);
    if (!$user) return;
    ?><section class="panel instructor-profile"><div class="instructor-profile-heading"><?php profileAvatar($user);?><div><p class="eyebrow"><?=h(t('Giảng viên'))?></p><h2><?=h($user['name'])?></h2></div></div>
    <?php if(!empty($user['bio'])):?><p class="profile-text"><?=h($user['bio'])?></p><?php endif;?>
    <?php if(!empty($user['qualifications'])):?><h3><?=h(t('Bằng cấp và chuyên môn'))?></h3><p class="profile-text"><?=h($user['qualifications'])?></p><?php endif;?></section><?php
}
function renderCourseImageForm(array $course): void {
    $fallback=$course;$fallback['image_path']=null;$fallback=courseMedia($fallback)['src'];
    ?><section class="panel course-image-panel"><h2><?=h(t('Ảnh khóa học'))?></h2><p class="muted"><?=h(t('Ảnh tải lên được ưu tiên. Khi xóa ảnh, khóa học dùng lại ảnh bìa có sẵn.'))?></p>
    <?=mediaFormStart('media_save',['media_kind'=>'course','course_id'=>$course['id'],'return_page'=>'edit'])?>
    <?php imageUploadControl('Chọn ảnh khóa học',mediaUrl('course',(int)$course['id'],$course['image_path']??null)??$fallback,$fallback,!empty($course['image_path']));?>
    <button class="btn btn-primary"><?=h(t('Lưu ảnh khóa học'))?></button></form></section><?php
}
function renderCategoryImageForm(array $category): void {
    $fallback=$category;$fallback['image_path']=null;$fallback=categoryPhoto($fallback);
    ?><details class="category-image-panel"><summary><?=h(t('Ảnh danh mục'))?>: <?=h($category['name'])?></summary>
    <?=mediaFormStart('media_save',['media_kind'=>'category','category_id'=>$category['id'],'return_page'=>'admin','return_tab'=>'categories'])?>
    <?php imageUploadControl('Chọn ảnh danh mục',mediaUrl('category',(int)$category['id'],$category['image_path']??null)??$fallback,$fallback,!empty($category['image_path']));?>
    <button class="btn btn-light"><?=h(t('Lưu ảnh danh mục'))?></button></form></details><?php
}
