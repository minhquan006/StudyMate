<?php
// AI panel owned by Quan; invoked from the Student view owned by An.
function renderAiPanel(array $lesson, int $lid, int $id): void {
?><aside class="panel ai-panel"><p class="eyebrow">TRỢ LÝ HỌC TẬP</p><h3>Hiểu rõ hơn cùng AI</h3><p class="small muted">Nguồn: <?=h($lesson['title'])?>. Mỗi câu hỏi độc lập. Không nhập dữ liệu riêng tư.</p><?php if(($_SESSION['ai_result']['lesson_id']??0)===$lid):?><div class="ai-message"><?=markdown($_SESSION['ai_result']['answer'])?></div><?php unset($_SESSION['ai_result']);endif;echo formStart('ai',['lesson_id'=>$lid,'course_id'=>$id,'return_page'=>'learn']);area('question','Câu hỏi về bài học','','maxlength="1000" rows="3"');?><div class="flex gap"><button class="btn btn-primary" name="mode" value="ask">Gửi câu hỏi</button><button class="btn btn-outline-secondary" name="mode" value="summarize">Tóm tắt</button></div></form><p class="small muted mt-3">AI có thể sai. Kiểm tra lại kiến thức trong bài.</p></aside><?php
}
