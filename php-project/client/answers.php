<?php
// WHY this file: question-details.php includes it to list every answer for the current question ($qid).
require_once("./common/db.php");

// WHY JOIN: the answers table only stores user_id, so we join the users table to get the username.
$answerStmt = $conn->prepare("select answers.answer, users.username from answers
join users on answers.user_id = users.id
where answers.question_id = ?
order by answers.id");
$answerStmt->bind_param("i", $qid);
$answerStmt->execute();
$answers = $answerStmt->get_result();
?>
<div class="margin-bottom-15">
    <h5>Answers</h5>
    <?php if ($answers->num_rows === 0) { ?>
        <p>No answers yet</p>
    <?php } else {
        foreach ($answers as $answer) { ?>
            <div class="answer-wrapper">
                <?php // WHY htmlspecialchars: answers and usernames are typed by users, so a <script> in them shows as text instead of running. ?>
                <p><?php echo htmlspecialchars($answer['answer']) ?></p>
                <small>Answered by <?php echo htmlspecialchars(ucfirst($answer['username'])) ?></small>
            </div>
    <?php }
    } ?>
</div>
