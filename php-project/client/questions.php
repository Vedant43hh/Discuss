<div class="container">

    <div class="row">
        <div class="col-8">
            <h1 class="heading">Questions</h1>
            <?php // WHY: a clear "+" button makes it obvious how to post a question. Guests who click it see the login form first. ?>
            <div class="text-end margin-bottom-15">
                <a class="btn btn-primary ask-btn" href="?ask=true">+ Ask a Question</a>
            </div>
            <?php
            require_once("./common/db.php");
            // WHY: a blank list looks broken, so we show a message when nothing is found.
            $emptyMessage = "No questions yet. Click \"+ Ask a Question\" to post the first one.";
            // WHY prepared statements: c-id, u-id and search come from the URL. With "?" placeholders
            // the values are sent separately from the SQL, so they can never change the query itself.
            if (isset($_GET["c-id"])) {
                $stmt = $conn->prepare("select * from questions where category_id = ?");
                $stmt->bind_param("i", $cid);
            } else if (isset($_GET["u-id"])) {
                $stmt = $conn->prepare("select * from questions where user_id = ?");
                $stmt->bind_param("i", $uid);
            } else if (isset($_GET["latest"])) {
                $stmt = $conn->prepare("select * from questions order by id desc");
            } else if (isset($_GET["search"])) {
                $stmt = $conn->prepare("select * from questions where `title` LIKE ?");
                // WHY: the % wildcards go into the bound value, not into the SQL string.
                $like = "%" . $search . "%";
                $stmt->bind_param("s", $like);
                $emptyMessage = "No questions found for \"" . htmlspecialchars($search) . "\".";
            } else {
                $stmt = $conn->prepare("select * from questions");
            }
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows === 0) {
                echo "<p class='text-center'>$emptyMessage</p>";
            }
            foreach ($result as $row) {
                // WHY htmlspecialchars: titles are typed by users, so a <script> in them shows as text instead of running.
                $title = htmlspecialchars($row['title']);
                $id = $row['id'];
                echo "<div class='row question-list'>
    <h4 class='my-question'><a href='?q-id=$id'>$title</a><span>";
                // WHY: shows where to click to answer. The answer form is on the question's own page,
                // and #answer-form makes the browser scroll straight down to it.
                echo "<a href='?q-id=$id#answer-form'>Answer</a>";
                // WHY: only the owner may delete a question, so only show the link to the user who asked it.
                if (isset($_SESSION['user']) && (int) $row['user_id'] === (int) $_SESSION['user']['user_id']) {
                    echo "<a class='ms-3' href='./server/requests.php?delete=$id'>Delete</a>";
                }
                echo "</span></h4></div>";
            }
            ?>
        </div>
        <div class="col-4">
            <?php include('categorylist.php'); ?>
        </div>
    </div>
</div>
