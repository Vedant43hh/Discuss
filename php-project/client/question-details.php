<?php
require_once("./common/db.php");

// WHY prepared statement: $qid comes from the URL, so it's bound as an integer ("i")
// instead of being pasted into the SQL text.
$stmt = $conn->prepare("select * from questions where id = ?");
$stmt->bind_param("i", $qid);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();

// WHY: if no question has this id, $row is null and using it would crash the page,
// so show a friendly message instead.
if (!$row) { ?>
<div class="container">
    <h1 class="heading">Question not found</h1>
</div>
<?php } else {
    $cid = $row['category_id'];
?>
<div class="container">
    <h1 class="heading">Question</h1>
    <div class="row">
        <div class="col-8">
            <?php
            // WHY htmlspecialchars: titles and descriptions are typed by users, so a <script> in them shows as text instead of running.
            echo "<h4 class='margin-bottom-15 question-title'>Question : " . htmlspecialchars($row['title']) . "</h4>
    <p class='margin-bottom-15'>" . htmlspecialchars($row['description']) . "</p>";
            include("answers.php");

            // WHY: an answer is saved with the logged-in user's id, so only show the form to logged-in users.
            if (isset($_SESSION['user'])) { ?>
            <?php // WHY id="answer-form": the "Answer" link in the question list jumps straight here. ?>
            <form id="answer-form" action="./server/requests.php" method="post">
                <input type="hidden" name="question_id" value="<?php echo $qid ?>">
                <textarea name="answer" class="form-control margin-bottom-15" placeholder="Your answer..."></textarea>
                <button class="btn btn-primary">Write your answer</button>
            </form>
            <?php } else { ?>
            <a id="answer-form" href="?login=true">Login to answer</a>
            <?php } ?>
        </div>
        <div class="col-4">

            <?php
            $categoryStmt = $conn->prepare("select name from category where id = ?");
            $categoryStmt->bind_param("i", $cid);
            $categoryStmt->execute();
            $categoryRow = $categoryStmt->get_result()->fetch_assoc();
            // WHY the if: a question saved without a category has no matching category row.
            if ($categoryRow) {
                echo "<h1>" . htmlspecialchars(ucfirst($categoryRow['name'])) . "</h1>";
            }

            $relatedStmt = $conn->prepare("select * from questions where category_id = ? and id != ?");
            $relatedStmt->bind_param("ii", $cid, $qid);
            $relatedStmt->execute();
            $result = $relatedStmt->get_result();
            foreach ($result as $row) {
                $id = $row['id'];
                $title = htmlspecialchars($row['title']);

                echo "<div class='question-list'>
                <h4><a href=?q-id=$id>$title</a></h4>
                </div>";
            }
            ?>
        </div>
    </div>
</div>
<?php } ?>
