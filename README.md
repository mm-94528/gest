# CRM/ERP Aziendale

Sistema di gestione aziendale modulare sviluppato in PHP senza framework.

## 🚀 Caratteristiche

- **Architettura Modulare**: Ogni funzionalità è un modulo indipendente
- **Core Leggero**: Funzionalità essenziali senza bloat
- **Database Flessibile**: Query builder e ORM semplificato
- **Sicurezza**: Autenticazione e sistema di permessi
- **Responsive**: Interfaccia moderna con Bootstrap 5

## 📦 Moduli Disponibili

- **Clienti**: Gestione anagrafica clienti e contatti
- **Offerte**: Creazione e gestione offerte commerciali
- **Ordini**: Gestione ordini clienti e fornitori
- **Progetti**: Pianificazione e controllo progetti
- **Magazzino**: Controllo giacenze e movimenti

## 🛠️ Installazione

### Requisiti
- PHP >= 7.4
- MySQL >= 5.7
- Apache/Nginx con mod_rewrite

### Setup

1. **Clona il repository**
```bash
git clone <repository-url>
cd crm-erp
```

2. **Configura l'ambiente**
```bash
cp .env.example .env
# Modifica .env con i tuoi parametri
```

3. **Installa dipendenze**
```bash
composer install
```

4. **Configura il database**
```bash
# Crea il database
mysql -u root -p -e "CREATE DATABASE crm_erp CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Esegui le migrazioni
php cli/migrate.php
```

5. **Crea un utente admin**
```bash
php cli/create-user.php
```

6. **Configura il web server**

**Apache (.htaccess già incluso)**
```apache
<VirtualHost *:80>
    DocumentRoot /path/to/crm-erp/public
    ServerName crm-erp.local
    
    <Directory /path/to/crm-erp/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

**Nginx**
```nginx
server {
    listen 80;
    server_name crm-erp.local;
    root /path/to/crm-erp/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

7. **Avvia il server di sviluppo** (opzionale)
```bash
composer serve
# oppure
php -S localhost:8000 -t public
```

## 🏗️ Struttura del Progetto

```
crm-erp/
├── app/
│   ├── Core/                 # Core del sistema
│   ├── Models/              # Modelli base
│   ├── Controllers/         # Controller base
│   └── Modules/             # Moduli CRM/ERP
├── config/                  # Configurazioni
├── public/                  # File pubblici
├── resources/               # Viste e assets
├── storage/                 # File e cache
└── cli/                     # Script CLI
```

## 🎯 Utilizzo

### Accesso al Sistema
1. Apri il browser su `http://localhost:8000` (o il tuo dominio)
2. Accedi con le credenziali create
3. Naviga tra i moduli dalla sidebar

### Gestione Clienti
```php
// Esempio creazione cliente
$cliente = Cliente::create([
    'ragione_sociale' => 'Azienda SpA',
    'partita_iva' => '12345678901',
    'email' => 'info@azienda.it'
]);
```

### Gestione Offerte
```php
// Esempio creazione offerta
$offerta = Offerta::create([
    'cliente_id' => $cliente->id,
    'numero' => 'OFF-2024-001',
    'data_offerta' => date('Y-m-d'),
    'stato' => 'bozza'
]);
```

## 🔧 Sviluppo

### Aggiungere un Nuovo Modulo

1. **Crea la struttura del modulo**
```bash
mkdir -p app/Modules/NuovoModulo/{Models,Controllers,Views}
```

2. **Aggiungi il modulo alla configurazione**
```php
// config/modules.php
'nuovo_modulo' => [
    'name' => 'Nuovo Modulo',
    'version' => '1.0.0',
    'enabled' => true,
    'routes_file' => 'app/Modules/NuovoModulo/routes.php'
]
```

3. **Crea il modello**
```php
// app/Modules/NuovoModulo/Models/NuovoModulo.php
class NuovoModulo extends BaseModel {
    protected $table = 'nuovo_modulo';
    protected $fillable = ['campo1', 'campo2'];
}
```

4. **Crea il controller**
```php
// app/Modules/NuovoModulo/Controllers/NuovoModuloController.php
class NuovoModuloController extends BaseController {
    public function index() {
        $this->requirePermission('nuovo_modulo.view');
        // Logica del controller
    }
}
```

5. **Definisci le route**
```php
// app/Modules/NuovoModulo/routes.php
return [
    'GET /nuovo-modulo' => 'NuovoModuloController@index',
    'POST /nuovo-modulo' => 'NuovoModuloController@store'
];
```

### Database e Migrazioni

**Creare una nuova migrazione**
```sql
-- resources/migrations/015_create_nuova_tabella.sql
CREATE TABLE nuova_tabella (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

**Eseguire le migrazioni**
```bash
php cli/migrate.php
```

### Sistema di Permessi

```php
// Nel controller
$this->requirePermission('modulo.azione');

// Nel template
<?php if ($auth->hasPermission('modulo.edit')): ?>
    <button>Modifica</button>
<?php endif; ?>
```

## 🎨 Personalizzazione UI

### CSS Personalizzato
Modifica `public/assets/css/app.css` per personalizzare l'aspetto.

### JavaScript Personalizzato
Estendi `public/assets/js/app.js` per aggiungere funzionalità.

### Template
I template si trovano in `resources/views/` e utilizzano PHP nativo.

## 📊 API REST

Il sistema supporta risposte JSON per chiamate AJAX:

```php
// Nel controller
public function apiIndex() {
    $data = ModelloEsempio::all();
    return $this->json(['data' => $data]);
}
```

```javascript
// Nel frontend
AppUtils.ajax('/api/endpoint')
    .then(data => {
        console.log(data);
    });
```

## 🔒 Sicurezza

### Autenticazione
- Sistema di login con hash password sicuro
- Gestione sessioni con timeout
- Protezione CSRF (da implementare)

### Autorizzazione
- Sistema di ruoli e permessi granulare
- Middleware per proteggere le route
- Controllo accessi a livello di modulo

### Validazione
```php
$this->validate([
    'email' => 'required|email|unique:users,email',
    'password' => 'required|min:8|confirmed'
]);
```

## 📈 Performance

### Query Optimization
- Indici del database ottimizzati
- Query builder efficiente
- Lazy loading delle relazioni

### Caching
- Sistema di cache file-based
- Cache delle viste (opzionale)
- Ottimizzazione assets

### Monitoraggio
- Log degli errori in `storage/logs/`
- Metriche di performance (da implementare)

## 🧪 Testing

```bash
# Esegui i test (quando implementati)
vendor/bin/phpunit
```

## 📚 Documentazione API

### Endpoints Principali

```
GET    /clienti              # Lista clienti
POST   /clienti              # Crea cliente
GET    /clienti/{id}         # Dettagli cliente
PUT    /clienti/{id}         # Aggiorna cliente
DELETE /clienti/{id}         # Elimina cliente

GET    /offerte              # Lista offerte
POST   /offerte              # Crea offerta
GET    /offerte/{id}         # Dettagli offerta
PUT    /offerte/{id}         # Aggiorna offerta

GET    /ordini               # Lista ordini
POST   /ordini               # Crea ordine
GET    /ordini/{id}          # Dettagli ordine

GET    /progetti             # Lista progetti
POST   /progetti             # Crea progetto
GET    /progetti/{id}        # Dettagli progetto
```

## 🤝 Contributi

1. Fork il progetto
2. Crea un branch per la feature (`git checkout -b feature/nuova-feature`)
3. Commit le modifiche (`git commit -am 'Aggiunge nuova feature'`)
4. Push al branch (`git push origin feature/nuova-feature`)
5. Apri una Pull Request

## 📄 Licenza

Questo progetto è rilasciato sotto licenza MIT. Vedi il file `LICENSE` per i dettagli.

## 🆘 Supporto

Per supporto e segnalazioni bug:
- Apri una issue su GitHub
- Contatta il team di sviluppo

## 🗺️ Roadmap

### v1.1 (Prossima versione)
- [ ] Modulo Fatturazione
- [ ] Sistema di Notifiche
- [ ] Dashboard avanzata con grafici
- [ ] Esportazione PDF/Excel
- [ ] API REST completa

### v1.2 (Futura)
- [ ] Integrazione e-commerce
- [ ] Modulo HR/Presenze
- [ ] Sistema di Workflow
- [ ] Mobile App
- [ ] Integrazione contabilità

### v2.0 (Long term)
- [ ] Microservizi
- [ ] Sistema multi-tenant
- [ ] AI/ML per analytics
- [ ] Integrazione IoT
- [ ] Cloud native deployment

---

**Sviluppato con ❤️ per la gestione aziendale moderna**