<div class="container">
<h1 class="heading">Ask A Question </h1>

<form action="./server/requests.php" method="post">

  <div class="col-6 offset-sm-3 margin-bottom-15">
    <label for="title" class="form-label">Title</label>
    <input type="text" name="title" class="form-control" id="title" placeholder="Enter question">
  </div>

  <div class="col-6 offset-sm-3 margin-bottom-15">
    <label for="description" class="form-label">Description</label>
    <textarea name="description" class="form-control" id="description" placeholder="Enter question"></textarea>
  </div>

  <div class="col-6 offset-sm-3 margin-bottom-15">
    <label for="category" class="form-label">Category</label>
    <?php
    include("category.php");
    ?>
  </div>

  <div class="col-6 offset-sm-3 margin-bottom-15">
    <?php // WHY: lets the asker create a category that isn't in the list. If filled in, it's used instead of the dropdown. ?>
    <label for="new_category" class="form-label">Or add a new category</label>
    <input type="text" name="new_category" class="form-control" id="new_category" maxlength="100"
      placeholder="e.g. python (leave empty to use the list above)">
  </div>
  <div class="col-6 offset-sm-3 margin-bottom-15">
  <button type="submit" name="ask" class="btn btn-primary">Ask Question</button>

  </div>

</form>

</div>