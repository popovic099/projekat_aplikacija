# UPUTSTVO ZA POKRETANJE

**Preduslov:** MySQL iz XAMPP-a mora biti pokrenut
(XAMPP Control Panel > Start **MySQL**)

---

## 1) Pokreni PHP server

U Windows CMD (ili PowerShell) pokreni:

```bat
"C:\xampp\php\php.exe" -S localhost:8000 -t "C:\PUNA_LOKACIJA\Aplikacija_Za_Pracenje_Licnih_Finansija\public"
```

> Zameni `C:\PUNA_LOKACIJA\Aplikacija_Za_Pracenje_Licnih_Finansija\public` stvarnom putanjom do projekta.

---

## 2) Inicijalizuj bazu (1 klik)

Otvori u pregledaču:

```
http://localhost:8000/install.php?reset=1
```

Ovo briše/kreira bazu **finance\_tracker** i ubacuje demo podatke.

---

## 3) Prijava (demo nalog ili registracija)

* Email: `marko@example.com`
* Lozinka: `test123`

Možete se registrovati novim nalogom preko stranice za registraciju.
