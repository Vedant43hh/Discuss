<select class="form-control" name="category" id="category">
    <option value="">Select A Category</option>
    <?php
    // WHY require_once: db.php may already be loaded by another file; this avoids opening a second connection.
    require_once("./common/db.php");
    $query="select * from category";
    $result=$conn->query($query);
    foreach($result as $row){
        // WHY htmlspecialchars: escape names from the database before printing them as HTML.
        $name= htmlspecialchars(ucfirst($row['name']));
         $id= $row['id'];
       echo  "<option value=$id>$name</option>";
    }
    ?>
</select>

