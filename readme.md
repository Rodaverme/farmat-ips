# FARTMAR IPS - Sistema de Dispensación, Inventarios y Traslados

Sistema integral desarrollado para la gestión farmacéutica de FARTMAR IPS, garantizando la trazabilidad en kardex, cumplimiento estricto de reglas de lotes (FEFO) y un asistente inteligente seguro con Function Calling.

## Stack Tecnológico
- **Backend**: PHP 8.4 / Laravel, API REST, Eloquent ORM, PostgreSQL 16.
- **Frontend**: React, TypeScript, Vite.
- **DevOps**: Docker, Docker Compose, Nginx, PHP-FPM.
- **Calidad**: Laravel Pint, Larastan, PHPUnit / Vitest.

---

## Ejecución Rápida (Un Solo Comando)

Asegúrate de tener Docker y Docker Compose instalados. Copia el archivo `.env.example` a `.env` en la carpeta del backend y ejecuta en la raíz del proyecto:


## Comando para  ejecutar la prueba de la Inteligencia Artificial 
 
```bash
php artisan assistant:evaluate

## DOCUMENTACION DEL LAS API POSTMAN

```bash
https://bitsolar-co.postman.co/workspace/My-Workspace~622d6fe6-7d8d-4b68-8f46-61827fb1d060/request/41860323-dfae6de7-15ab-4e9a-a5c4-8df1ec4b1fd1?action=share&creator=41860323


```bash
docker compose up --build -d