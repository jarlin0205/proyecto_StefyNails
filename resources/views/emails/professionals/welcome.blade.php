<x-mail::message>
# ¡Hola, {{ $user->name }}!

¡Bienvenida al equipo de **Stefy Nails**! Estamos muy felices de tenerte con nosotros.

Se ha creado tu cuenta de acceso para que puedas gestionar tu agenda y citas desde nuestro panel administrativo.

### Tus credenciales de acceso:

**URL de Acceso:** [{{ route('login') }}]({{ route('login') }})
**Usuario:** `{{ $user->email }}`
**Contraseña:** `{{ $password }}`

<x-mail::button :url="route('login')">
Entrar al Panel
</x-mail::button>

*Por seguridad, te recomendamos cambiar tu contraseña una vez hayas ingresado al sistema.*

Gracias,<br>
El equipo de {{ config('app.name') }}
</x-mail::message>
