<?php 

session_start();

if (isset($_GET['close'])) {
  session_destroy();
  echo session_status();
  session_start();
} 

?>

<h2>Prodotti</h2>

<em>Sessione nr: <?php echo session_id(); ?> </em>

<br><br>

<form action="chart.php?PHPSESSID=<?php echo session_id(); ?>" method="post">

  <select name="prodotto">

    <?php 

    $prodotti = array("prodotto1", "prodotto2", "prodotto3", "prodotto4");

    foreach ($prodotti as $p) {
      echo "<option value=\"$p\">$p</option>\n";
    }

    ?>

  </select>

  <input type="submit" value="Metti nel carrello">

</form>