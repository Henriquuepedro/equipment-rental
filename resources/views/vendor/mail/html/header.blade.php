@props(['url'])
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
<img src="{{ public_path(env('app.logotipo')) }}" class="logo" alt="{{ env('app.name') }}"> {{ $slot }}
</a>
</td>
</tr>
