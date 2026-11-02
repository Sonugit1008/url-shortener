<?php

namespace App\Http\Controllers;

use App\Mail\InvitationMail;
use App\Models\Company;
use App\Models\Invitation;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class InvitationController extends Controller
{
    public function inviteClient(Request $request)
    {
        $request->validate([
            'company_name' => ['required', 'string', 'max:255', 'unique:companies,name'],
            'email' => ['required', 'email', 'unique:users,email'],
        ]);

        $company = Company::create(['name' => $request->company_name]);

        return $this->sendInvitation($company->id, 'Admin', $request->email, null);
    }


    public function inviteTeamMember(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'role' => ['required', Rule::in(['Admin', 'Member'])],
        ]);

        return $this->sendInvitation($request->user()->company_id, $request->role, $request->email, $request->name);
    }

    public function show(string $token)
    {
        $invitation = $this->findValidInvitation($token);

        return view('invitations.accept', compact('invitation'));
    }

    public function accept(Request $request, string $token)
    {
        $invitation = $this->findValidInvitation($token);

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'password' => ['required', 'min:8', 'confirmed'],
        ]);

        if (User::where('email', $invitation->email)->exists()) {
            return back()->withErrors(['email' => 'An account with this email already exists.']);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $invitation->email,
            'password' => Hash::make($request->password),
            'company_id' => $invitation->company_id,
            'role_id' => $invitation->role_id,
        ]);

        $invitation->update(['accepted_at' => now()]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect('/');
    }

    private function sendInvitation(int $companyId, string $roleName, string $email, ?string $name)
    {
        $invitation = Invitation::create([
            'company_id' => $companyId,
            'role_id' => Role::where('name', $roleName)->value('id'),
            'name' => $name,
            'email' => $email,
            'token' => Str::random(40),
            'expires_at' => now()->addDays(7),
            'created_by' => auth()->id(),
        ]);

        $link = route('invitations.show', $invitation->token);

        try {
            Mail::to($email)->send(new InvitationMail($invitation));
            $message = "Invitation sent to {$email}.";
        } catch (\Throwable $e) {
            report($e);
            $message = 'Invitation created, but the email could not be sent.';
        }

        return back()->with('success', "{$message} Invitation link: {$link}");
    }

    private function findValidInvitation(string $token): Invitation
    {
        return Invitation::with(['company', 'role'])
            ->where('token', $token)
            ->whereNull('accepted_at')
            ->where('expires_at', '>', now())
            ->firstOrFail();
    }
}
