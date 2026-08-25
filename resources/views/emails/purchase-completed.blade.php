@component('mail::message')

{{-- ===== AVISO LEGAL (primero) ===== --}}
<div style="background-color: #fef9d6; border: 1px solid #f5e42c; border-left: 4px solid #f5e42c; border-radius: 8px; padding: 12px 16px; margin-bottom: 18px;">
<p style="margin:0; font-size: 11px; color: #133a54; line-height: 1.6;">
<strong>Aviso Legal:</strong> Este correo proviene de <strong>meritoconstruyendoexcelencia.com</strong>, plataforma privada e independiente. No tenemos vínculo con la Procuraduría General de la Nación. El contenido adquirido es <strong>material de preparación independiente</strong> y no incluye trámites ni inscripciones.
</p>
</div>

{{-- ===== BANNER BIENVENIDA ===== --}}
<div style="background: linear-gradient(135deg, #133a54 0%, #1a5a80 100%); border-radius: 12px; padding: 24px 28px; margin-bottom: 24px; text-align: center;">
<div style="font-size: 22px; font-weight: 900; color: #ffffff; letter-spacing: 0.5px;">¡Compra Exitosa! &#127881;</div>
<div style="font-size: 14px; color: #f5e42c; margin-top: 6px; font-weight: 600;">{{ config('app.name') }}</div>
</div>

{{-- ===== SALUDO ===== --}}
<p style="font-size: 16px; color: #333; margin: 0 0 16px;">
Hola <strong style="color: #133a54;">{{ $order->user->name }}</strong>,
</p>
<p style="font-size: 14px; color: #555; margin: 0 0 24px;">
Tu compra ha sido procesada exitosamente. ¡Gracias por confiar en nosotros!
</p>

{{-- ===== DETALLES ===== --}}
<div style="background-color: #ffffff; border: 1px solid #e2e8f0; border-left: 4px solid #133a54; border-radius: 10px; padding: 20px 24px; margin: 0 0 24px;">
<div style="font-size: 15px; font-weight: 800; color: #133a54; margin-bottom: 14px; text-transform: uppercase; letter-spacing: 0.5px;">Detalles de tu Compra</div>
<table style="width: 100%; border-collapse: collapse;">
<tr>
<td style="padding: 8px 0; font-size: 13px; color: #64748b;">Número de Orden</td>
<td style="padding: 8px 0; font-size: 13px; color: #133a54; font-weight: 700; text-align: right;">{{ $order->number }}</td>
</tr>
<tr style="background-color: #f8fafc;">
<td style="padding: 8px 0; font-size: 13px; color: #64748b;">Fecha</td>
<td style="padding: 8px 0; font-size: 13px; color: #133a54; font-weight: 600; text-align: right;">{{ $order->paid_at->format('d/m/Y H:i') }}</td>
</tr>
<tr>
<td style="padding: 8px 0; font-size: 13px; color: #64748b;">Total Pagado</td>
<td style="padding: 8px 0; font-size: 18px; color: #133a54; font-weight: 900; text-align: right;">${{ number_format($order->amount, 0, ',', '.') }} {{ $order->currency }}</td>
</tr>
</table>
</div>

{{-- ===== MATERIAL ADQUIRIDO ===== --}}
<div style="font-size: 15px; font-weight: 800; color: #133a54; margin: 0 0 14px; text-transform: uppercase; letter-spacing: 0.5px;">Material de Estudio Adquirido</div>

@foreach($order->items as $item)
<div style="background-color: #fef9d6; border: 1px solid #f5e42c; border-left: 4px solid #133a54; border-radius: 8px; padding: 16px 18px; margin: 0 0 10px;">
<table style="width: 100%; border-collapse: collapse;">
<tr>
<td style="vertical-align: top; width: 34px;">
<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#133a54" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H19a1 1 0 0 1 1 1v18a1 1 0 0 1-1 1H6.5a1 1 0 0 1 0-5H20"/></svg>
</td>
<td style="vertical-align: middle;">
<div style="font-size: 15px; font-weight: 700; color: #133a54;">{{ $item->description }}</div>
<a href="{{ url('/cursos/'.$item->course->slug) }}" style="display: inline-block; margin-top: 8px; font-size: 12px; font-weight: 700; color: #b45309; text-decoration: none; border-bottom: 1px dashed #b45309; padding-bottom: 1px;">Acceder al material de estudio &rarr;</a>
</td>
</tr>
</table>
</div>
@endforeach

{{-- ===== CÓMO ACCEDER ===== --}}
<div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 20px 24px; margin: 24px 0;">
<div style="font-size: 15px; font-weight: 800; color: #133a54; margin-bottom: 14px; text-transform: uppercase; letter-spacing: 0.5px;">&#128279; ¿Cómo Acceder a tu Material de Estudio?</div>
<ol style="margin: 0; padding-left: 20px; font-size: 13px; color: #333; line-height: 1.8;">
<li>Ingresa a <strong><a href="{{ url('/') }}" style="color: #133a54; font-weight: 700;">{{ config('app.name') }}</a></strong></li>
<li>Haz clic en <strong>Iniciar Sesión</strong></li>
<li>Usa tu correo: <strong style="color: #133a54;">{{ $order->user->email }}</strong></li>
<li>Usa la contraseña que creaste durante la compra</li>
</ol>
</div>

@component('mail::button', ['url' => url('/mis_cursos'), 'color' => 'primary'])
Ir a Mi Material de Estudio
@endcomponent

{{-- ===== AYUDA ===== --}}
<div style="font-size: 15px; font-weight: 800; color: #133a54; margin: 28px 0 14px; text-transform: uppercase; letter-spacing: 0.5px;">&#128222; ¿Necesitas Ayuda?</div>

<div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 20px 24px; margin: 0 0 24px; text-align: center;">
<p style="margin: 0 0 16px; font-size: 14px; color: #333;"><strong>Si tienes inconvenientes con la plataforma o necesitas soporte:</strong></p>
<a href="https://wa.me/573236871881?text=Hola%2C%20necesito%20ayuda%20con%20mi%20compra%20{{ $order->number }}"
   style="display: inline-block; background-color: #25D366; color: #ffffff; padding: 12px 30px; border-radius: 25px; text-decoration: none; font-weight: 700; font-size: 14px;">
   <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" width="18" height="18" fill="white" style="display: inline-block; vertical-align: middle; margin-right: 8px;"><path d="M476.9 161.1C435 119.1 379.2 96 319.9 96C197.5 96 97.9 195.6 97.9 318C97.9 357.1 108.1 395.3 127.5 429L96 544L213.7 513.1C246.1 530.8 282.6 540.1 319.8 540.1L319.9 540.1C442.2 540.1 544 440.5 544 318.1C544 258.8 518.8 203.1 476.9 161.1zM319.9 502.7C286.7 502.7 254.2 493.8 225.9 477L219.2 473L149.4 491.3L168 423.2L163.6 416.2C145.1 386.8 135.4 352.9 135.4 318C135.4 216.3 218.2 133.5 320 133.5C369.3 133.5 415.6 152.7 450.4 187.6C485.2 222.5 506.6 268.8 506.5 318.1C506.5 419.9 421.6 502.7 319.9 502.7zM421.1 364.5C415.6 361.7 388.3 348.3 383.2 346.5C378.1 344.6 374.4 343.7 370.7 349.3C367 354.9 356.4 367.3 353.1 371.1C349.9 374.8 346.6 375.3 341.1 372.5C308.5 356.2 287.1 343.4 265.6 306.5C259.9 296.7 271.3 297.4 281.9 276.2C283.7 272.5 282.8 269.3 281.4 266.5C280 263.7 268.9 236.4 264.3 225.3C259.8 214.5 255.2 216 251.8 215.8C248.6 215.6 244.9 215.6 241.2 215.6C237.5 215.6 231.5 217 226.4 222.5C221.3 228.1 207 241.5 207 268.8C207 296.1 226.9 322.5 229.6 326.2C232.4 329.9 268.7 385.9 324.4 410C359.6 425.2 373.4 426.5 391 423.9C401.7 422.3 423.8 410.5 428.4 397.5C433 384.5 433 373.4 431.6 371.1C430.3 368.6 426.6 367.2 421.1 364.5z"/></svg>
   Contáctanos por WhatsApp
</a>
<p style="margin: 14px 0 0 0; color: #64748b; font-size: 12px;">Horario de atención: Lunes a Viernes de 9:00 AM a 12:00 PM y de 2:00 PM a 6:00 PM</p>
</div>

{{-- ===== TÉRMINOS ===== --}}
<div style="font-size: 15px; font-weight: 800; color: #133a54; margin: 24px 0 14px; text-transform: uppercase; letter-spacing: 0.5px;">&#128221; Términos y Condiciones</div>

<div style="background-color: #fef9d6; border: 1px solid #f5e42c; border-radius: 10px; padding: 18px 22px; margin: 0 0 24px; font-size: 13px; color: #333;">
<p style="margin: 0 0 12px;">Al realizar esta compra has aceptado nuestros <a href="{{ url('/terminos-de-servicio') }}" style="color: #133a54; font-weight: 700;">Términos y Condiciones</a>.</p>
<table style="width: 100%; border-collapse: collapse;">
<tr>
<td style="padding: 6px 0; font-size: 13px; color: #333; vertical-align: middle;">&#10004; El material <strong>no es descargable</strong> y se visualiza <strong>únicamente desde la plataforma</strong></td>
</tr>
<tr>
<td style="padding: 6px 0; font-size: 13px; color: #333; vertical-align: middle;">&#10004; Material de <strong>uso personal e intransferible</strong></td>
</tr>
<tr>
<td style="padding: 6px 0; font-size: 13px; color: #333; vertical-align: middle;">&#10004; Contenido protegido por derechos de autor</td>
</tr>
<tr>
<td style="padding: 6px 0; font-size: 13px; color: #333; vertical-align: middle;">&#10004; Actualizaciones gratuitas del material</td>
</tr>
</table>
</div>

Saludos,<br>
{{ config('app.name') }}

@endcomponent
