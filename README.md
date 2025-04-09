
# TS Users API

Prosta aplikacja REST API w Laravel 12 realizująca operacje CRUD na użytkownikach oraz obsługę wielu adresów e-mail na użytkownika. Zadanie rekrutacyjne.

---

## 📦 Wymagania funkcjonalne

- Użytkownik ma:
  - imię, nazwisko, numer telefonu
  - wiele adresów e-mail
- API umożliwia:
  - tworzenie, aktualizację, pobieranie i usuwanie użytkowników
  - przypisywanie wielu adresów e-mail do użytkownika
- Po utworzeniu użytkownika wysyłany jest powitalny mail na każdy przypisany adres (poprzez event + kolejkę)

---

## ✅ Przyjęte założenia

- **Laravel 12**, REST API (`routes/api.php`)
- JSON jako format komunikacji
- W ramach projektu abstrahuje się od tego, do czego użytkownicy (User) mają być wykorzystywani – nie są definiowane żadne role, typy użytkowników, logowanie.
- Aplikacja nie implementuje uwierzytelniania (auth)
- Unikalny musi być tylko email globalnie; Dla użytkowników imię i nazwisko oraz w szczególnośc numer telefonu nie muszą być unikalne.
- Wysyłka maili obsługiwana przez **event `UserCreated`** i **listener `SendWelcomeEmails`**, asynchronicznie przez kolejkę
- `User` korzysta z **soft deletes**
- E-maile:
  - nie są usuwane przy `delete` użytkownika
  - **nie mogą zostać ponownie użyte** (nawet po soft-delecie)
  - są usuwane **wyłącznie podczas `update`**, jeśli znikną z listy maili użytkownika
- Prosty **throttling 100 żądań/minutę/IP**

---

## 🧪 Przykładowe `curl`e

### 🔹 Utwórz użytkownika

```bash
curl -X POST http://127.0.0.1:8000/api/users \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "first_name": "Janusz",
    "last_name": "Kowalski",
    "phone": "123456789",
    "emails": ["janusz@example.com", "kowalski@example.com"]
}'
```

---

### 🔹 Pobierz listę użytkowników

```bash
curl -X GET http://127.0.0.1:8000/api/users \
  -H "Accept: application/json"
```

---

### 🔹 Pobierz jednego użytkownika

```bash
curl -X GET http://127.0.0.1:8000/api/users/1 \
  -H "Accept: application/json"
```

---

### 🔹 Zaktualizuj użytkownika i e-maile

```bash
curl -X PUT http://127.0.0.1:8000/api/users/1 \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "first_name": "Janusz",
    "last_name": "Kowalski",
    "phone": "987654321",
    "emails": ["kowalski@example.com", "nowy@example.com"]
}'
```

---

### 🔹 Usuń użytkownika

```bash
curl -X DELETE http://127.0.0.1:8000/api/users/1 \
  -H "Accept: application/json"
```

---

## 🧩 Struktura kodu

- `User` model: softDeletes, relacja `hasMany(Email)`
- `Email` model: należy do `User`, pole `email` musi być unikalne
- `UserController`:
  - `store`, `update` obudowane transakcją
  - aktualizacja e-maili działa na zasadzie `diff` (dodaje nowe, usuwa brakujące)
- `UserCreated` event + `SendWelcomeEmails` listener wysyła maile (kolejka)

---

## 🚀 Uruchomienie projektu lokalnie

1. Zainstaluj zależności:

```bash
composer install
```

2. Skonfiguruj `.env` i bazę danych

3. Uruchom migracje:

```bash
php artisan migrate
```

4. Uruchom kolejkę:

```bash
php artisan queue:work
```

5. Odpal serwer:

```bash
php artisan serve
```

---

## 🧪 Testy

```bash
php artisan test
```

---
