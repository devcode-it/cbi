# Libreria CBI

Libreria dedicata alla gestione dello standard CBI (Corporate Banking Interbancario), in particolare per la lettura e la generazione del formato relativo ai pagamenti RiBa.

La documentazione per la gestione del formato RiBa deriva dal seguente documento: <https://www.cracantu.it/files/STANDARD_CBI_AREA_INCASSI-RIBA-REL_5_01.pdf>.

Requisito minimo di PHP >= 7.1.

## Installazione

L'installazione della libreria è possibile tramite [Composer](https://getcomposer.org/):
```bash
php composer require itajackass/cbi
```

## Utilizzo

La libreria supporta la generazione e la lettura del formato CBI attraverso delle classi dedicate ai record dello standard.

### Generazione RiBa

La generazione del documento RiBa può essere gestita attraverso la struttura ausiliaria `DevCode\CBI\RiBa\RiBa`, che permette di definire una intestazione per il documento seguita da un numero qualunque di ricevute per il pagamento.

```php
<?php
require 'vendor/autoload.php';

use DevCode\CBI\RiBa\RiBa;
use DevCode\CBI\RiBa\Intestazione;
use DevCode\CBI\RiBa\Ricevuta;

// Impostazione dell'intestazione
$intestazione = new Intestazione();
...

// Generazione struttura di supporto
$riba = new RiBa($intestazione);

// Aggiunta delle ricevute relative
$ricevuta = new Ricevuta();
...
$riba->addRicevuta($ricevuta);

$cbi = $riba->asCBI();
```

In alternativa all'utilizzo di queste strutture semplificate, è possibile interagire direttamente con i record del formato utilizzando le classi disponibili in `DevCode\CBI\RiBa\Records`.
Un esempio pratico viene fornito per la lettura del formato CBI per i pagamenti RiBa.

### Lettura RiBa

```php
<?php
require 'vendor/autoload.php';

use DevCode\CBI\RiBa\Records\RecordIB;
use DevCode\CBI\RiBa\Intestazione;
use DevCode\CBI\RiBa\Ricevuta;

$contenuto = file_get_contents(__DIR__.'/example.cbi');
$righe = explode("\n", $contenuto);

// Lettura del primo record IB
$recordIB = new RecordIB();
$recordIB->fromCBI($righe[0]);

// Lettura dei record successivi
...
```

### Generazione RiBa dal software GAzie

La libreria rende inoltre disponibile un ulteriore metodo per la generazione del file CBI per pagamenti RiBa, derivato dal progetto [GAzie - Gestione Azienda](http://gazie.sourceforge.net).

```php
<?php
require 'vendor/autoload.php';

use DevCode\CBI\RiBa\RibaAbiCbi;

// Impostazione degli array come previsto dalla relativa documentazione interna
$intestazione = [];
$ricevute = [];

$riba = new RibaAbiCbi();
$cbi = $riba->creaFile($intestazione, $ricevute);
```

## Licenza

Questa libreria è tutelato dalla licenza [**GPL 3**](https://github.com/devcode-it/cbi/blob/master/LICENSE).
