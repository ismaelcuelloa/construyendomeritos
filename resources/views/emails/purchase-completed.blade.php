@component('mail::message')
<div style="text-align: center; margin-bottom: 30px;">
<img src="{{ url('assets/images/logo/logo-color.png') }}" alt="Construyendo Méritos con Excelencia" style="max-width: 200px; height: auto;">
</div>

# ¡Compra Exitosa!

Hola **{{ $order->user->name }}**,

Tu compra ha sido procesada exitosamente. ¡Gracias por confiar en nosotros!

---

<div style="display: flex; align-items: center; margin-bottom: 15px;">
<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#f07900" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 10px;"><path d="M16 2v4"/><path d="M8 2v4"/><rect width="18" height="18" x="3" y="4" rx="2"/><path d="M3 10h18"/></svg>
<span style="font-size: 18px; font-weight: bold; color: #f07900;">Detalles de tu Compra</span>
</div>

<table style="width: 100%; margin: 20px 0; border-collapse: collapse;">
<tr style="background-color: #f8f9fa;">
<td style="padding: 12px; border: 1px solid #dee2e6; font-weight: bold;">Número de Orden:</td>
<td style="padding: 12px; border: 1px solid #dee2e6;">{{ $order->number }}</td>
</tr>
<tr>
<td style="padding: 12px; border: 1px solid #dee2e6; font-weight: bold;">Fecha:</td>
<td style="padding: 12px; border: 1px solid #dee2e6;">{{ $order->paid_at->format('d/m/Y H:i') }}</td>
</tr>
<tr style="background-color: #f8f9fa;">
<td style="padding: 12px; border: 1px solid #dee2e6; font-weight: bold;">Total Pagado:</td>
<td style="padding: 12px; border: 1px solid #dee2e6; color: #f07900; font-weight: bold; font-size: 18px;">
${{ number_format($order->amount, 0, ',', '.') }} {{ $order->currency }}
</td>
</tr>
</table>

---

<div style="display: flex; align-items: center; margin: 20px 0 15px 0;">
<span style="font-size: 18px; font-weight: bold; color: #f07900;">Material de Estudio Adquirido</span>
</div>

@foreach($order->items as $item)
<div style="background-color: #fff8f3; padding: 15px; margin: 10px 0; border-left: 4px solid #f07900; border-radius: 4px;">
<div style="display: flex; align-items: center;">
<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#f07900" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 10px; flex-shrink: 0;"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H19a1 1 0 0 1 1 1v18a1 1 0 0 1-1 1H6.5a1 1 0 0 1 0-5H20"/></svg>
<div>
<strong style="color: #333; font-size: 16px;">{{ $item->description }}</strong><br>
<a href="{{ url('/cursos/'.$item->course->slug) }}" style="color: #f07900; text-decoration: none; font-size: 14px; font-weight: 500;">
Acceder al material de estudio
</a>
</div>
</div>
</div>
@endforeach

---

<div style="display: flex; align-items: center; margin: 20px 0 15px 0;">
<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#f07900" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 10px;"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
<span style="font-size: 18px; font-weight: bold; color: #f07900;">¿Cómo Acceder a tu Material de Estudio?</span>
</div>

<div style="background-color: #fff8f3; padding: 20px; border-radius: 8px; margin: 20px 0;">
<ol style="margin: 0; padding-left: 20px;">
<li style="margin-bottom: 10px;">Ingresa a <strong><a href="{{ url('/') }}" style="color: #f07900;">{{ config('app.name') }}</a></strong></li>
<li style="margin-bottom: 10px;">Haz clic en <strong>Iniciar Sesión</strong></li>
<li style="margin-bottom: 10px;">Usa tu correo: <strong>{{ $order->user->email }}</strong></li>
<li style="margin-bottom: 10px;">Usa la contraseña que creaste durante la compra</li>
</ol>
</div>

@component('mail::button', ['url' => url('/mis_cursos'), 'color' => 'primary'])
Ir a Mi Material de Estudio
@endcomponent

---

<div style="display: flex; align-items: center; margin: 20px 0 15px 0;">
<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#f07900" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 10px;"><path d="M3 7a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7z"/><path d="M3 7 12 13 21 7"/></svg>
<span style="font-size: 18px; font-weight: bold; color: #f07900;">¿Necesitas Ayuda?</span>
</div>

<div style="background-color: #fff8f3; padding: 20px; border-radius: 8px; margin: 20px 0; text-align: center;">
<p style="margin: 0 0 15px 0; font-size: 16px; color: #333;">
<strong>Si tienes inconvenientes con la plataforma o necesitas soporte:</strong>
</p>
<a href="https://wa.me/573236871881?text=Hola%2C%20necesito%20ayuda%20con%20mi%20compra%20{{ $order->number }}" 
   style="display: inline-block; background-color: #25D366; color: white; padding: 14px 35px; border-radius: 25px; text-decoration: none; font-weight: bold; font-size: 16px;">
   <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" width="20" height="20" fill="white" style="display: inline-block; vertical-align: middle; margin-right: 8px;"><path d="M476.9 161.1C435 119.1 379.2 96 319.9 96C197.5 96 97.9 195.6 97.9 318C97.9 357.1 108.1 395.3 127.5 429L96 544L213.7 513.1C246.1 530.8 282.6 540.1 319.8 540.1L319.9 540.1C442.2 540.1 544 440.5 544 318.1C544 258.8 518.8 203.1 476.9 161.1zM319.9 502.7C286.7 502.7 254.2 493.8 225.9 477L219.2 473L149.4 491.3L168 423.2L163.6 416.2C145.1 386.8 135.4 352.9 135.4 318C135.4 216.3 218.2 133.5 320 133.5C369.3 133.5 415.6 152.7 450.4 187.6C485.2 222.5 506.6 268.8 506.5 318.1C506.5 419.9 421.6 502.7 319.9 502.7zM421.1 364.5C415.6 361.7 388.3 348.3 383.2 346.5C378.1 344.6 374.4 343.7 370.7 349.3C367 354.9 356.4 367.3 353.1 371.1C349.9 374.8 346.6 375.3 341.1 372.5C308.5 356.2 287.1 343.4 265.6 306.5C259.9 296.7 271.3 297.4 281.9 276.2C283.7 272.5 282.8 269.3 281.4 266.5C280 263.7 268.9 236.4 264.3 225.3C259.8 214.5 255.2 216 251.8 215.8C248.6 215.6 244.9 215.6 241.2 215.6C237.5 215.6 231.5 217 226.4 222.5C221.3 228.1 207 241.5 207 268.8C207 296.1 226.9 322.5 229.6 326.2C232.4 329.9 268.7 385.9 324.4 410C359.6 425.2 373.4 426.5 391 423.9C401.7 422.3 423.8 410.5 428.4 397.5C433 384.5 433 373.4 431.6 371.1C430.3 368.6 426.6 367.2 421.1 364.5z"/></svg>
   Contáctanos por WhatsApp
</a>
<p style="margin: 15px 0 0 0; color: #666; font-size: 14px;">
Horario de atención: Lunes a Viernes 8:00 AM - 6:00 PM
</p>
</div>

---

<div style="display: flex; align-items: center; margin: 20px 0 15px 0;">
<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#f07900" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 10px;"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/></svg>
<span style="font-size: 18px; font-weight: bold; color: #f07900;">Términos y Condiciones</span>
</div>

<div style="background-color: #fff8f3; padding: 15px; border-radius: 8px; margin: 20px 0; font-size: 14px; color: #333; border: 2px solid #f8a145;">
<p style="margin: 0 0 10px 0;">
Al realizar esta compra has aceptado nuestros 
<a href="{{ url('/terminos-de-servicio') }}" style="color: #f07900; font-weight: bold;">Términos y Condiciones</a>.
</p>
<ul style="margin: 0; padding-left: 20px; list-style: none;">
<li style="margin-bottom: 8px;">
<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#f07900" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: inline-block; vertical-align: middle; margin-right: 8px;"><path d="M20 6 9 17l-5-5"/></svg>
El material <strong>no es descargable</strong> y se puede visualizar <strong>únicamente desde nuestra plataforma</strong>
</li>
<li style="margin-bottom: 8px;">
<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#f07900" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: inline-block; vertical-align: middle; margin-right: 8px;"><path d="M20 6 9 17l-5-5"/></svg>
Material de <strong>uso personal e intransferible</strong>
</li>
<li style="margin-bottom: 8px;">
<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#f07900" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: inline-block; vertical-align: middle; margin-right: 8px;"><path d="M20 6 9 17l-5-5"/></svg>
Contenido protegido por derechos de autor
</li>
<li style="margin-bottom: 8px;">
<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#f07900" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: inline-block; vertical-align: middle; margin-right: 8px;"><path d="M20 6 9 17l-5-5"/></svg>
Actualizaciones gratuitas del material
</li>
</ul>
</div>

---

<div style="text-align: center; margin-top: 30px;">
<img src="{{ url('assets/images/logo/logo-color.png') }}" alt="Construyendo Méritos con Excelencia" style="max-width: 150px; height: auto;">
</div>

Saludos,<br>
{{ config('app.name') }}
@endcomponent
