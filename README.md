# Климатици ЕООД — Уеб приложение

Дипломен проект за уеб базирана система за онлайн търговия с климатици.

---

## Технологии

- **Frontend:** HTML5, CSS3, JavaScript
- **Backend:** PHP 8.0+
- **База данни:** MySQL 8.0
- **Сървър:** Apache (XAMPP)

---

## Изисквания

- XAMPP 8.0+
- MySQL Workbench 8.0
- Visual Studio Code (препоръчително)
- Google Chrome / Firefox

---

## Инсталация

1. Копирайте папката `Diplomna` в `C:\xampp\htdocs\`
2. Стартирайте **Apache** и **MySQL** от XAMPP Control Panel
3. Импортирайте базата данни в MySQL Workbench от `docs/klimatici_db.sql`
4. Отворете `api/config/database.php` и задайте вашата MySQL парола:
   ```php
   private $password = ""; // вашата парола
   ```
5. Отворете в браузър: `http://localhost/Diplomna/index.html`

---

## Структура на проекта

```
Diplomna/
├── api/                    # PHP backend
│   ├── auth/               # Логване и регистрация
│   ├── brands/             # Марки
│   ├── chat/               # Чат система
│   ├── config/             # Конфигурация на базата данни
│   ├── dashboard/          # Статистики за admin
│   ├── orders/             # Поръчки
│   ├── products/           # Продукти
│   ├── reviews/            # Отзиви
│   ├── service_requests/   # Сервизни заявки
│   ├── services/           # Услуги
│   └── users/              # Потребители
├── assets/
│   ├── css/                # Стилове
│   ├── js/                 # JavaScript файлове
│   └── images/             # Снимки
├── docs/                   # Документация и база данни
├── index.html              # Начална страница
├── products.html           # Каталог с продукти
├── product-detail.html     # Детайли за продукт
├── cart.html               # Количка
├── checkout.html           # Поръчка
├── login.html              # Вход / Регистрация
├── user-profile.html       # Потребителски профил
├── repair.html             # Заявка за ремонт
├── admin.html              # Административен панел
└── technician-dashboard.html  # Technician Panel
```

---

## Потребителски роли

| Роля | Достъп |
|------|--------|
| `client` | Каталог, количка, поръчки, профил, ремонт |
| `employee` | Technician Panel, продукти, сервизни заявки |
| `admin` | Admin панел, всички функционалности |

---

## Автор
Светослав Панов 12.а СПГЕ "Джон Атанасов"