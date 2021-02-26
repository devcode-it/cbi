<?php

declare(strict_types=1);

use Carbon\Carbon;
use DevCode\CBI\RiBa\Intestazione;
use DevCode\CBI\RiBa\RiBa;
use DevCode\CBI\RiBa\Ricevuta;
use PHPUnit\Framework\TestCase;

final class ValidityTest extends TestCase
{
    protected $intestazione = [
        'nome_supporto' => 'Test di creazione',
        'data_creazione' => '2021-03-01',

        'creditore' => [
            'ragione_sociale' => 'Creditore',
            'partita_iva' => '123456789',
            'codice_fiscale' => '',
            'cap' => '00100',
            'citta' => 'Roma',
            'provincia' => 'RM',
            'indirizzo' => 'Via Roma',

            'banca' => [
                'codice_sia' => '12345',
                'conto' => 'IT60X0542811101000000123456',
                'abi' => '12345',
                'cab' => '12345',
            ],
        ],
    ];

    protected $ricevute = [
        [
            'numero' => 1,
            'data_scadenza' => '2021-04-01',
            'descrizione' => 'Importo di 100 euro',
            'importo' => '100',

            'debitore' => [
                'codice' => '00001',

                'ragione_sociale' => 'Debitore',
                'partita_iva' => '123456789',
                'codice_fiscale' => '',
                'cap' => '00100',
                'citta' => 'Roma',
                'provincia' => 'RM',
                'indirizzo' => 'Via Roma',

                'banca' => [
                    'descrizione' => 'Banca di test',
                    'abi' => '12345',
                    'cab' => '12345',
                ],
            ],
        ],
    ];

    public function testFormatoCorretto(): void
    {
        $intestazione = $this->creaIntestazione($this->intestazione);
        $riba = $this->creaRiBa($intestazione);

        // Generazione del gestore interno
        foreach ($this->ricevute as $ricevuta) {
            $ricevuta = $this->creaRicevuta($ricevuta);
            $riba->addRicevuta($ricevuta);
        }

        $contenuto = file_get_contents(__DIR__.DIRECTORY_SEPARATOR.'ricevuta.txt');

        $this->assertEquals(
            $contenuto,
            $riba->asCBI()
        );
    }

    public function testFormatoGAzieCorretto(): void
    {
        $intestazione = $this->creaIntestazione($this->intestazione);
        $riba = $this->creaRiBa($intestazione);

        // Generazione del gestore interno
        foreach ($this->ricevute as $ricevuta) {
            $ricevuta = $this->creaRicevuta($ricevuta);
            $riba->addRicevuta($ricevuta);
        }

        $contenuto = file_get_contents(__DIR__.DIRECTORY_SEPARATOR.'ricevuta_gazie.txt');

        $this->assertEquals(
            $contenuto,
            $riba->asRibaAbiCbi()
        );
    }

    public function testCreazioneIntestazione(): void
    {
        $intestazione = $this->creaIntestazione($this->intestazione);

        $this->assertInstanceOf(
            Intestazione::class,
            $intestazione
        );
    }

    public function testCreazioneRiBa(): void
    {
        $intestazione = $this->creaIntestazione($this->intestazione);
        $riba = $this->creaRiBa($intestazione);

        $this->assertInstanceOf(
            RiBa::class,
            $riba
        );
    }

    public function testCreazioneRicevuta(): void
    {
        $ricevuta = $this->creaRicevuta($this->ricevute[0]);

        $this->assertInstanceOf(
            Ricevuta::class,
            $ricevuta
        );
    }

    protected function creaRicevuta($dati = [])
    {
        $debitore = $dati['debitore'];
        $banca = $debitore['banca'];

        // Salvataggio della singola ricevuta nel RiBa
        $ricevuta = new Ricevuta();
        $ricevuta->numero_ricevuta = $dati['numero'];
        $ricevuta->scadenza = (new Carbon($dati['data_scadenza']))->format('dmy');
        $ricevuta->importo = $dati['importo'];
        $ricevuta->descrizione = strtoupper($dati['descrizione']);

        // Informazioni sulla banca
        $ricevuta->abi_banca = $banca['abi'];
        $ricevuta->cab_banca = $banca['cab'];
        $ricevuta->descrizione_banca = $banca['descrizione'];

        // Informazioni sul debitore
        $ricevuta->codice_cliente = $debitore['codice'];
        $ricevuta->nome_debitore = strtoupper($debitore['ragione_sociale']);
        $ricevuta->identificativo_debitore = !empty($debitore['partita_iva']) ? $debitore['partita_iva'] : $debitore['codice_fiscale'];
        $ricevuta->indirizzo_debitore = strtoupper($debitore['indirizzo']);
        $ricevuta->cap_debitore = $debitore['cap'];
        $ricevuta->comune_debitore = strtoupper($debitore['citta']);
        $ricevuta->provincia_debitore = $debitore['provincia'];

        return $ricevuta;
    }

    protected function creaRiBa(Intestazione $intestazione)
    {
        return new RiBa($intestazione);
    }

    protected function creaIntestazione($dati = [])
    {
        $creditore = $dati['creditore'];
        $banca = $creditore['banca'];

        // Generazione intestazione
        $intestazione = new Intestazione();
        $intestazione->data_creazione = (new Carbon($dati['data_creazione']))->format('dmy');
        $intestazione->nome_supporto = $dati['nome_supporto'];

        // Informazioni sulla banca
        $intestazione->codice_sia = $banca['codice_sia'];
        $intestazione->conto = $banca['conto'];
        $intestazione->abi = $banca['abi'];
        $intestazione->cab = $banca['cab'];

        // Informazioni sul creditore
        $intestazione->cap_citta_prov_creditore = strtoupper($creditore['cap'].' '.$creditore['citta'].' '.$creditore['provincia']);
        $intestazione->ragione_soc1_creditore = strtoupper($creditore['ragione_sociale']);
        $intestazione->indirizzo_creditore = strtoupper($creditore['indirizzo']);
        $intestazione->identificativo_creditore = !empty($creditore['partita_iva']) ? $creditore['partita_iva'] : $creditore['codice_fiscale'];

        return $intestazione;
    }
}
