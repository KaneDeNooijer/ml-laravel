# LLMaaltijd ML proof of concept: Laravel-app

Proof of concept van Kane de Nooijer voor BP5 AI Food Prep (LLMaaltijd).

Dit is een uitgeklede versie van de LLMaaltijd-webapp, met de **integratie van een machine learning-component
in Laravel**. Na het scannen van een product toont de app de NOVA-groep (hoe sterk het bewerkt is, van 1 tot 4).
Ontbreekt die in Open Food Facts, dan schat de Python-service
[ml-python](https://github.com/KaneDeNooijer/ml-python) hem met machine learning. De app laat zien dat het om een
schatting gaat, hoe zeker het model is en op welke kenmerken het lette.

De analyse achter de keuzes staat in
[ml-python/docs/analyse.md](https://github.com/KaneDeNooijer/ml-python/blob/master/docs/analyse.md).

---

## Voor de docent: opzetten en testen

### 1. Wat je nodig hebt

- [Docker Desktop](https://www.docker.com/products/docker-desktop/), gestart.
- Git.
- Internet: productgegevens komen live uit Open Food Facts.

PHP, Composer, Node en MongoDB hoef je niet zelf te installeren; die draaien in containers.

### 2. Beide repositories ophalen

```sh
git clone https://github.com/KaneDeNooijer/ml-python.git
git clone https://github.com/KaneDeNooijer/ml-laravel.git
```

### 3. Eerst de Python-service starten

De app heeft de Python-service nodig. In een eerste terminal:

```sh
cd ml-python
docker compose up --build
```

Wacht tot je `Application startup complete` ziet en laat dit venster open. Zie de README van ml-python voor
details.

### 4. De Laravel-app starten

In een tweede terminal:

```sh
cd ml-laravel
docker compose up --build
```

De eerste keer duurt dit een paar minuten: Composer en pnpm installeren de afhankelijkheden, en de database
wordt aangemaakt. De app is klaar als de logs rustig worden en <http://localhost:5679> opent.

Dit start drie containers:
- `app`: Laravel, op poort 5679;
- `mongo`: de database;
- `vite`: de frontend, op poort 5174.

### 5. De ML-integratie testen in de browser

Inloggen is niet nodig. Open deze pagina's:

| Wat je test | Pagina | Wat je hoort te zien |
| --- | --- | --- |
| **Schatting door het model** | <http://localhost:5679/products/5400119506922> | "yoghurt drink aardbei", **Groep 4** met het gele label **"Geschat door ons model"**, op welke producten het model getraind is, hoe zeker het is, en de kenmerken waar het op lette |
| **Een tweede schatting** | <http://localhost:5679/products/8718907676588> | "Haver yoghurt ongezoet", een geschatte groep met redenen |
| **Bekende groep (geen ML nodig)** | <http://localhost:5679/products/5410146416866> | Groep 4 met "Deze groep komt uit Open Food Facts", zonder schattingsdetails |
| **Onbekend product** | <http://localhost:5679/products/00000000> | De melding dat het product niet in Open Food Facts staat |
| **Python-service uit** | Stop de Python-service (`Ctrl+C`) en ververs een productpagina | Een nette foutmelding in plaats van een crash |
| **Scannen** | <http://localhost:5679/scanner> | Na toestemming voor de camera: richt hem op een barcode. Na herkenning opent de productpagina vanzelf. |

Scannen werkt alleen op `localhost` of via HTTPS, omdat de browser anders de camera niet vrijgeeft. Test het dus
op de laptop met een webcam, of gebruik de directe links hierboven.

### 6. De automatische tests draaien

Terwijl de app draait, in een derde terminal in de map `ml-laravel`:

```sh
docker compose exec app php artisan test --compact
```

Er horen **10 tests** te slagen. Vijf daarvan testen de ML-integratie (`ProductControllerTest`):
- dat een geschatte groep met zekerheid en redenen wordt getoond;
- dat een gemeten groep zonder schattingsdetails wordt getoond;
- de melding bij een onbekend product;
- de melding als de Python-service faalt;
- dat een ongeldige barcode wordt geweigerd.

De tests bootsen de Python-service na, dus ze hebben Python en internet niet nodig. Ze gebruiken een aparte
testdatabase.

### 7. Stoppen

Druk `Ctrl+C` in beide terminalvensters, of in elke map:

```sh
docker compose down
```

---

## Waar zit de integratie?

| Bestand | Inhoud |
| --- | --- |
| `app/Services/PythonService.php` | De enige koppeling met de Python-service (Adapter): roept `GET /products/{barcode}` aan en vertaalt het antwoord |
| `app/Services/PythonServiceException.php` | Fouten van de Python-service, zodat de app er netjes op reageert |
| `app/Http/Controllers/ProductController.php` | Haalt het product op en kiest de juiste melding |
| `resources/views/products/show.blade.php` | Toont de NOVA-groep, het label "Geschat door ons model", de zekerheid en de redenen |
| `resources/js/scanner-page.ts` | Opent na een scan de productpagina |
| `config/services.php`, `compose.yaml` | Waar de Python-service draait (`PYTHON_API_URL`, standaard `http://host.docker.internal:8101`) |
| `tests/Feature/Http/Controllers/ProductControllerTest.php` | De tests van de integratie |

De rest van de app (maaltijden, navigatie, scanner) is overgenomen uit LLMaaltijd, zodat de integratie in een
echte context te zien is.

## Problemen oplossen

| Probleem | Oplossing |
| --- | --- |
| `port is already allocated` | Poort 5679 of 5174 is bezet. Start met andere poorten: `APP_PORT=5689 VITE_PORT=5184 docker compose up`. |
| "De productgegevens konden niet worden opgehaald" | De Python-service draait niet, of niet op poort 8101. Controleer <http://localhost:8101/health>. |
| De pagina heeft geen opmaak | De `vite`-container is nog bezig of gestopt. Wacht tot `docker compose up` klaar is, of start hem opnieuw. |
| Een product geeft na lang wachten een fout | Open Food Facts beperkt het aantal aanvragen per netwerk. Wacht een paar minuten. |
