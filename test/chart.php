<?php

  session_start();

  if (isset($_POST['prodotto'])) {
    
    if (isset($_SESSION['prodotti'])) {

      if (in_array($_POST['prodotto'], $_SESSION['prodotti'])) {

        $_SESSION['prodotti'][$_POST['prodotto']]++;

      } else {

        $_SESSION['prodotti'][$_POST['prodotto']] = 1;

      }      

    } else {
      $_SESSION['prodotti'] = array();
    }
  }

?>

<h2>Carrello della spesa</h2>

<em>Sessione nr: <?php echo session_id(); ?></em>

<p>Prodotti presenti nel Carrello</p>

<ul>
  <li>libro 1</li>
  <li>libro 2</li>
  <li>libro 3</li>
</ul>

<a href="products.php?PHPSESSID=<?php echo session_id(); ?>">Continua lo shopping</a>
<a href="products.php?close">Chiudi carrello</a>