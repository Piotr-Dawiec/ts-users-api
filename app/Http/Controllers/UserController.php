<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

use Illuminate\Support\Facades\DB;

use Illuminate\Validation\Rule;

use App\Events\UserCreated;

use App\Models\Email;
use App\Models\User;


class UserController extends Controller
{
    public function index()
    {
        return response()->json(User::with('emails')->get());
    }

    public function show($id)
    {
        $user = User::with('emails')->findOrFail($id);
        return response()->json($user);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone' => 'required|string|max:15',
            'emails' => 'required|array',
            'emails.*' => 'required|email|distinct|unique:emails,email'
        ]);

        try
        {
            DB::beginTransaction();

            $user = User::create($validated);

            foreach ($request->emails as $email) {
                $user->emails()->create(['email' => $email]);
            }

            DB::commit();

            event(new UserCreated($user));

            return response()->json($user, 201);
        }
        catch (Throwable $e) {
            DB::rollBack();

            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone' => 'required|string|max:15',
            'emails' => 'required|array',
            'emails.*' => [
                'required',
                'email',
                'distinct',
                Rule::unique('emails', 'email')->whereNot('user_id', $user->id),
            ],
        ]);
        
        try 
        {
            DB::beginTransaction();

            $user->update($validated);

            // Diff emails to delete and to create
            $existingEmails = $user->emails->pluck('email')->toArray();
            $newEmails = $request->emails;

            $toAdd = array_diff($newEmails, $existingEmails);
            $toDelete = array_diff($existingEmails, $newEmails);

            if ($toDelete) {
                $user->emails()->whereIn('email', $toDelete)->delete();
            }

            foreach ($toAdd as $email) {
                if (!Email::where('email', $email)->exists()) {
                    $user->emails()->create(['email' => $email]);
                } else {
                    throw new \Exception("E-mail '{$email}' already exists.");
                }
            }

            DB::commit();

            return response()->json($user->load('emails'));
        }
        catch (Throwable $e) {
            DB::rollBack();

            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        // @todo: what to do with Email?
        // Leave as they are or also delete?

        return response()->json(null, 204);
    }
}
    