---
description: Cómo reiniciar el sistema correctamente después de un rollback de migraciones
---

# Reinicio del sistema después de un Rollback de migraciones

Cuando se ejecuta `php artisan migrate:rollback` (o `migrate:fresh`, `migrate:reset`), las tablas de la base de datos se eliminan o revierten. Esto incluye las tablas de **Spatie Permission** (`roles`, `permissions`, `model_has_roles`, `model_has_permissions`, `role_has_permissions`) que usa **Filament Shield** para gestionar roles y permisos.

> **Problema raíz:** Shield almacena sus permisos y asignaciones en la base de datos. Un rollback elimina esos registros, dejando la sección de Roles y Permisos vacía o inaccesible.

## Pasos obligatorios después de un rollback

### 1. Re-ejecutar las migraciones

```bash
php artisan migrate
```

Esto recrea todas las tablas, incluyendo las de permisos.

### 2. Ejecutar los seeders base

```bash
php artisan db:seed
```

Esto ejecuta `DatabaseSeeder` que llama a:
- `RoleSeeder` → crea los roles `registrado` y `super_admin`
- `UserSeeder` → crea el usuario admin y usuarios de prueba

Si solo necesitas los roles (sin usuarios):

```bash
php artisan db:seed --class=RoleSeeder
```

### 3. Regenerar permisos y políticas de Shield

```bash
php artisan shield:generate --all --panel=dashboard
```

Cuando pregunte "Would you like to select what to generate?", responde **no** para generar todo (permisos + políticas).

Este comando:
- Escanea todos los recursos, páginas y widgets del panel `dashboard`
- Crea los registros de permisos en la tabla `permissions`
- Genera las políticas PHP (archivos en `app/Policies/`)

### 4. Reasignar el super admin

```bash
php artisan shield:super-admin
```

- Selecciona el panel `dashboard` (opción `0`)
- Ingresa el `UserID` del administrador (normalmente `1`)

Esto asigna **todos** los permisos al rol `super_admin`.

### 5. Limpiar todas las cachés

```bash
php artisan optimize:clear
```

Esto limpia cachés de configuración, rutas, vistas y el caché de permisos de Spatie.

## Comando rápido (todo en uno)

Si hiciste `migrate:fresh` (borra todo y recrea):

// turbo-all
```bash
php artisan migrate:fresh
php artisan db:seed
php artisan shield:generate --all --panel=dashboard
php artisan shield:super-admin
php artisan optimize:clear
```

> **Nota:** `shield:generate` y `shield:super-admin` son interactivos, así que deberás responder a las preguntas que hagan.

## ¿Por qué sucede esto?

| Qué hace el rollback | Consecuencia |
|---|---|
| Elimina tabla `permissions` | Shield no encuentra permisos → no muestra la sección de Roles |
| Elimina tabla `roles` | Los usuarios pierden sus roles asignados |
| Elimina tabla `role_has_permissions` | Los roles pierden sus permisos |
| Elimina tabla `model_has_roles` | Los usuarios no tienen acceso a ningún recurso |

Shield depende de que estas tablas existan **y tengan datos**. La migración solo crea las tablas vacías; los datos los generan los seeders y los comandos `shield:generate` / `shield:super-admin`.
