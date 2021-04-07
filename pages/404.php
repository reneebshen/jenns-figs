<?php
$title = 'Page Not Found';
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

  <main>
    <h2>We're sorry.</h2>
    <p>The page you have requested was not found. Please navigate back to the <a href='/'>home page.</a></p>
  </main>

  <?php include('includes/footer.php'); ?>

</body>

</html>
