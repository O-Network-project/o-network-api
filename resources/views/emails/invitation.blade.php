@component('mail::message')
# Vous êtes invité à rejoindre *{{ $invitation->getOrganization()->name }}* sur {{ config('app.name') }}&nbsp;!

Il vous suffit de créer un compte en cliquant sur le bouton "Rejoindre" ci-dessous.

Remplissez le formulaire et vous pourrez ensuite échanger avec tous les membres de ***{{ $invitation->getOrganization()->name }}*** sur [{{ env('CLIENT_URL') }}]({{ env('CLIENT_URL') }}).

@component('mail::button', ['url' => env('CLIENT_URL').'/sign-up?token='.$invitation->getToken()])
Rejoindre
@endcomponent

Note : cette invitation expire dans 30 jours.
@endcomponent
