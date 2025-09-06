# UPUTSTVO ZA POKRETANJE

**Preduslov:** MySQL iz XAMPP-a mora biti pokrenut
(XAMPP Control Panel > Start **MySQL**)

---

## 1) Pokrenuti PHP server

U Windows CMD (ili PowerShell) pokreni:

```bat
"C:\xampp\php\php.exe" -S localhost:8000 -t "C:\PUNA_LOKACIJA\Aplikacija_Za_Pracenje_Licnih_Finansija\public"
```

> Zameniti `C:\PUNA_LOKACIJA\Aplikacija_Za_Pracenje_Licnih_Finansija\public` punom putanjom do projekta (`public` podfolder u folderu `Aplikacija_Za_Pracenje_Licnih_Finansija`)

---

## 2) Pripremi bazu (1 klik)

Otvori u pregledaču:

```
http://localhost:8000/install.php?reset=1
```

Ovo briše i kreira bazu **finance\_tracker** i ubacuje demo podatke.

---

## 3) Prijava (demo nalog ili registracija)

* Email: `marko@example.com`
* Lozinka: `test123`
