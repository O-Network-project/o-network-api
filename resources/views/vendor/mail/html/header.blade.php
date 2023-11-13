<tr>
<td class="header">
<a href="{{ env('CLIENT_URL') }}" style="display: inline-block;">
@if (trim($slot) === 'Laravel')
<img src="https://laravel.com/img/notification-logo.png" class="logo" alt="Laravel Logo">
@else
<img src="{{ asset('images/emails/o_network_logo.png') }}" class="logo" alt="Logo {{ $slot }}">
@endif
</a>
</td>
</tr>
