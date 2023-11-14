<?php

namespace App\Http\Controllers;

use App\Classes\Invitation\Invitation;
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
     * Return all the invitations.
     *
     * @param InvitationRepository $invitation
     * @return \Illuminate\Http\Response
     */
    public function index(InvitationRepository $invitation)
    {
        return $invitation->findAll();
    }

    /**
     * Return the specified invitation.
     *
     * @param  App\Classes\Invitation\Invitation  $invitation
     * @return \Illuminate\Http\Response
     */
    public function show(Invitation $invitation)
    {
        return $invitation;
    }

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

        // Create the invitation in the Redis database
        $invitation = $repository->create($email);

        try {
            // Send the invitation to the user by email
            Mail::send(new InvitationMail($invitation));
        }
        catch (\Exception $error) {
            // If an error occurs when sending the email, the invitation should
            // be deleted: it shouldn't exist if the email failed to be sent
            $repository->delete($invitation);
        }

        return $invitation;
    }
}
