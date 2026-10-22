<?php
session_start();
// WHY require_once: it loads db.php only once, even if another file already loaded it,
// so we never open extra database connections by accident.
require_once("../common/db.php");

// WHY: asking, answering and deleting need to know WHO is doing it. If nobody is logged in,
// send them to the login page instead of saving data with no user.
if ((isset($_POST['ask']) || isset($_POST['answer']) || isset($_GET['delete'])) && !isset($_SESSION['user'])) {
    header("Location: ../index.php?login=true");
    exit;
}

if (isset($_POST['signup'])) {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $address = $_POST['address'];

    // WHY: each email should belong to only one account, so check before inserting a new user.
    $check = $conn->prepare("select id from users where email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    if ($check->get_result()->num_rows > 0) {
        echo "This email is already registered";
        exit;
    }

    // WHY password_hash: never store the real password. If the database leaks, a hash can't
    // simply be read back as the password. password_hash also adds a random "salt" for you.
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // WHY ? + bind_param: the values travel separately from the SQL text, so input like
    // ' OR 1=1 -- is treated as plain data and can never change the query.
    // ("ssss" = four strings, one letter per ?)
    $user = $conn->prepare("Insert into `users`
(`id`,`username`,`email`,`password`,`address`)
values(NULL, ?, ?, ?, ?)");
    $user->bind_param("ssss", $username, $email, $hashedPassword, $address);

    $result = $user->execute();
    if ($result) {
        $_SESSION["user"] = ["username" => $username, "email" => $email, "user_id" => $user->insert_id];
        // WHY "../index.php": a relative path works no matter what the project folder is named.
        // WHY exit: header() only queues the redirect; exit stops the rest of the script from running.
        header("Location: ../index.php");
        exit;
    } else {
        echo "New user not registered";
    }

} else if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // WHY: look the user up by email only. The database stores a hash, not the password,
    // so the password itself is checked in PHP with password_verify() below.
    $query = $conn->prepare("select * from users where email = ?");
    $query->bind_param("s", $email);
    $query->execute();
    $row = $query->get_result()->fetch_assoc();

    // WHY password_verify: it hashes the typed password the same way and compares it to the stored hash.
    if ($row && password_verify($password, $row['password'])) {
        $_SESSION["user"] = ["username" => $row['username'], "email" => $row['email'], "user_id" => $row['id']];
        header("Location: ../index.php");
        exit;
    } else {
        // WHY one message for both cases: it doesn't tell strangers which emails have accounts.
        echo "Invalid email or password";
    }

} else if (isset($_GET['logout'])) {
    // WHY both: session_unset() empties the session's variables, session_destroy() deletes the session itself.
    session_unset();
    session_destroy();
    header("Location: ../index.php");
    exit;

} else if (isset($_POST["ask"])) {
    $title = $_POST['title'];
    $description = $_POST['description'];
    // WHY (int): category ids are numbers. An empty "Select A Category" becomes 0.
    $category_id = (int) $_POST['category'];
    $user_id = $_SESSION['user']['user_id'];

    // WHY: if the user typed a new category name, use it instead of the dropdown.
    // trim() removes spaces around the name, so typing only spaces counts as empty.
    $new_category = trim($_POST['new_category'] ?? '');
    if ($new_category !== '') {
        // WHY: the name column is VARCHAR(100), so a longer name would make the INSERT fail.
        if (mb_strlen($new_category) > 100) {
            echo "Category name must be 100 characters or less";
            exit;
        }

        // WHY: reuse a category that already exists instead of making a duplicate.
        // MySQL compares these names ignoring upper/lower case, so "PHP" finds "php".
        $findCategory = $conn->prepare("select id from category where name = ?");
        $findCategory->bind_param("s", $new_category);
        $findCategory->execute();
        $existing = $findCategory->get_result()->fetch_assoc();

        if ($existing) {
            $category_id = $existing['id'];
        } else {
            $addCategory = $conn->prepare("Insert into `category` (`id`,`name`) values(NULL, ?)");
            $addCategory->bind_param("s", $new_category);
            $addCategory->execute();
            // WHY insert_id: it's the id MySQL just gave the new category, so the question can point to it.
            $category_id = $addCategory->insert_id;
        }
    }

    // WHY "ssii": title and description are strings (s), category_id and user_id are integers (i).
    $question = $conn->prepare("Insert into `questions`
(`id`,`title`,`description`,`category_id`,`user_id`)
values(NULL, ?, ?, ?, ?)");
    $question->bind_param("ssii", $title, $description, $category_id, $user_id);

    $result = $question->execute();
    if ($result) {
        header("Location: ../index.php");
        exit;
    } else {
        // WHY: the old message said the question WAS added, which is the opposite of what happened.
        echo "Question could not be added";
    }

} else if (isset($_POST["answer"])) {
    $answer = $_POST['answer'];
    $question_id = (int) $_POST['question_id'];
    $user_id = $_SESSION['user']['user_id'];

    $query = $conn->prepare("Insert into `answers`
(`id`,`answer`,`question_id`,`user_id`)
values(NULL, ?, ?, ?)");
    $query->bind_param("sii", $answer, $question_id, $user_id);

    $result = $query->execute();
    if ($result) {
        header("Location: ../index.php?q-id=$question_id");
        exit;
    } else {
        echo "Answer is not submitted";
    }

} else if (isset($_GET["delete"])) {
    // WHY no echo here: printing anything before header() breaks the redirect ("headers already sent").
    $qid = (int) $_GET["delete"];

    // WHY: anyone can type this URL by hand, so the server must check that the
    // question really belongs to the logged-in user before deleting it.
    $check = $conn->prepare("select user_id from questions where id = ?");
    $check->bind_param("i", $qid);
    $check->execute();
    $question = $check->get_result()->fetch_assoc();
    if (!$question || (int) $question['user_id'] !== (int) $_SESSION['user']['user_id']) {
        echo "You can only delete your own questions";
        exit;
    }

    // WHY answers first: otherwise they'd stay in the table pointing at a question that no longer exists.
    $deleteAnswers = $conn->prepare("delete from answers where question_id = ?");
    $deleteAnswers->bind_param("i", $qid);
    $deleteAnswers->execute();

    $query = $conn->prepare("delete from questions where id = ?");
    $query->bind_param("i", $qid);
    $result = $query->execute();
    if ($result) {
        header("Location: ../index.php");
        exit;
    } else {
        echo "Question not deleted";
    }
}
?>
