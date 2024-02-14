<?php // upload dei file

// php può ricevere i file in formato testuale o binario
if (isset($_FILES['mio'])) {

  echo "Nome: " . $_FILES['mio']['name'];
  echo "<br>Tipo: " . $_FILES['mio']['type'];
  echo "<br>Dimensione: " . $_FILES['mio']['size'];
  echo "<br>Nome temporaneo: " . $_FILES['mio']['tmp_name'];
  echo "<br>Errore: " . $_FILES['mio']['error']; 
  /* 0 => il file è stato caricato
  correttamente in memoria
  1 => la dimensione del file
  eccede quanto stabilito nel php.ini
  2 => la dimensione del file
  eccede quanto indicato nel form
  3 => il file è stato caricato
  parzialmente */

  if ($_FILES['mio']['error'] == 0) { // caricamento avvenuto correttamente

    // controllo che il file abbia un'estensione consentita
    $nomi = explode('.', $_FILES['mio']['name']);
    $estensione = $nomi[count($nomi) - 1];

    echo "<br>$estensione";

    $ammesse = array("jpg", "gif", "txt");

    if (in_array($estensione, $ammesse)) {
      // possiamo procedere a salvare il file permanentemente su server

      $tmp_name = $_FILES['mio']['tmp_name'];
      $dir = "upload/" . $_FILES['mio']['name'];

      if (move_uploaded_file($tmp_name, $dir)) {
        // se tutto okay la funzione ritorna true
        echo "<br>File caricato correttamente<br>";
        print_r($_FILES);
      }

    } else {

      echo "<br>Estensione del file non ammessa";
    }

    
  }

} else {

  ?>

  <!-- form di inserimento -->

  <form enctype="multipart/form-data" action="file_upload.php" method="post">
    <input type="hidden" name="MAX_FILE_SIZE" value="100000">
    File da caricare:
    <input type="file" name="mio" size="40">
    
    <input type="reset" value="Annulla">
    &nbsp;
    <input type="submit" value="Carica">
  </form>

  <?php 

}


