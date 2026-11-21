<?php
// AI panel owned by Quan; invoked from the Student view owned by An.
function renderAiPanel(array $lesson, int $lid, int $id): void {
    ?><aside class="panel ai-panel" id="study-assistant" aria-labelledby="assistant-title">
        <p class="eyebrow">TRỢ LÝ HỌC TẬP</p>
        <h2 id="assistant-title">Hiểu rõ hơn cùng AI</h2>
        <p class="small muted ai-context">Nguồn: <?=h($lesson['title'])?>. Mỗi câu hỏi độc lập.</p>
        <?php if(($_SESSION['ai_result']['lesson_id']??0)===$lid): ?><div class="ai-message" role="region" aria-label="Phản hồi trợ lý"><?=markdown($_SESSION['ai_result']['answer'])?></div><?php unset($_SESSION['ai_result']); endif; ?>
        <?=formStart('ai',['lesson_id'=>$lid,'course_id'=>$id,'return_page'=>'learn'])?>
            <?php area('question','Câu hỏi về bài học','','maxlength="1000" rows="4" aria-describedby="ai-privacy-note" placeholder="Bạn muốn hiểu rõ điều gì?"'); ?>
            <div class="ai-actions"><button class="btn btn-primary" name="mode" value="ask">Gửi câu hỏi</button><button class="btn btn-outline-secondary" name="mode" value="summarize">Tóm tắt</button></div>
        </form>
        <p class="small muted ai-note" id="ai-privacy-note">Không nhập dữ liệu riêng tư. AI có thể sai, hãy đối chiếu với nội dung bài học.</p>
    </aside><?php
}
