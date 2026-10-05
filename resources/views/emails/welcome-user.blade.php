<x-mail::message>
# ¡Hola, {{ $name }}! 👋

Te damos la bienvenida a **{{ config('app.name') }}**. Tu cuenta ha sido creada exitosamente en el sistema.

<x-mail::panel>
**Detalles de tu cuenta:**
- **Correo electrónico:** {{ $email }}
@if($plainPassword)
- **Contraseña asignada:** `{!! $plainPassword !!}`
@endif
</x-mail::panel>

@if($plainPassword)
> **Recomendación de seguridad:** Te sugerimos cambiar tu contraseña inmediatamente después de iniciar sesión por primera vez.
> En la sección de mi perfil.
@endif

<x-mail::button :url="$loginUrl" color="primary">
Acceder a la plataforma
</x-mail::button>

Si tienes alguna pregunta o necesitas ayuda, no dudes en contactar al equipo de soporte.

Atentamente,<br>
El equipo de **{{ config('app.name') }}**
</x-mail::message>