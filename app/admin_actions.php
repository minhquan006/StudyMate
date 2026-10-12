<?php
function admin_actions(string $action,int $cid,array $stamp):never{global $config;switch($action){
 case 'user_status':
  requireUser('admin');mustConfirm();$id=(int)($_POST['user_id']??0);$user=one('SELECT * FROM users WHERE id=?',[$id]);if(!$user||$user['role']==='admin')reject('Không được khóa/mở tài khoản Admin.',403);$active=(int)($_POST['is_active']??0);if(!in_array($active,[0,1],true))reject('Trạng thái không hợp lệ.');run('UPDATE users SET is_active=?,auth_version=auth_version+1,updated_at=? WHERE id=?',[$active,now(),$id]);flash('Đã cập nhật tài khoản; phiên cũ không còn hợp lệ.');go('admin');
 case 'category_save':
  requireUser('admin');$id=(int)($_POST['category_id']??0);$name=textInput('name',2,80);if(one('SELECT id FROM categories WHERE LOWER(name)=LOWER(?) AND id<>?',[$name,$id]))reject('Tên danh mục đã tồn tại.');if($id)run('UPDATE categories SET name=?,updated_at=? WHERE id=?',[$name,now(),$id]);else insertRow('categories',['name'=>$name]+$stamp);flash('Đã lưu danh mục.');go('admin',['tab'=>'categories']);
 case 'category_delete':
  requireUser('admin');mustConfirm();$id=(int)($_POST['category_id']??0);if((int)val('SELECT COUNT(*) FROM courses WHERE category_id=?',[$id]))reject('Danh mục đang được sử dụng.',409);try{run('DELETE FROM categories WHERE id=?',[$id]);}catch(PDOException $e){reject('Danh mục đang được sử dụng.',409);}flash('Đã xóa danh mục.');go('admin',['tab'=>'categories']);
default:reject('Thao tác không tồn tại.',404);}}
