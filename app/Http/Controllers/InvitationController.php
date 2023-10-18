<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Response;
use App\Http\Requests\StoreInvitationRequest;
use App\Classes\Invitation\InvitationRepository;
use App\Mail\InvitationMail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Mail;

class InvitationController extends Controller
{
    /**
     * Store a newly created invitation in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(StoreInvitationRequest $request, InvitationRepository $repository)
    {
        $email = $request->input('email');

        /** @var User $user */
        $user = User::firstWhere('email', $email);

        // A user of the app cannot be invited another time
        if ($user) {
            $message = $user->organization_id === Auth::user()->organization_id ?
                "'$email' is already a member of your organization." :
                "'$email' is already a member of another organization."
            ;

            throw ValidationException::withMessages(['email' => $message])
                ->status(Response::HTTP_CONFLICT);
        }

        // Create and send the invitation to the user
        $invitation = $repository->create($email);
        Mail::send(new InvitationMail($invitation));

        return $invitation;
    }
}
