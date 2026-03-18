@props(['url'])
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
<img src="{{ asset('logo.jpg') }}" class="logo" alt="{{ config('app.name') }}" style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover; border: 3px solid #fdf2f8;">
</a>
</td>
</tr>
