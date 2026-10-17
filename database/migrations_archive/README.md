# Database Migrations Archive

This folder contains historical, intermediate SQL patches and one-time seed scripts from development Phases 1 through 14.

### 🌟 Active Master Database
The single master database dump containing all 20 tables and current production/seed records is located in:
`database/kravyo_complete_dump.sql`

To import the complete database on any machine:
- Run `import_db.bat` from the project root, or:
- Run: `Get-Content database\kravyo_complete_dump.sql | C:\xampp\mysql\bin\mysql.exe -u root kravyo_db`
