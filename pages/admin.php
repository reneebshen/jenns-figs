<?php
include_once("includes/db.php");
$title = "Add Figs";

$db = open_sqlite_db("db/catalog.sqlite");

// form CSS classes
$show_form = TRUE;
$show_conf = FALSE;
$show_failure = FALSE;

// feedback CSS classes
$name_feedback_class = 'hidden';
$price_feedback_class = 'hidden';
$minzone_feedback_class = 'hidden';
$maxzone_feedback_class = 'hidden';

// values
$name = '';
$price = NULL;
$minzone = NULL;
$maxzone = NULL;
$description = '';

// sticky values
$sticky_name = '';
$sticky_price = '';
$sticky_minzone = '';
$sticky_maxzone = '';
$sticky_description = '';

// Did the user submit the form?
if (isset($_POST["submit"])) {

  // Get HTTP request user data
  $name = trim($_POST["name"]); // untrusted
  $price = $_POST["price"]; // untrusted
  $minzone = $_POST["min_zone"]; // untrusted
  $maxzone = $_POST["max_zone"]; // untrusted
  $description = trim($_POST["description"]); // untrusted

  $form_valid = TRUE;

  if (empty($name)) {
    $form_valid = FALSE;
    $name_feedback_class = '';
  }

  if (empty($price)) {
    $form_valid = FALSE;
    $price_feedback_class = '';
  }

  if (empty($minzone)) {
    $form_valid = FALSE;
    $minzone_feedback_class = '';
  }

  if (empty($maxzone)) {
    $form_valid = FALSE;
    $maxzone_feedback_class = '';
  }

  if (empty($description)) {
    $description_conf_class = 'hidden';
  }

  if ($form_valid) { // form valid, show conf
    $show_form = FALSE;
    $result = exec_sql_query(
      $db,
      "INSERT INTO figs (name, price, min_zone, max_zone, description, in_cart) VALUES (:nm, :pr, :minz, :maxz, :descr, 0);",
      array(
        ':nm' => $name,
        ':pr' => $price,
        ':minz' => $minzone,
        ':maxz' => $maxzone,
        ':descr' => $description
      )
    );
    if ($result) {
      $title = 'Add Confirmation';
      $show_conf = TRUE;
      $show_failure = FALSE;
    } else {
      $title = 'Add Failure';
      $show_conf = FALSE;
      $show_failure = TRUE;
    }
  } else { // form invalid, show feedback
    $sticky_name = $name; // tainted
    $sticky_price = $price; // tainted
    $sticky_minzone = $minzone; // tainted
    $sticky_maxzone = $maxzone; // tainted
    $sticky_description = $description; // tainted
  }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <title>
    <?php echo $title; ?> - Jenn's Figs
  </title>

  <link rel="stylesheet" type="text/css" href="public/styles/main.css" media="all" />
</head>

<body>

  <?php include('includes/header.php'); ?>
  <main class="admin">
    <?php if ($show_conf) { ?>
      <section>

        <h2>Successfully added: "<strong><?php echo htmlspecialchars($name); ?></strong>".</h2>
        <h2>Preview</h2>

        <body>
          <section class="catalog">
            <li class="tile">
              <div class="tile-main">
                <h3><?php echo htmlspecialchars($name); ?></h3>
                <p><?php echo htmlspecialchars($description); ?></p>
              </div>
              <div class="tile-info">
                <p>$<?php echo htmlspecialchars($price); ?></p>
                <p>Zone: <?php echo htmlspecialchars($minzone); ?> -
                  <?php echo htmlspecialchars($maxzone); ?></p>
                <h3>Add to cart</h3>
              </div>
            </li>
          </section>
        </body>

      </section>
    <?php } ?>

    <?php if ($show_failure) { ?>
      <section>
        <h2>Failed to add: "<strong><?php echo htmlspecialchars($name); ?></strong>".</h2>
        <p>Please <strong><a href="/admin">try again.</a></strong></p>
      </section>
    <?php } ?>

    <?php if ($show_form) { ?>
      <section>
        <form action='/admin' method="post" novalidate>
          <fieldset>
            <legend>
              <h2>Entry</h2>
            </legend>
            <p>
              Required fields are followed by
              <strong>*</strong>.
            </p>
            <p class='feedback <?php echo $name_feedback_class; ?>'>Please provide a fig name.</p>
            <div class="form-item">
              <label for="name">
                <span>Fig Name: </span>
              </label>
              <input id="name" type="text" name="name" value='<?php echo $sticky_name; ?>' /> *
            </div>

            <p class='feedback <?php echo $price_feedback_class; ?>'>Please provide a price (numerical).</p>
            <div class="form-item">
              <label for="price">
                <span>Price: </span>
              </label>
              <input id="price" type="number" name="price" value='<?php echo $sticky_price; ?>' /> *
            </div>

            <p class='feedback <?php echo $minzone_feedback_class; ?>'>Please provide a minimum geographic zone (numerical).</p>
            <div class="form-item">
              <label for="min_zone">
                <span>Minimum Zone: </span>
              </label>
              <input id="min_zone" type="number" name="min_zone" value='<?php echo $sticky_minzone; ?>' /> *
            </div>

            <p class='feedback <?php echo $maxzone_feedback_class; ?>'>Please provide a maximum geographic zone (numerical).</p>
            <div class="form-item">
              <label for="max_zone">
                <span>Maximum Zone: </span>
              </label>
              <input id="max_zone" type="number" name="max_zone" value='<?php echo $sticky_maxzone; ?>' /> *
            </div>
            <br>
            <div class="form-item">
              <label for="description">
                <span>Description</span>
              </label>
              <textarea id="description" name="description"><?php echo $sticky_description; ?></textarea>
            </div>

            <p>
              <input id='submit' type="submit" value="Add!" name='submit' />
            </p>

          </fieldset>

        </form>
      </section>
    <?php } ?>
  </main>

  <?php include('includes/footer.php'); ?>

</body>

</html>
