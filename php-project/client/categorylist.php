<div>
    <h1 class="heading">Categories</h1>
    <?php
    // WHY require_once: db.php may already be loaded by another file; this avoids opening a second connection.
    require_once('./common/db.php');

    $query="select * from category";
$result = $conn->query($query);
foreach($result as $row){
    // WHY htmlspecialchars: escape names from the database before printing them as HTML.
    $name=  htmlspecialchars(ucfirst($row['name']));
    $id= $row['id'];
    echo "<div class='row question-list'>
    <h4><a href='?c-id=$id'>$name</a></h4>
    </div>";
}


    ?>
</div>
