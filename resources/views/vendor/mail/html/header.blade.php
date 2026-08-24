@props(['url'])
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
@if (trim($slot) === 'Laravel')
<img src="{{ url('assets/images/logo/logo-color.png') }}" class="logo" alt="Construyendo Méritos con Excelencia" style="height: 75px; max-height: 75px;">
@else
{!! $slot !!}
@endif
</a>
</td>
</tr>
