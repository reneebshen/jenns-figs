<?php
$title = "Browse Figs";

// connect to databse
include_once("includes/db.php");
$db = open_sqlite_db("db/catalog.sqlite");

// list of valid attributes for sort/filter
$attr = array("price", "min_zone", "max_zone", "name");

// search, filter, sort valeues
$sql_query = 'SELECT * FROM figs';
$sql_query_params = array();
// flags
$filter = False;
$sort = False;
$has_where = False;

// SEARCH

// form values
$search_terms = trim($_GET['q']); // untrusted
// if empty, set to NULL
if (empty($search_terms)) {
  $search_terms = NULL;
}

// sticky
$sticky_search = $search_terms; // tainted

// build search SQL
if ($search_terms) {
  $sql_query = $sql_query . " WHERE ((name LIKE '%' || :search || '%') OR (description LIKE '%' || :search || '%'))";
  $sql_query_params[':search'] = $search_terms;

  // update flags
  $has_where = True;
  $search = True;
}

// FILTER

// get values
$filter_min_price = trim($_GET['minpr']); // untrusted
$filter_max_price = trim($_GET['maxpr']); // untrusted
$filter_min_zone = trim($_GET['minzn']); // untrusted
$filter_max_zone = trim($_GET['maxzn']); // untrusted

// icon img
$arrow_up = 'filled_up.png';
$arrow_dn = 'empty_dn.png';

// filter submitted
if (!(empty($filter_min_price) && empty($filter_max_price) && empty($filter_min_zone) && empty($filter_max_zone))) {
  $sql_filter = '';

  if (!empty($filter_min_price)) {
    $sql_filter = $sql_filter . "(price >= :minprice)";
    $sql_query_params[':minprice'] = $filter_min_price;
    $filter = True;
  }
  if (!empty($filter_max_price)) {
    $sql_filter = $sql_filter . ($filter ? ' AND '  : '') . "(price <= :maxprice)";
    $sql_query_params[':maxprice'] = $filter_max_price;
    $filter = True;
  }
  if (!empty($filter_min_zone)) {
    $sql_filter = $sql_filter . ($filter ? ' AND '  : '') . "(min_zone <= :minzone)";
    $sql_query_params[':minzone'] = $filter_min_zone;
    $filter = True;
  }
  if (!empty($filter_max_zone)) {
    $sql_filter = $sql_filter . ($filter ? ' AND '  : '') . "(max_zone <= :maxzone)";
    $sql_query_params[':maxzone'] = $filter_max_zone;
    $filter = True;
  }

  // check for WHERE
  if (!$has_where) {
    $sql_query = $sql_query . ' WHERE ';
    $has_where = True;
  }

  // add AND if search
  if ($search) {
    $sql_query = $sql_query . ' AND ';
  }

  // append filter to query
  $sql_query = $sql_query . '(' . $sql_filter . ')';

  $sticky_min_price_filter = $filter_min_price; // tainted
  $sticky_max_price_filter = $filter_max_price; // tainted
  $sticky_min_zone_filter = $filter_min_zone; // tainted
  $sticky_max_zone_filter = $filter_max_zone; // tainted
}

// SORT
$sort_attr = trim($_GET['sort']); // untrusted
$sort_order = trim($_GET['order']); // untrusted

// css classes
$sort_css = array(
  'asc' => 'inactive',
  'desc' => 'inactive'
);

// sort action
if ($sort_order == 'DESC') {
  $order_next = 'ASC';
  $sort_icon = 'down';
} else { // default is asc sort by name
  $sort_order = 'ASC';
  $order_next = 'DESC';
  $sort_icon = 'up';
}

if (in_array($sort_attr, array('price', 'name', 'min_zone', 'max_zone'))) {
  $sql_query = $sql_query . ' ORDER BY ' . $sort_attr . ' ' . $sort_order;
} else {
  $sort_attr = 'name';
}

if ($sort_icon == "up") {
  $arrow = 'point_up.png';
} else if ($sort_icon == "down") {
  $arrow = 'point_dn.png';
}

// remember search/filtering

$sort_url = '/?';

$sort_query = http_build_query(
  array(
    'q' => $search_terms,
    'minpr' => $filter_min_price,
    'maxpr' => $filter_max_price,
    'minzn' => $filter_min_zone,
    'maxzn' => $filter_max_zone
  )
);

$sort_url = $sort_url . $sort_query;

$sticky_min_zone_sort = ($sort_attr == 'min_zone' ? 'selected' : '');
$sticky_max_zone_sort = ($sort_attr == 'max_zone' ? 'selected' : '');
$sticky_price_sort = ($sort_attr == 'price' ? 'selected' : '');
$sticky_default_sort = ($sort_attr == 'name' ? 'selected' : '');


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
    <section class="sidebar">
      <form action='/' method="get" novalidate>
        <fieldset>
          <div class="form-item">
            <label for="sort">
              <h3>
                Sort
                <a href="<?php echo $sort_url . '&sort=' . $sort_attr . '&order=' . $order_next; ?>" aria-label="sort order">
                  <!-- As stated on course Ed, both icons used are also cited via a link to citations page in the footer -->
                  <!-- Source: https://thenounproject.com/kmgdesignid/collection/maps-and-navigation-solid/?i=3823949 -->
                  <img src="/public/images/<?php echo htmlspecialchars($arrow); ?>" alt="Sort order indicator" class="icon">
                </a>
              </h3>
              <label>
                <select id="sort" name="sort">
                  <option value="name" <?php echo $sticky_default_sort; ?>>Name</option>
                  <option value="price" <?php echo $sticky_price_sort; ?>>Price</option>
                  <option value="min_zone" <?php echo $sticky_min_zone_sort; ?>>Min. Zone</option>
                  <option value="max_zone" <?php echo $sticky_max_zone_sort; ?>>Max. Zone</option>
                </select>
              </label>
              <br>
              <input type="submit" value="Apply Sort">
          </div>

          <!-- Remember sort order, search, filter -->
          <input type="hidden" name="order" value="<?php echo $sort_order; ?>" />
          <input type="hidden" name="q" value="<?php echo htmlspecialchars($search_terms); ?>" />
          <input type="hidden" name="minpr" value="<?php echo $filter_min_price; ?>" />
          <input type="hidden" name="maxpr" value="<?php echo $filter_max_price; ?>" />
          <input type="hidden" name="minzn" value="<?php echo $filter_min_zone; ?>" />
          <input type="hidden" name="maxzn" value="<?php echo $filter_max_zone; ?>" />

      </form>
      <br>
      <hr>
      <form action='/' method="get" novalidate>
        <div class="form-item">
          <h3><label for='filter'>Filter By</label></h3>
          <br>
          <p>Price</p>
          <input id="filter" type="number" name="minpr" required value="<?php echo htmlspecialchars($sticky_min_price_filter); ?>" />
          to
          <input id="filter" type="number" name="maxpr" required value="<?php echo htmlspecialchars($sticky_max_price_filter); ?>" />
          <p>Zone</p>
          <input id="filter" type="number" name="minzn" required value="<?php echo htmlspecialchars($sticky_min_zone_filter); ?>" />
          to
          <input id="filter" type="number" name="maxzn" required value="<?php echo htmlspecialchars($sticky_max_zone_filter); ?>" />
          <br>
          <input type="submit" value="Apply Filter">
        </div>
        </fieldset>

        <!-- Remember search and sort -->
        <input type="hidden" name="q" value="<?php echo htmlspecialchars($search_terms); ?>" />
        <input type="hidden" name="sort" value="<?php echo $sort_attr; ?>" />
        <input type="hidden" name="order" value="<?php echo $sort_order; ?>" />

      </form>
    </section>
    <section class="search">
      <div class='searchbar'>
        <form action="/" method="get" novalidate>
          <label for="search">Search:</label>
          <input id="search" type="text" name="q" required value="<?php echo htmlspecialchars($sticky_search); ?>" />
          <input type="submit" value="Search">

          <!-- Remember filter + sort -->
          <input type="hidden" name="minpr" value="<?php echo $filter_min_price; ?>" />
          <input type="hidden" name="maxpr" value="<?php echo $filter_max_price; ?>" />
          <input type="hidden" name="minzn" value="<?php echo $filter_min_zone; ?>" />
          <input type="hidden" name="maxzn" value="<?php echo $filter_max_zone; ?>" />
          <input type="hidden" name="sort" value="<?php echo $sort_attr; ?>" />
          <input type="hidden" name="order" value="<?php echo $sort_order; ?>" />
        </form>
      </div>
      <div class="insert-record">
        <a href="/admin" class='admin-btn'>Add Fig</a>
      </div>
    </section>
    <section class="catalog">
      <?php
      $records = exec_sql_query(
        $db,
        $sql_query,
        $sql_query_params
      )->fetchAll();
      if (count($records) > 0) { ?>
        <ul>
          <?php
          foreach ($records as $record) { ?>
            <li class="tile">
              <div class="tile-main">
                <h3><?php echo htmlspecialchars($record["name"]); ?></h3>
                <p><?php echo htmlspecialchars($record["description"]); ?></p>
              </div>
              <div class="tile-info">
                <h3><strong>$<?php echo htmlspecialchars($record["price"]); ?></strong></h3>
                <h3>Zone: <?php echo htmlspecialchars($record["min_zone"]); ?> -
                  <?php echo htmlspecialchars($record["max_zone"]); ?></h3>
              </div>
            </li>

          <?php } ?>
        </ul>
      <?php } else { ?>
        <p>No figs found.</p>
      <?php } ?>
    </section>
  </main>

  <?php include('includes/footer.php'); ?>

</body>

</html>
